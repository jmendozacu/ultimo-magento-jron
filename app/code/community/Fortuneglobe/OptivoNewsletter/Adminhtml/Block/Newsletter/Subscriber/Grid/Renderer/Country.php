<?php

/**
 * Fortuneglobe
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Fortuneglobe
 * @package     Fortuneglobe_OptivoNewsletter
 * @copyright   Copyright (c) 2014 Fortuneglobe (http://www.fortuneglobe.de)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml newsletter subscribers grid Country item renderer
 *
 * @category   Fortuneglobe
 * @package    Fortuneglobe_OptivoNewsletter
 * @author     Fortuneglobe Magento Developer
 */

class Fortuneglobe_OptivoNewsletter_Adminhtml_Block_Newsletter_Subscriber_Grid_Renderer_Country extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	public function render(Varien_Object $row)
	{
		$value = $row->getSubscriberCountry();
		return $value ? $value : '---';
	}
}