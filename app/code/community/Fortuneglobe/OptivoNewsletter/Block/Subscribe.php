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
 * Newsletter Subscription block
 *
 * @category   Fortuneglobe
 * @package    Fortuneglobe_OptivoNewsletter
 * @author     Fortuneglobe Magento Developer
 */

class Fortuneglobe_OptivoNewsletter_Block_Subscribe extends Mage_Newsletter_Block_Subscribe
{

    protected function _toHtml()
    {
		// set extended template if no template or the default template is set (that makes it possible to override the template via layout.xml)
    	if (!$this->getTemplate() || $this->getTemplate() == 'newsletter/subscribe.phtml') {
			$this->setTemplate('fortuneglobe/optivonewsletter/subscriber.phtml');
        }else if($this->getTemplate() == 'newsletter/subscribe_mini.phtml'){
        	$this->setTemplate('fortuneglobe/optivonewsletter/subscriber_mini.phtml');
        }
		
        return parent::_toHtml();
    }
}
