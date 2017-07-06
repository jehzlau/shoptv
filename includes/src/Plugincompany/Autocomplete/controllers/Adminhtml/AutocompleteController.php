<?php
/**
 *
 * Created by:  Milan Simek
 * Company:     Plugin Company
 *
 * LICENSE: http://plugin.company/docs/magento-extensions/magento-extension-license-agreement
 *
 * YOU WILL ALSO FIND A PDF COPY OF THE LICENSE IN THE DOWNLOADED ZIP FILE
 *
 * FOR QUESTIONS AND SUPPORT
 * PLEASE DON'T HESITATE TO CONTACT US AT:
 *
 * SUPPORT@PLUGIN.COMPANY
 *
 */
class Plugincompany_Autocomplete_Adminhtml_AutocompleteController extends Mage_Adminhtml_Controller_Action
{
    public function cachesuggestionsAction()
    {
        $stores = Mage::getModel('core/store')->getCollection();
        $error = 0;
        try {
            foreach ($stores as $store) {
                if ($store->getId() == 0) {
                    continue;
                }

                $this->saveJSON($store);
            }
        } catch (Exception $e){
            $error = true;
            Mage::getSingleton('core/session')->addError($e->getMessage());
        }
        if(!$error){
            Mage::getSingleton('core/session')->addSuccess('Suggestion cache successfully rebuilt');
        }
        $this->_redirect('adminhtml/cache/index');
    }

    public function saveJSON($store)
    {
        $cacheDir = Mage::getBaseDir('media') . DS . 'suggestioncache';
        if(!is_dir($cacheDir)){
            mkdir($cacheDir, 0777, true);
        }

        $json_products = $this->getProductsJSON($store);
        $parts = $this->getParts($json_products);

        $i = 0;
        foreach ($parts as $part) {
            $i++;
            $data = json_encode(array_values($part));
            $fileName = $cacheDir . DS . $store->getId() . '_' . $i . '.json';
            $fp = fopen($fileName, 'w');
            fwrite($fp, $data);
            fclose($fp);
            chmod($fp, 777);
        }
        return $this;
    }

    public function getProductsJSON($store)
    {

        $_productCollection = Mage::getModel('catalog/product')->setStoreId($store->getId())->getCollection()
            ->addStoreFilter($store->getId())
            ->setStoreId($store->getId())
            ->addAttributeToFilter('status', 1)
            ->addAttributeToFilter('visibility', 4)
            ->addAttributeToSelect(array('name', 'url_path','image_url','image','small_image','thumbnail','short_description'))
            ;

        $json_products = array();
        foreach ($_productCollection as $_product) {
            $img = $this->getProductImage($_product,$store);

            //sku
            $sku = $_product->getSku();
            //sku split beteen numbers and alpha chars (with spaces)
            $skuParts = preg_replace('/((?<=[A-Z]{2})(?=[0-9])|(?<=[0-9])(?=[A-Z]{2}))/',' ', $sku);
            //sku with alphanumeric characters only (other characters replaced with spaces)
            $skuAlpha = preg_replace("/[^a-zA-Z0-9\s]/", " ", $sku);
            //split sku with alphanumeric characters only
            $skuAlphaParts = preg_replace("/[^a-zA-Z0-9\s]/", " ", $skuParts);

            $skuSearch = $sku;
            $skuSearch .= ' ' . $skuParts;
            $skuSearch .= ' ' . $skuAlpha;
            $skuSearch .= ' ' . $skuAlphaParts;

            $json_products[] = array(
                'name'          => $_product->getName(),
                'url_path'   => $_product->getUrlPath(),
                'description'   => substr(strip_tags($_product->getShortDescription()), 0, 110) . '...',
                'keywords'      => implode(' ',array_unique(explode(' ', strip_tags($_product->getShortDescription()) . ' ' . $skuSearch . ' ' . $_product->getName()))),
                'imageurl'      => $img
            );

        }
        return $json_products;
    }

    protected function _makeUnique($a)
    {
        //only keep unique values
        $a = array_filter($a);
        $a = array_reduce($a, 'array_merge', array());
        $res = array();
        foreach ($a as $k => $v) {
            if (in_array($v['url_path'],$res)) {
                unset($a[$k]);
            }else{
                $res[] = $v['url_path'];
            }
        }
        return $a;
    }

    protected function _removeDups($a, $b)
    {
        $keys = array();
        foreach ($a as $v) {
            $keys[] = $v['url_path'];
        }
        foreach($b as $k => $v){
            if (in_array($v['url_path'],$keys)) {
                unset($b[$k]);
            }
        }
        return $b;
    }

    public function getParts($json)
    {
        $jsonn = $json;
        $alphas = array_merge(range(0,9),range('A', 'Z'));

        //PART1 starting with character
        $part1 = array_flip($alphas);
        foreach ($part1 as $k => $v) {
            $part1[$k] = array();
        }

        foreach($json as $item){
            foreach($alphas as $alph){
                //only add up to 5 items
                if(count($part1[$alph]) >= 5){
                    continue;
                }
                $match = preg_grep("/^{$alph}/i", explode(' ',$item['keywords']));
                if (!empty($match)) {
                    $part1[$alph][] = $item;
                }
            }
        }

        $part1 = $this->_makeUnique($part1);

        //PART 2
        $part2 = array();
        foreach($alphas as $a){
            foreach($alphas as $b){
                //two characters
                $t = $a . $b;
                $part2[$t] = array();
            }
        }

        //keep for part 3 usage
        $twoChars = $part2;

        //PART2 the first two characters
        foreach($json as $item){
            foreach($part2 as $chars => $v){
                //only add up to 5 items
                if(count($part2[$chars]) >= 5){
                    continue;
                }
                $match = preg_grep("/^{$chars}/i", explode(' ',$item['keywords']));
                if (!empty($match)) {
                    $part2[$chars][] = $item;
                }
            }
        }

        $part2 = $this->_makeUnique($part2);
        $part2 = $this->_removeDups($part1, $part2);

        $json = $this->_removeDups($part1, $json);
        $json = $this->_removeDups($part2, $json);

        return array($part1, $part2, $json);
    }

    public function getProductImage($_product,$store){
        $imageType = Mage::getStoreConfig('plugincompany_autocomplete/general/imagetype');
        if(!$imageType){
            $imageType = 'thumbnail';
        }
        $images = array();
        $images['thumbnail'] = 'thumbnail';
        $images['small_image'] = 'small_image';
        $images['image'] = 'image';
        unset($images[$imageType]);
        array_unshift($images,$imageType);

        foreach($images as $imageType){
            try{
                $img = (string)Mage::helper('catalog/image')->init($_product, $imageType)->resize(Mage::getStoreConfig('plugincompany_autocomplete/general/imagesize',$store));
                $img = str_replace($store->getBaseUrl('media') . 'catalog/product/cache/', '', $img);
                return $img;
            }catch(Exception $e){
                $img = '';
                //do nothing
            }
        }
        if($img == ''){
            try {
                $_product->setSmallImage('no_selection');
                $img = (string)Mage::helper('catalog/image')->init($_product, 'small_image')->resize(Mage::getStoreConfig('plugincompany_autocomplete/general/imagesize', $store));
            }catch(Exception $e) {
                $img = '';
                //do nothing
            }
        }
        return $img;
    }
}
