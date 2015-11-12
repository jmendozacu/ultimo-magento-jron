<?php

/**
 * @category   Creativestyle
 * @package    Creativestyle_Core
 * @copyright  Copyright (c) 2011 - 2013 creativestyle GmbH (http://www.creativestyle.de)
 * @author     Marek Zabrowarny / creativestyle GmbH <support@creativestyle.de>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class Creativestyle_Core_Block_Adminhtml_Info_Abstract extends Mage_Adminhtml_Block_System_Config_Form_Fieldset {

    protected
        $_extensionName = 'Creativestyle_Abstract';

    abstract protected function _getInfo();

    public function getExtensionVersion() {
        return (string)Mage::getConfig()->getNode('modules/' . $this->_extensionName . '/version');
    }

    public function render(Varien_Data_Form_Element_Abstract $element) {
        return $this->_getInfo();
    }

    /**
     * @deprecated after 1.0.0
     */
    protected function _getStyle() {
        $content = '<style>';
        $content .= '.creativestyle-info { border: 1px solid #cccccc; background: #e7efef; margin-bottom: 10px; padding: 10px; height: auto; }';
        $content .= '.creativestyle-info .creativestyle-logo { float: right; padding: 5px; }';
        $content .= '.creativestyle-info .creativestyle-command { border: 1px solid #cccccc; background: #ffffff; padding: 15px; text-align: left; margin: 10px 0; font-weight: bold; }';
        $content .= '.creativestyle-info h3 { color: #ea7601; }';
        $content .= '.creativestyle-info h3 small { font-weight: normal; font-size: 80%; font-style: italic; }';
        $content .= '</style>';
        return $content;
    }

    /**
     * @deprecated after 1.0.0
     */
    protected function _getFooter() {
        $content = '--------------------------------------------------------<br />';
        $content .= '<p>' . $this->helper('creativestyle')->__('Visit <a href="%s" target="_blank">%s</a> to get more information.', 'http://www.creativestyle.de/magento-agentur.html', 'http://www.creativestyle.de/magento-agentur.html') . '</p>';
        return $content;
    }

    /**
     * @deprecated after 1.0.0
     */
    protected function _getLogo() {
        return '<a class="creativestyle-logo" href="http://www.creativestyle.de" target="_blank"><img src="' . $this->getSkinUrl('creativestyle/images/creativestyle-logo.png') . '" alt="creativestyle" /></a>';
    }

}
