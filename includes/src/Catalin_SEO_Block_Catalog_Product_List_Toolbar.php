<?php

/**
 * Catalin Ciobanu
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @package     Catalin_Seo
 * @copyright   Copyright (c) 2013 Catalin Ciobanu
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Catalin_SEO_Block_Catalog_Product_List_Toolbar extends Mage_Catalog_Block_Product_List_Toolbar
{

    /**
     * Return current URL with rewrites and additional parameters
     *
     * @param array $params Query parameters
     * @return string
     */
    public function getPagerUrl($params = array())
    {
        if (!$this->helper('catalin_seo')->isEnabled()) {
            return parent::getPagerUrl($params);
        }

        if ($this->helper('catalin_seo')->isCatalogSearch()) {
            $params['isLayerAjax'] = null;
            return parent::getPagerUrl($params);
        }

        return $this->helper('catalin_seo')->getPagerUrl($params);
    }

    public function setCollection( $collection )
    {
        $this->_collection = $collection;
        $this->_collection->setCurPage( $this->getCurrentPage() );    // we need to set pagination only if passed value integer and more that 0
        $limit = (int) $this->getLimit();
        if ($limit) {
            $this->_collection->setPageSize( $limit );
        }
        if ($this->getCurrentOrder()) {
            if ($this->getCurrentOrder() == 'random') {
                $this->getCollection()->getSelect()->order( new Zend_Db_Expr('RAND()') );
            } else {
                $this->getCollection()->setOrder( $this->getCurrentOrder(), $this->getCurrentDirection() );
            }
        }

        return $this;
    }

}