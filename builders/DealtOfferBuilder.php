<?php

declare(strict_types=1);
require_once(_DEALT_MODULE_BUILDERS_DIR_ . 'AbstractBuilder.php');
require_once(_DEALT_MODULE_CLASSES_DIR_ . 'DealtModuleLogger.php');

class DealtOfferBuilder extends AbstractBuilder
{
    private $multiple_value_separator=',';

    /**
     * @param null $id
     * @return DealtOffer
     */
    public function build($id = null)
    {
        return new DealtOffer($id);
    }

    /**
     * @param $data
     * @return bool|DealtOffer
     */
    public function createOrUpdate($data)
    {
        $id = !empty($data['id_offer']) ? $data['id_offer'] : null;
        $dealt = $this->build($id);
        $dealt->dealt_id_offer = $data['dealt_id_offer'];
        $product = new \Product();
        if($dealt->id){
            $product = new \Product($dealt->id_dealt_product);
        }
        //lang fields
        $languages = \Language::getIsoIds(true);
        $name=$data['title_offer_' . Configuration::get('PS_DEFAULT_LANG')] ??  null;
        if(!empty($name)){
            $product->name=DealtTools::createMultiLangField($name);
            $dealt->title_offer=DealtTools::createMultiLangField($name);
        }
        foreach ($languages as $lang) {
            $name=!empty($data['title_offer_' . $lang['id_lang']]) ? $data['title_offer_' . $lang['id_lang']] : null;
            $dealt->title_offer[$lang['id_lang']] = $name;
            $product->name[$lang['id_lang']] = $name;
            $link_rewrite = Tools::link_rewrite($name);
            if ($link_rewrite == '') {
                $link_rewrite = 'friendly-url-autogeneration-failed';
            }
            $product->link_rewrite[$lang['id_lang']] = $link_rewrite;
        }
        //id_category_default
        if(!\Configuration::get('DEALT_MODULE_PRODUCT_CATEGORY')){
            DealtTools::createDealtCategory();
        }
        $product->id_category_default=\Configuration::get('DEALT_MODULE_PRODUCT_CATEGORY');
        $product->reference='DEALT_'.Tools::passwdGen(7);

        //price
        $product->price = (float)$data['product_price'];

        //shop association
        $shop_is_feature_active = Shop::isFeatureActive();
        if (!$shop_is_feature_active) {
            $product->shop = (int)Configuration::get('PS_SHOP_DEFAULT');
            $dealt->shop = (int)Configuration::get('PS_SHOP_DEFAULT');
        } elseif (!isset($product->shop) || empty($product->shop)) {
            $product->shop = implode($this->multiple_value_separator, Shop::getContextListShopID());
            $dealt->shop = implode($this->multiple_value_separator, Shop::getContextListShopID());
        }

        if (!$shop_is_feature_active) {
            $product->id_shop_default = (int)Configuration::get('PS_SHOP_DEFAULT');
        } else {
            $product->id_shop_default = (int)Context::getContext()->shop->id;
        }


        try {
            $product->save();
            if (!$product->id) {
                DealtModuleLogger::log(
                    'Could not create offer',
                    DealtModuleLogger::TYPE_ERROR,
                    ['Errors' => []]
                );
                return false;
            }
            //category product association
            if(empty($data['offer_category_tree'])){
                $data['offer_category_tree']=[];
            }
            $categories_ids=array_unique($data['offer_category_tree']);
            DealtTools::setProductCategoriesAssociations($product,$categories_ids);

            //add stock
            \StockAvailable::setQuantity($product->id, 0, 1);

            $dealt->id_dealt_product = $product->id;
            $dealt->save();

            //DealtOffer category
            if($dealt->id){
                DealtTools::setOfferCategoryAssociation($dealt, $categories_ids);
                DealtOfferCategory::cleanTreeDifference($dealt->id, $dealt->id_dealt_product, $categories_ids);
            }
        } catch (Exception $e) {
            DealtModuleLogger::log(
                'Could not create offer',
                DealtModuleLogger::TYPE_ERROR,
                ['Errors' => ['message' => $e->getMessage(), 'line' => $e->getLine()]]
            );
            return false;
        }
        return $dealt;
    }

}