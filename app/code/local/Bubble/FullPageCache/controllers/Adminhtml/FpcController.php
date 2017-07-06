<?php
/**
 * @category    Bubble
 * @package     Bubble_FullPageCache
 * @version     3.5.0
 * @copyright   Copyright (c) 2015 BubbleShop (https://www.bubbleshop.net)
 */
class Bubble_FullPageCache_Adminhtml_FpcController extends Mage_Adminhtml_Controller_Action
{
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('system/fpc');
    }

    public function indexAction()
    {
        $this->_title($this->__('Full Page Cache'));
        $this->loadLayout();
        $this->_setActiveMenu('system/fpc');
        $this->renderLayout();
    }

    public function actionsAction()
    {
        $this->_getSession()->setActiveSection('actions');
        $this->loadLayout();
        $this->renderLayout();
    }

    public function newActionAction()
    {
        $this->_forward('editAction');
    }

    public function editActionAction()
    {
        $this->_getSession()->setActiveSection('actions');
        $id = $this->getRequest()->getParam('id');
        $action = Mage::getModel('bubble_fpc/action')->load($id);
        if ($id && !$action->getId()) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('bubble_fpc')->__('This action no longer exists.')
            );

            return $this->_redirect('*/*/');
        }

        Mage::register('fpc_action', $action);

        $this->_title($this->__('Edit Action'));
        $this->loadLayout();
        $this->_addContent($this->getLayout()->createBlock('bubble_fpc/adminhtml_action_edit'));
        $this->renderLayout();
    }

    public function saveActionAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            $id = $this->getRequest()->getParam('action_id');
            $action = Mage::getModel('bubble_fpc/action')->load($id);
            if ($id && !$action->getId()) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('bubble_fpc')->__('This action no longer exists.')
                );

                return $this->_redirect('*/*/');
            }

            try {
                $action->setData($data);
                $action->save();

                Mage::getSingleton('adminhtml/session')
                    ->addSuccess(Mage::helper('bubble_fpc')->__('The action has been saved.'));

                return $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());

                return $this->_redirect('*/*/editAction', array('id' => $this->getRequest()->getParam('id')));
            }
        } else {
            return $this->_redirect('*/*/');
        }
    }

    public function deleteActionAction()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            try {
                $model = Mage::getModel('bubble_fpc/action');
                $model->load($id);
                $model->delete();
                Mage::getSingleton('adminhtml/session')
                    ->addSuccess(Mage::helper('bubble_fpc')->__('The action has been deleted.'));

                return $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());

                return $this->_redirect('*/*/editAction', array('id' => $id));
            }
        }

        Mage::getSingleton('adminhtml/session')
            ->addError(Mage::helper('bubble_fpc')->__('Unable to find an action to delete.'));

        return $this->_redirect('*/*/');
    }

    public function storesAction()
    {
        $this->_getSession()->setActiveSection('stores');
        $this->loadLayout();
        $this->renderLayout();
    }

    public function editStoreAction()
    {
        $this->_getSession()->setActiveSection('stores');
        $id = $this->getRequest()->getParam('id');
        $store = Mage::app()->getStore($id);
        if ($id && !$store->getId()) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('bubble_fpc')->__('This store no longer exists.')
            );

            return $this->_redirect('*/*/');
        }

        Mage::register('fpc_store', $store);

        $this->_title($this->__('Edit Store View'));
        $this->loadLayout();
        $this->_addContent($this->getLayout()->createBlock('bubble_fpc/adminhtml_store_edit'));
        $this->renderLayout();
    }

    public function saveStoreAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            $id = $this->getRequest()->getParam('store_id');
            $store = Mage::app()->getStore($id);
            if ($id && !$store->getId()) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('bubble_fpc')->__('This store no longer exists.')
                );

                return $this->_redirect('*/*/');
            }

            try {
                if ($data['is_active']) {
                    Mage::helper('bubble_fpc')->enableStoreCache($store);
                } else {
                    Mage::helper('bubble_fpc')->disableStoreCache($store);
                }

                Mage::getSingleton('adminhtml/session')
                    ->addSuccess(Mage::helper('bubble_fpc')->__('The store has been saved.'));

                return $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());

                return $this->_redirect('*/*/editStore', array('id' => $this->getRequest()->getParam('id')));
            }
        } else {
            return $this->_redirect('*/*/');
        }
    }

    public function generateAllAction()
    {
        $this->loadLayout();
        $session = Mage::getSingleton('adminhtml/session');
        $helper = Mage::helper('bubble_fpc');

        if (!$helper->isFpcEnabled()) {
            $session->addError('Full Page Cache is disabled. Enable it in "System > Cache Management".');

            return $this->_redirect('*/*/');
        }

        foreach (Mage::app()->getStores() as $store) {
            if ($store->getIsActive() && $helper->isStoreCacheable($store)) {
                $this->_addContent($this->getLayout()->createBlock(
                    'bubble_fpc/adminhtml_generate',
                    '',
                    array(
                        'template' => 'bubble/fpc/generate.phtml',
                        'store' => $store,
                    )
                ));
            }
        }

        $this->renderLayout();
    }

    public function generateAction()
    {
        $storeId = $this->getRequest()->getParam('store_id');
        $store = Mage::app()->getStore($storeId);
        $session = Mage::getSingleton('adminhtml/session');

        if (!$storeId || !$store->getId() || !$store->getIsActive()) {
            $session->addError('Please specify a valid store for cache generation');
        } elseif (!Mage::helper('bubble_fpc')->isStoreCacheable($store)) {
            $session->addError('Full Page Cache is disabled for this store. Aborted.');
        } elseif (!Mage::helper('bubble_fpc')->isFpcEnabled()) {
            $session->addError('Full Page Cache is disabled. Enable it in "System > Cache Management".');
        } else {
            Mage::register('fpc_store', $store);
        }

        $this->loadLayout();
        $this->renderLayout();
    }

    public function clearStoreAction()
    {
        $storeId = $this->getRequest()->getParam('store_id');
        $store = Mage::app()->getStore($storeId);
        $session = Mage::getSingleton('adminhtml/session');

        if (!$storeId || !$store->getId() || !$store->getIsActive()) {
            $session->addError('Please specify a valid store for cache generation');
        } elseif (!Mage::helper('bubble_fpc')->isStoreCacheable($store)) {
            $session->addError('Full Page Cache is disabled for this store. Aborted.');
        } elseif (!Mage::helper('bubble_fpc')->isFpcEnabled()) {
            $session->addError('Full Page Cache is disabled. Enable it in "System > Cache Management".');
        } else {
            Mage::helper('bubble_fpc')->clearCache($store->getId());
            $session->addSuccess(Mage::helper('bubble_fpc')->__('Store cache has been cleared.'));
        }

        $this->_redirectReferer();
    }

    public function generateUrlAction()
    {
        $url = $this->getRequest()->getParam('url');
        $store = $this->getRequest()->getParam('store');
        $result = Mage::helper('bubble_fpc')->call($url, $store);
        $urls = Mage::getSingleton('core/session')->getAutoGenerateUrls();
        if ($urls && $urls->offsetExists($url)) {
            $urls->offsetUnset($url);
        }

        return $this->getResponse()
            ->setHeader('Content-type', 'application/json', true)
            ->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function clearProductAction()
    {
        $productId = $this->getRequest()->getParam('id');
        $product = Mage::getModel('catalog/product')->load($productId);
        if ($product->getId()) {
            Mage::helper('bubble_fpc')->clearProduct($product, true);
            Mage::getSingleton('adminhtml/session')
                ->addSuccess(Mage::helper('bubble_fpc')->__('Product cache has been cleared.'));
        }

        $this->_redirectReferer();
    }

    public function clearCategoryAction()
    {
        $categoryId = $this->getRequest()->getParam('id');
        $category = Mage::getModel('catalog/category')->load($categoryId);
        if ($category->getId()) {
            Mage::helper('bubble_fpc')->clearCategory($category, true);
            Mage::getSingleton('adminhtml/session')
                ->addSuccess(Mage::helper('bubble_fpc')->__('Category cache has been cleared.'));
        }

        $this->_redirect('*/catalog_category/edit', array('id' => $categoryId, 'clear' => true));
    }

    public function clearPageAction()
    {
        $pageId = $this->getRequest()->getParam('id');
        $page = Mage::getModel('cms/page')->load($pageId);
        if ($page->getId()) {
            Mage::helper('bubble_fpc')->clearPage($page, true);
            Mage::getSingleton('adminhtml/session')
                ->addSuccess(Mage::helper('bubble_fpc')->__('Page cache has been cleared.'));
        }

        $this->_redirectReferer();
    }

    public function blocksAction()
    {
        $this->_getSession()->setActiveSection('blocks');
        $this->loadLayout();
        $this->renderLayout();
    }

    public function newBlockAction()
    {
        $this->_forward('editBlock');
    }

    public function editBlockAction()
    {
        $this->_getSession()->setActiveSection('blocks');
        $id = $this->getRequest()->getParam('id');
        $block = Mage::getModel('bubble_fpc/block')->load($id);
        if ($id && !$block->getId()) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('bubble_fpc')->__('This block no longer exists.')
            );

            return $this->_redirect('*/*/');
        }

        Mage::register('fpc_block', $block);

        $this->_title($this->__('Edit Block'));
        $this->loadLayout();
        $this->_addContent($this->getLayout()->createBlock('bubble_fpc/adminhtml_block_edit'));
        $this->renderLayout();
    }

    public function saveBlockAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            $id = $this->getRequest()->getParam('block_id');
            $block = Mage::getModel('bubble_fpc/block')->load($id);
            if ($id && !$block->getId()) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('bubble_fpc')->__('This block no longer exists.')
                );

                return $this->_redirect('*/*/');
            }

            try {
                $block->setData($data);
                $block->save();

                Mage::getSingleton('adminhtml/session')
                    ->addSuccess(Mage::helper('bubble_fpc')->__('The block has been saved.'));

                return $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());

                return $this->_redirect('*/*/editBlock', array('id' => $this->getRequest()->getParam('id')));
            }
        } else {
            return $this->_redirect('*/*/');
        }
    }

    public function deleteBlockAction()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            try {
                $model = Mage::getModel('bubble_fpc/block');
                $model->load($id);
                $model->delete();
                Mage::getSingleton('adminhtml/session')
                    ->addSuccess(Mage::helper('bubble_fpc')->__('The block has been deleted.'));

                return $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());

                return $this->_redirect('*/*/editBlock', array('id' => $id));
            }
        }

        Mage::getSingleton('adminhtml/session')
            ->addError(Mage::helper('bubble_fpc')->__('Unable to find a block to delete.'));

        return $this->_redirect('*/*/');
    }
}