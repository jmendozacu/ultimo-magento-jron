<?php

/**
 *  * You are allowed to use this API in your web application.
 *
 * Copyright (C) 2015 by customweb GmbH
 *
 * This program is licenced under the customweb software licence. With the
 * purchase or the installation of the software in your application you
 * accept the licence agreement. The allowed usage is outlined in the
 * customweb software licence which can be found under
 * http://www.sellxed.com/en/software-license-agreement
 *
 * Any modification or distribution is strictly forbidden. The license
 * grants you the installation in one application. For multiuse you will need
 * to purchase further licences at http://www.sellxed.com/shop.
 *
 * See the customweb software licence agreement for more details.
 *
 *
 * @category Customweb
 * @package Customweb_SaferpayCw
 * @version 1.3.251
 */

/**
 *
 * @author Simon Schurter
 */
class Customweb_SaferpayCw_Model_ExternalCheckoutWidgets {
	public function getWidgets()
    {
    	$widgets = array();
    	
    	
    	
		return $widgets;
    }
    
    public function getAllWidgets()
    {
    	if (Mage::registry('customweb_externalcheckout_widgets_collected') == null) {
    		Mage::register('customweb_externalcheckout_widgets_collected', true);
    		
    		$widgets = array();
    		Mage::dispatchEvent('customweb_externalcheckout_widgets_collect', array(
                'widgets' => &$widgets,
            ));
    		
    		usort($widgets, function($a, $b){
    			if ($a['sortOrder'] == $b['sortOrder']) {
    				return 0;
    			} else {
    				return $a['sortOrder'] < $b['sortOrder'] ? -1 : 1;
    			}
    		});
    		return $widgets;
    	} else {
    		return array();
    	}
    }
}
	