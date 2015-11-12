<?php
/**
  * You are allowed to use this API in your web application.
 *
 * Copyright (C) 2013 by customweb GmbH
 *
 * This program is licenced under the customweb software licence. With the
 * purchase or the installation of the software in your application you
 * accept the licence agreement. The allowed usage is outlined in the
 * customweb software licence which can be found under
 * http://www.customweb.ch/en/software-license-agreement
 *
 * Any modification or distribution is strictly forbidden. The license
 * grants you the installation in one application. For multiuse you will need
 * to purchase further licences at http://www.customweb.com/shop.
 *
 * See the customweb software licence agreement for more details.
 *

 * @category	Customweb
 * @package		Customweb_SaferpayCw
 * @version		1.3.177
 */

class Customweb_SaferpayCw_Model_Source_Capture
{
	public function toOptionArray()
	{
		return array(
				array('value'=>'capturing_deferred', 'label'=> Mage::helper('adminhtml')->__('Delayed')),
				array('value'=>'capturing_direct', 'label'=> Mage::helper('adminhtml')->__('Direct capture after order'))
		);
	}
}
