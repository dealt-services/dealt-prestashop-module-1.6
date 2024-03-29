<?php

declare(strict_types=1);


class DealtCheckoutValidation
{
    /** @var bool|null */
    private $valid = null;

    /** @var bool|null */
    private $validPhone = null;

    /** @var string */
    private $zipCode;

    /** @var string */
    private $country;
    /**
     * @var Cart
     */
    private $cart;

    public function __construct(\Cart $cart)
    {
        $this->cart=$cart;
    }

    public function isValid()
    {
        if(!$this->verifyOfferAvailabilityForSession()){
            DealtModuleLogger::log(
                'Dealt Service is not available',
                DealtModuleLogger::TYPE_ERROR,
                ['id_cart' => $this->cart->id]
            );
        }
        if(!$this->verifyPhoneNumberForSession()){
            DealtModuleLogger::log(
                'Invalid phone or mobile phone',
                DealtModuleLogger::TYPE_ERROR,
                ['id_cart' => $this->cart->id]
            );
        }
        return $this->valid && $this->validPhone;
    }


    /**
     * @return bool
     */
    protected function verifyOfferAvailabilityForSession()
    {
        $offers = DealtTools::getDealtOffersFromCart($this->cart);

        if($offers->count()===0){
            $this->valid = true;

            return $this->valid;
        }
        $dealtCartRefs = new PrestaShopCollection('DealtCartProductRef');
        $dealtCartRefs->where('id_cart', '=', $this->cart->id);

        if($dealtCartRefs->count()===0){
            DealtModuleLogger::log(
                'DealtCartProductRef not found',
                DealtModuleLogger::TYPE_ERROR,
                ['Errors' => 'Cannot found cart in DealtCartProductRef', 'id_cart' => $this->cart->id]
            );
            $this->valid = false;
            return $this->valid;
        }
        $address = new Address((int)$this->cart->id_address_delivery);
        $this->zipCode = $address->postcode;
        $this->country = $address->country;

        $valid = true;
        foreach ($offers as $offer) {
            $offerId = $offer->dealt_id_offer;
            $client=Dealtmodule::getClient();
            $result=$client->checkAvailability($offerId, $this->zipCode, $this->country);

            $available = (($result != null && !empty($result['response']['available'])) ? $result['response']['available']==1 : false);

            $valid = $valid && $available;

        }

        $this->valid = $valid;

        return $this->valid;
    }

    protected function verifyPhoneNumberForSession()
    {
        $address = new Address((int)$this->cart->id_address_delivery);
        $countryCode = (new Country($address->id_country))->iso_code;

        $phone = DealtTools::formatPhoneNumberE164($address->phone, $countryCode);
        $phoneMobile = DealtTools::formatPhoneNumberE164($address->phone_mobile, $countryCode);

        $this->validPhone = ($phone != false || $phoneMobile != false);

        return $this->validPhone;
    }
}