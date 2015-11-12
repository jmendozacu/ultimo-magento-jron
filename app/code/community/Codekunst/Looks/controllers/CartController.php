<?php

require_once 'app/code/core/Mage/Checkout/controllers/CartController.php';

class Codekunst_Looks_CartController extends Mage_Checkout_CartController {
    public function addAction() {
        if (!$this->_validateFormKey()) {
            $this->_goBack();
            return;
        }
        $cart      = $this->_getCart();
        $noticeProduct = Mage::getModel('catalog/product');
        $noticeProduct->setName($this->__('Look'));
        $allParams = $this->getRequest()->getParams();
        foreach($allParams['products'] as $productId => $params) {
            try {
                if(!array_key_exists('look-checkbox', $params) || $params['look-checkbox'] != 'on') {
                    continue;
                }

                if (isset($params['qty'])) {
                    $filter = new Zend_Filter_LocalizedToNormalized(
                        array('locale' => Mage::app()->getLocale()->getLocaleCode())
                    );
                    $params['qty'] = $filter->filter($params['qty']);
                }

                $product = $this->_initLookProduct($productId, $params['super_attribute']);
                if(Mage::helper('core')->isModuleEnabled('OrganicInternet_SimpleConfigurableProducts')) {
                    $params['product'] = $product->getId();
                    $params['cpid'] = $product->getCpid();
                }

                /**
                 * Check product availability
                 */
                if (!$product) {
                    continue;
                }

                $cart->addProduct($product, $params);

                $this->getLayout()->getUpdate()->addHandle('ajaxcart');
                $this->loadLayout();
            } catch (Mage_Core_Exception $e) {
                if(Mage::helper('core')->isModuleEnabled('Hardik_Ajaxcart')) {
                    $_response = Mage::getModel('ajaxcart/response');
                    $_response->setError(true);

                    $messages = array_unique(explode("\n", $e->getMessage()));
                    $json_messages = array();
                    foreach ($messages as $message) {
                        $json_messages[] = Mage::helper('core')->escapeHtml($message);
                    }

                    $_response->setMessages($json_messages);

                    $url = $this->_getSession()->getRedirectUrl(true);

                    $_response->send();
                    break;
                } else {
                    if ($this->_getSession()->getUseNotice(true)) {
                        $this->_getSession()->addNotice(Mage::helper('core')->escapeHtml($e->getMessage()));
                    } else {
                        $messages = array_unique(explode("\n", $e->getMessage()));
                        foreach ($messages as $message) {
                            $this->_getSession()->addError(Mage::helper('core')->escapeHtml($message));
                        }
                    }

                    $url = $this->_getSession()->getRedirectUrl(true);
                    if ($url) {
                        $this->getResponse()->setRedirect($url);
                    } else {
                        $this->_redirectReferer(Mage::helper('checkout/cart')->getCartUrl());
                    }
                    break;
                }
            } catch (Exception $e) {
                if(Mage::helper('core')->isModuleEnabled('Hardik_Ajaxcart')) {
                    $this->_getSession()->addException($e, $this->__('Cannot add the item to shopping cart.'));
                    Mage::logException($e);

                    $_response = Mage::getModel('ajaxcart/response');
                    $_response->setError(true);
                    $_response->setMessage($this->__('Cannot add the item to shopping cart.'));
                    $_response->send();
                } else {
                    $this->_getSession()->addException($e, $this->__('Cannot add the item to shopping cart.'));
                    Mage::logException($e);
                    $this->_goBack();
                }
            }
        }

        $cart->save();

        $this->_getSession()->setCartWasUpdated(true);

        Mage::dispatchEvent('checkout_cart_add_product_complete',
            array('product' => $noticeProduct, 'request' => $this->getRequest(), 'response' => $this->getResponse())
        );

        if (!$this->_getSession()->getNoCartRedirect(true)) {
            if (!$cart->getQuote()->getHasError()) {
                $message = $this->__('The selected look was added to your shopping cart.');
                $this->_getSession()->addSuccess($message);
            }
        }

        $this->_goBack();
    }

    /**
     * Initialize product instance from request data
     *
     * @param int $productId ID of the configurable product
     * @param array $params Configurable attribute values
     * @return Mage_Catalog_Model_Product || false
     */
    protected function _initLookProduct($productId, $params)
    {
        if ($productId) {
            $parentProduct = Mage::getModel('catalog/product')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($productId);
            if ($parentProduct->getId()) {
                if(Mage::helper('core')->isModuleEnabled('OrganicInternet_SimpleConfigurableProducts')) {
                    // Load simple product with the configured super attribute values
                    /** @var Mage_Catalog_Model_Product_Type_Configurable $typeInstance */
                    $typeInstance = $parentProduct->getTypeInstance();
                    $product = $typeInstance->getProductByAttributes($params, $parentProduct);

                    $product = Mage::getModel('catalog/product')
                        ->setStoreId(Mage::app()->getStore()->getId())
                        ->load($product->getId());
                    $product->setCpid($parentProduct->getId());
                } else {
                    $product = $parentProduct;
                }
                return $product;
            }
        }
        return false;
    }
}
