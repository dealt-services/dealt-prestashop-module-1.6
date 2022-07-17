<?php

declare(strict_types=1);



class DealtPresenter
{
    /**
     * Present dealt offer data for a product
     * id / attribute_id pair
     *
     * @param Cart $cart
     * @param int $productId
     * @param int|null $productAttributeId
     * @param $id_lang
     * @param int|null $orderId
     *
     * @return mixed
     */
    public static function present(Cart $cart, $productId, $productAttributeId, $id_lang, $id_currency,  $orderId = null)
    {
        $offer=self::getOfferFromProductCategories($productId);
        if($offer === null){
            return [];
        }

        $cartProduct = DealtTools::getProductFromCart($cart, $productId, $productAttributeId);

        $quantity = Tools::getValue('qty', (isset($cartProduct['quantity']) ? $cartProduct['quantity'] : null));

        return [
            'offer' => array_merge([
                'title' => $offer->title_offer[$id_lang],
                'description' => $offer->getDealtProduct()->description_short,
                'dealtOfferId' => $offer->id,
                'dealtOfferUUIDV4' => $offer->dealt_id_offer,
                'price' => DealtTools::getFormattedPrice($offer, $id_currency, $quantity),
                'unitPriceFormatted' => DealtTools::getFormattedPrice($offer, $id_currency),
                'unitPrice' => DealtTools::getPrice($productId, $productAttributeId),
                'image' => _PS_BASE_URL_.'/modules/dealtmodule/views/img/default.png',
                'product' => $offer->getDealtProduct(),
            ], []),
            'binding' => [
                'productId' => $productId,
                'productAttributeId' => $productAttributeId,
                'cartProduct' => DealtTools::getProductFromCart($cart, $productId, $productAttributeId),
                'cartOffer' => DealtTools::getProductFromCart($cart, $offer->id_dealt_product),
                'data' => array_merge(
                    [
                        'cartId' => $cart->id,
                        'productId' => $productId,
                        'offer' => $offer,
                    ],
                    $productAttributeId != null ? ['productAttributeId' => $productAttributeId] : []
                ),
                'cartRef' => DealtCartProductRef::searchByCriteria($cart->id, $productId, $offer->id, $productAttributeId),
            ],
        ];
    }

    /**
     * @param $productId
     * @return DealtOffer|null
     */
    private static function getOfferFromProductCategories($productId)
    {
        $product = new \Product($productId);

        if (!\Validate::isLoadedObject($product)) {
            return null;
        }
        $id_offer=DealtOfferCategory::getProductOffer($product);

        if(empty($id_offer)){
            return null;
        }
        $offer= new DealtOffer($id_offer);

        if (!\Validate::isLoadedObject($offer)) {
            return null;
        }

        return $offer;
    }
}