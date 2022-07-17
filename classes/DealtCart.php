<?php

declare(strict_types=1);


class DealtCart
{
    private $module;
    private $builder;
    /**
     * @var bool
     */
    private $cartSanitized;

    public function __construct()
    {
        $this->module = Module::getInstanceByName('dealtmodule');
        $this->builder = $this->module->getBuilder('DealtCartProductRef');
    }

    /**
     * Attaches a dealt product to a product
     * currently in the prestashop cart and syncs
     * their quantities
     *
     * @param string $dealtOfferId
     * @param int $productId
     * @param int $productAttributeId
     *
     * @return bool
     */
    public function addDealtOfferToCart($dealtOfferId, $productId, $productAttributeId)
    {
        $offer = DealtOffer::findOneByUUID($dealtOfferId);

        if ($offer == null) {
            $this->module->returnResultToAjax([$this->l('Unknown Dealt offer id')]);
        }

        $cart = \Context::getContext()->cart;
        if (!isset($cart) || !$cart->id) {
            $cart = new Cart();
            $cart->id_lang = (int)\Context::getContext()->cookie->id_lang;
            $cart->id_currency = (int)(\Context::getContext()->cookie->id_currency ?? ConfigurationCore::get('PS_CURRENCY_DEFAULT'));
            $cart->id_guest = (int)\Context::getContext()->cookie->id_guest;
            $cart->id_shop_group = (int)\Context::getContext()->shop->id_shop_group;
            $cart->id_shop = \Context::getContext()->shop->id;
            if (\Context::getContext()->cookie->id_customer) {
                $cart->id_customer = (int)\Context::getContext()->cookie->id_customer;
                $cart->id_address_delivery = (int)Address::getFirstCustomerAddressId($cart->id_customer);
                $cart->id_address_invoice = (int)$cart->id_address_delivery;
            } else {
                $cart->id_address_delivery = 0;
                $cart->id_address_invoice = 0;
            }
            // Needed if the merchant want to give a free product to every visitors
            \Context::getContext()->cart = $cart;
            CartRule::autoAddToCart(\Context::getContext());
        }
        $cart->id_currency = (int)(\Context::getContext()->cookie->id_currency ?? ConfigurationCore::get('PS_CURRENCY_DEFAULT'));
        $cartProduct = DealtTools::getProductFromCart($cart, $productId, $productAttributeId);

        if ($cartProduct == null) {
            try {
                if (!$cart->id) {
                    // Save new cart
                    $cart->add();
                }
                $cart->updateQty(
                    Tools::getValue('qty'),
                    $productId,
                    $productAttributeId,
                    false,
                    'up',
                    0,
                    null,
                    false
                );
            } catch (Throwable $e) {
                DealtModuleLogger::log(
                    'Cannot update cart',
                    DealtModuleLogger::TYPE_ERROR,
                    ['Error' => $e]
                );
            }
        }

        $this->builder->createOrUpdate(
            [
                'id_cart' => $cart->id,
                'id_product' => $productId,
                'id_product_attribute' => $productAttributeId,
                'id_offer' => $offer->id
            ]
        );
        \Context::getContext()->cart = $cart;
        \Context::getContext()->cookie->__set('id_cart', (int)$cart->id);

        return $cart->updateQty(
            (isset($cartProduct['quantity']) &&  $cartProduct['quantity'] > 1) ? $cartProduct['quantity'] : 1,
            $offer->id_dealt_product,
            null,
            false
        );
    }

    /**
     * Sanitization of prestashop cart against dealt constraints
     * - get all dealt cart products
     *
     * @param int $cartId
     *
     * @return void
     */
    public function sanitizeDealtCart($cartId)
    {

        if ($this->cartSanitized) {
            return;
        }
        $this->cartSanitized = true;

        $cart = new Cart($cartId);
            $offers = DealtTools::getDealtOffersFromCart($cart);
            $cartProductsIndex = DealtTools::indexCartProducts($cart);

            /*
                     * If we have dealt offers present in the cart
                     * we need to ensure their quantities match their
                     * attached products
                     */
            foreach ($offers as $offer) {
                $quantity = 0;


                $dealtCartRefs = new PrestaShopCollection('DealtCartProductRef');
                $dealtCartRefs->where('id_cart', '=', $cart->id);
                $dealtCartRefs->where('id_offer', '=', $offer->id);
                if($dealtCartRefs->count()){
                    /* iterate over dealt offers in cart */
                    foreach ($dealtCartRefs as $dealtCartRef) {
                        $cartProductId = $dealtCartRef->id_product;
                        $cartProductAttributeId = $dealtCartRef->id_product_attribute;
                        if (isset($cartProductsIndex[$cartProductId][$cartProductAttributeId])) {
                            /* we have a match in the cart */
                            $cartProduct = $cartProductsIndex[$cartProductId][$cartProductAttributeId];
                            $quantity += $cartProduct['quantity'];
                        } else {
                            /*
                             * we should delete the DealtCartProductRef reference as the product id/attribute_id pair could not be
                             * found in the current cart
                             */
                            \Db::getInstance()->execute("DELETE FROM "._DB_PREFIX_."dealt_cart_product_ref WHERE id_dealt_cart_product_ref=".(int)$dealtCartRef->id);
                        }
                    }
                }


                $offerProductId = $offer->id_dealt_product;
                $newQty = (int) $quantity;
                $currentQty = (int) $cartProductsIndex[$offerProductId][0]['quantity'];

                if ($newQty != $currentQty) {
                    $delta = $newQty - $currentQty;
                    $cart->updateQty(abs($delta), $offerProductId, null, false, $delta > 0 ? 'up' : 'down');
                }
            }


    }
}