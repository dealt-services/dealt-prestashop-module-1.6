<?php


use Dealt\DealtSDK\DealtClient;
use Dealt\DealtSDK\DealtEnvironment;
use Dealt\DealtSDK\Exceptions\GraphQLException;
use Dealt\DealtSDK\Exceptions\GraphQLFailureException;
use Dealt\DealtSDK\GraphQL\Types\Object\Mission;
use Dealt\DealtSDK\GraphQL\Types\Object\OfferAvailabilityQuerySuccess;


class DealtGenericClient
{
    /**
     * @var DealtEnv
     */
    private $env;
    private $module;

    public function __construct()
    {
        $this->env = new DealtEnv();
        $this->module = Module::getInstanceByName('dealtmodule');
    }

    protected $client;

    /**
     * Retrieves the DealtSDK\DealtClient or instantiates
     * a fresh instance on first call
     *
     * @return DealtClient
     */
    public function getClient()
    {
        if ($this->client instanceof DealtClient) {
            return $this->client;
        }

        return new DealtClient([
            'api_key' => $this->env->getDealtApiKey(),
            'env' => $this->env->getName() === 'prod' ? DealtEnvironment::PRODUCTION : DealtEnvironment::TEST,
        ]);
    }

    /**
     * Checks the availability of a Dealt offer
     *
     * @param string $offer_id
     * @param string $zip_code
     * @param string $country
     *
     * @return array
     */
    public function checkAvailability($offer_id, $zip_code, $country = 'France')
    {
        try {
            $offer = $this->getClient()->offers->availability([
                'offer_id' => $offer_id,
                'address' => [
                    'country' => $country,
                    'zip_code' => $zip_code,
                ],
            ]);

            if ($offer !== null) {
                return $this->handleResponse(
                    "Offer is available",
                    'availability',

                    [
                        'id_offer' => $offer_id,
                        'zip_code' => $zip_code,
                        'country' => $country
                    ],

                    array_merge(
                        [
                            'available' => $offer->available,
                            'net_price' => $offer->net_price,
                            'gross_price' => $offer->gross_price,
                            'vat_price' => $offer->vat_price
                        ],
                        $offer->available ? [] : ['reason' => $this->module->l('Offer unavailable for the requested zip code')]
                    )

                );
            }
        } catch (GraphQLFailureException $e) {
            $this->handleException($e);
        } catch (GraphQLException $e) {
            $this->handleException($e);
        } catch (\Exception $e) {
            $this->handleException($e);
        } catch (Throwable $e) {
            $this->handleException($e);
        }
        return null;
    }


    /**
     * @param string $missionId
     *
     * @return Mission|null
     */
    public function cancelMission($missionId)
    {
        try {
            $result = $this->getClient()->missions->cancel($missionId);

            return $result->mission;
        } catch (GraphQLFailureException $e) {
            $this->handleException($e);
        } catch (GraphQLException $e) {
            $this->handleException($e);
        } catch (\Exception $e) {
            $this->handleException($e);
        }
    }

    protected function handleException(\Exception $exception)
    {
        $body = '';
        $httpCode = 500;
        $hasResponse = method_exists($exception, 'hasResponse') ? $exception->hasResponse() : false;

        if (true === $hasResponse && method_exists($exception, 'getResponse')) {
            $body = $exception->getResponse()->getBody();
            $httpCode = $exception->getResponse()->getStatusCode();
        }
        DealtModuleLogger::log('Service temporarely indisponible', DealtModuleLogger::TYPE_ERROR, [
            'status' => false,
            'php version'=>phpversion(),
            'httpCode' => $httpCode,
            'body' => $body,
            'exceptionCode' => $exception->getCode(),
            'exceptionMessage' => $exception->getMessage(),
        ]);
        return [
            'status' => false,
            'httpCode' => $httpCode,
            'body' => $body,
            'exceptionCode' => $exception->getCode(),
            'exceptionMessage' => $exception->getMessage(),
        ];
    }

    protected function handleResponse($successMessage, $method, $arguments, $response)
    {
        if ($this->env->isDebugMode()) {
            DealtModuleLogger::log($successMessage, DealtModuleLogger::TYPE_SUCCESS, [
                'status' => '200',
                'method' => $method,
                'php version'=>phpversion(),
                'arguments' => json_encode($arguments),
                'response' => json_encode($response)
            ]);
        }
        return [
            'status' => '200',
            'method' => $method,
            'arguments' => $arguments,
            'response' => $response
        ];
    }

}
