<?php

class Iksanika_OneStepCheckout_Helper_Extraproducts 
    extends Mage_Core_Helper_Abstract
{
    
    
    
    function getProductIds()
    {
        $ids_raw = Mage::getStoreConfig('onestepcheckout/extra_products/product_ids');
        return ($ids_raw && $ids_raw != '') ? explode(',', $ids_raw) : array();
    }
   
    
    
    function productInCart($productId)
    {
        $cart = Mage::helper('checkout/cart')->getCart();
        foreach($cart->getItems() as $cartItem) 
        {
            if($cartItem->getProduct()->getId() == $productId) 
            {
                return true;
            }
        }
        return false;
    }

    
    
    function isValidExtraProduct($product_id)
    {
        $ids = $this->getProductIds();
        return (in_array($product_id, $ids)); // return true : false
    }

    
    
    function hasExtraProducts()
    {
        return (count($this->getProductIds()) > 0) ? true : false;
    }

    
    
    function getExtraProducts()
    {
        $products = array();
        foreach($this->getProductIds() as $productId) 
        {
            if($productId != '') 
            {
                try {
                    $product = Mage::getModel('catalog/product')->load($productId);
                } catch(Exception $e) 
                {
                    continue;
                }
                if($product->getId()) 
                {
                    $products[] = $product;
                }
            }
        }
        return $products;
    }

}
