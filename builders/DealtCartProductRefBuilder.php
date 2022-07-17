<?php

declare(strict_types=1);
require_once(_DEALT_MODULE_BUILDERS_DIR_ . 'AbstractBuilder.php');
require_once(_DEALT_MODULE_CLASSES_DIR_ . 'DealtModuleLogger.php');

class DealtCartProductRefBuilder extends AbstractBuilder
{
    /**
     * @param null $id
     * @return DealtCartProductRef
     */
    public function build($id = null)
    {
        return new DealtCartProductRef($id);
    }

    /**
     * @param $data
     * @return bool|DealtOffer
     */
    public function createOrUpdate($data)
    {

        try{
            $id = !empty($data['id_dealt_cart_product_ref']) ? $data['id_dealt_cart_product_ref'] : null;
            $dealtCartRef = $this->build($id);
            $dealtCartRef->id_cart=$data['id_cart'];
            $dealtCartRef->id_product=$data['id_product'];
            $dealtCartRef->id_product_attribute=$data['id_product_attribute'];
            $dealtCartRef->id_offer=$data['id_offer'];
            $dealtCartRef->save();
        } catch (Exception $e) {
            DealtModuleLogger::log(
                'Could not create dealt_cart_product_ref',
                DealtModuleLogger::TYPE_ERROR,
                ['Errors' => ['message' => $e->getMessage(), 'line' => $e->getLine()]]
            );
            return false;
        }
        return $dealtCartRef;
    }

}