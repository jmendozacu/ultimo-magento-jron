<?php

class Infortis_Ultimo_Helper_Labels extends Mage_Core_Helper_Abstract
{
	/**
	 * Get product labels (HTML)
	 *
	 * @return string
	 */
	public function getLabels($product)
	{
		$html = '';
		if($product->getTypeId() == 'configurable'){
			$childIds = Mage::getResourceSingleton('catalog/product_type_configurable')->getChildrenIds($product->getId(), true);
		}else{
			$childIds = array();
		}
		$now = new DateTime();
		$time = $now->getTimestamp();
		

		$isNew = true;
		if (Mage::getStoreConfig('ultimo/product_labels/new') && false)
		{	
			if(sizeof($childIds) > 0){
				foreach($childIds[0] as $childproducts){
		            $childproddata = Mage::getResourceModel('catalog/product_collection')->addAttributeToSelect(array('news_from_date','news_to_date'))->addIdFilter(array($childproducts))->getFirstItem();
					$tmp = $this->isNew($childproddata);

					if( $tmp == 1){
						$isNew = $tmp;
						break;
					}
				}
			}else{
				$isNew = $this->isNew($product);
			}

			//Old
			//$isNew = $this->isNew($product);
		}
		
		$isSale = true;
		if (Mage::getStoreConfig('ultimo/product_labels/sale') && false)
		{
			if(sizeof($childIds) > 0){
				foreach($childIds[0] as $childproducts){
		            $childproddata = Mage::getResourceModel('catalog/product_collection')->addAttributeToSelect(array('price','special_price','special_from_date','special_to_date'))->addIdFilter(array($childproducts))->getFirstItem();
					$tmp = $this->isOnSale($childproddata);
					if( $tmp == 1){
						$isSale = $tmp;
						break;
					}
				}
			}else{
				$isSale = $this->isOnSale($product);
			}

			//Old
			//$isSale = $this->isOnSale($product);
		}
		
		if ($isNew == true)
		{
			$html .= '<span class="sticker-wrapper top-left customhide"><span class="sticker new">' . $this->__('New') . '</span></span>';
		}
		
		if ($isSale == true)
		{
			$html .= '<span class="sticker-wrapper top-right customhide"><span class="sticker sale">' . $this->__('Verkauf') . '</span></span>';
		}

		if ( $this->isBluesign( $product ) )
		{
			$bluesignImgURL = sprintf( '%s%s', Mage::getBaseUrl( Mage_Core_Model_Store::URL_TYPE_MEDIA ), 'wysiwyg/StatischeBilder/bluesign.png' );

			$html .= '<span class="sticker-wrapper bluesign-wrapper customhide"><img class="bluesign-image" src="' . $bluesignImgURL . '"></span>';
		}
		
		return $html;
	}
	
	/**
	 * Check if "new" label is enabled and if product is marked as "new"
	 *
	 * @return  bool
	 */
	public function isNew($product)
	{
		$now = new DateTime();
		$time = $now->getTimestamp();
		if($time <= strtotime($product->getNewsToDate()) && $product->getNewsToDate() != "" ){
			return 1;
		}else{
			return 0;
		}
		//return $this->_nowIsBetween($product->getData('news_from_date'), $product->getData('news_to_date'));
	}
	
	/**
	 * Check if "sale" label is enabled and if product has special price
	 *
	 * @return  bool
	 */
	public function isOnSale($product)
	{
		$now = new DateTime();
		$time = $now->getTimestamp();
		$specialPrice = number_format($product->getSpecialPrice(), 2);
		$regularPrice = number_format($product->getPrice(), 2);


		if ($specialPrice != $regularPrice && $specialPrice < $regularPrice && $specialPrice > 0){
			if($time <= strtotime($product->getSpecialToDate()) && $product->getSpecialToDate() != ""  ){
				return 1;
			}else{
				return 0;
			}
			//return $this->_nowIsBetween($product->getData('special_from_date'), $product->getData('special_to_date'));
		}
		else{
			return false;
		}
	}

	public function isBluesign( $product )
	{
		$tmp_arr = $product->getTypeInstance()->getUsedProducts();
		$obj     = $tmp_arr[0];

		if ( is_string( $obj->getBadge() ) )
		{
			$badges = explode( ',', $obj->getBadge() );
			if ( in_array( '13122', $badges ) )
				return true;
		}

		return false;
	}
	
	protected function _nowIsBetween($fromDate, $toDate)
	{
		if ($fromDate)
		{
			$fromDate = strtotime($fromDate);
			$toDate = strtotime($toDate);
			$now = strtotime(Mage::app()->getLocale()->date()->setTime('00:00:00')->toString(Varien_Date::DATETIME_INTERNAL_FORMAT));
			
			if ($toDate)
			{
				if ($fromDate <= $now && $now <= $toDate)
					return true;
			}
			else
			{
				if ($fromDate <= $now)
					return true;
			}
		}
		
		return false;
	}
}
