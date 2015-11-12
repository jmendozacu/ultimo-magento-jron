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
 * Adminhtml newsletter subscribers grid block
 *
 * @category   Fortuneglobe
 * @package    Fortuneglobe_OptivoNewsletter
 * @author     Fortuneglobe Magento Developer
 */

class Fortuneglobe_OptivoNewsletter_Adminhtml_Block_Newsletter_Subscriber_Grid extends Mage_Adminhtml_Block_Newsletter_Subscriber_Grid
{
	protected function _prepareCollection()
	{
        $collection = Mage::getResourceSingleton('newsletter/subscriber_collection');
        $collection
            ->showCustomerInfo()
			->showCustomerGender() // we added this
            ->addSubscriberTypeField()
            ->showStoreInfo();

        if($this->getRequest()->getParam('queue', false)) {
            $collection->useQueue(Mage::getModel('newsletter/queue')
                ->load($this->getRequest()->getParam('queue')));
        }

        $this->setCollection($collection);
		
		/* 	we have to copy the following lines from Mage_Adminhtml_Block_Widget_Grid because we need  
			a new collection but Mage_Adminhtml_Block_Newsletter_Subscriber_Grid would overwrite it */
        if ($this->getCollection()) {

            $this->_preparePage();

            $columnId = $this->getParam($this->getVarNameSort(), $this->_defaultSort);
            $dir      = $this->getParam($this->getVarNameDir(), $this->_defaultDir);
            $filter   = $this->getParam($this->getVarNameFilter(), null);

            if (is_null($filter)) {
                $filter = $this->_defaultFilter;
            }

            if (is_string($filter)) {
                $data = $this->helper('adminhtml')->prepareFilterString($filter);
                $this->_setFilterValues($data);
            }
            else if ($filter && is_array($filter)) {
                $this->_setFilterValues($filter);
            }
            else if(0 !== sizeof($this->_defaultFilter)) {
                $this->_setFilterValues($this->_defaultFilter);
            }

            if (isset($this->_columns[$columnId]) && $this->_columns[$columnId]->getIndex()) {
                $dir = (strtolower($dir)=='desc') ? 'desc' : 'asc';
                $this->_columns[$columnId]->setDir($dir);
                $this->_setCollectionOrder($this->_columns[$columnId]);
            }

            if (!$this->_isExport) {
                $this->getCollection()->load();
                $this->_afterLoadCollection();
            }
        }
    }

	protected function _prepareColumns()
	{
		// prepare columns and sort them by order (see Mage_Adminhtml_Block_Widget_Grid)
		parent::_prepareColumns();
		
		// remove old columns
		$this->removeColumn('gender'); // futureproof
        $this->removeColumn('lastname');
        $this->removeColumn('firstname');
		
		// add new columns
		$this->addColumnAfter('gender', array(
			'header'    => Mage::helper('newsletter')->__('Gender'),
            'index'     => 'customer_gender',
            'type'      => 'options',
            'options'   => array(
                1  => Mage::helper('newsletter')->__('Mr'),
                2  => Mage::helper('newsletter')->__('Ms/Mrs')
            ),
			'renderer'	=> 'Fortuneglobe_OptivoNewsletter_Adminhtml_Block_Newsletter_Subscriber_Grid_Renderer_Gender'
		), 'type');
		
        $this->addColumnAfter('title', array(
            'header'    => Mage::helper('newsletter')->__('Title'),
            'index'     => 'subscriber_title',
            'renderer'  => 'Fortuneglobe_OptivoNewsletter_Adminhtml_Block_Newsletter_Subscriber_Grid_Renderer_Title'
        ), 'gender');

		$this->addColumnAfter('firstname', array(
			'header'    => Mage::helper('newsletter')->__('Firstname'),
            'index'     => 'customer_firstname',
			'renderer'	=> 'Fortuneglobe_OptivoNewsletter_Adminhtml_Block_Newsletter_Subscriber_Grid_Renderer_Firstname'
		), 'title');
		
		$this->addColumnAfter('lastname', array(
			'header'    => Mage::helper('newsletter')->__('Lastname'),
            'index'     => 'customer_lastname',
			'renderer'	=> 'Fortuneglobe_OptivoNewsletter_Adminhtml_Block_Newsletter_Subscriber_Grid_Renderer_Lastname'
		), 'firstname');

        $this->addColumnAfter('street', array(
            'header'    => Mage::helper('newsletter')->__('Street'),
            'index'     => 'subscriber_street',
            'renderer'  => 'Fortuneglobe_OptivoNewsletter_Adminhtml_Block_Newsletter_Subscriber_Grid_Renderer_Street'
        ), 'lastname');

        $this->addColumnAfter('country', array(
            'header'    => Mage::helper('newsletter')->__('Country'),
            'index'     => 'subscriber_country',
            'renderer'  => 'Fortuneglobe_OptivoNewsletter_Adminhtml_Block_Newsletter_Subscriber_Grid_Renderer_Country'
        ), 'street');

        $this->addColumnAfter('zipcode', array(
            'header'    => Mage::helper('newsletter')->__('Zipcode'),
            'index'     => 'subscriber_zipcode',
            'renderer'  => 'Fortuneglobe_OptivoNewsletter_Adminhtml_Block_Newsletter_Subscriber_Grid_Renderer_Zipcode'
        ), 'country');

        $this->addColumnAfter('city', array(
            'header'    => Mage::helper('newsletter')->__('City'),
            'index'     => 'subscriber_city',
            'renderer'  => 'Fortuneglobe_OptivoNewsletter_Adminhtml_Block_Newsletter_Subscriber_Grid_Renderer_City'
        ), 'zipcode');

        $this->addColumnAfter('telephone', array(
            'header'    => Mage::helper('newsletter')->__('Telephone'),
            'index'     => 'subscriber_telephone',
            'renderer'  => 'Fortuneglobe_OptivoNewsletter_Adminhtml_Block_Newsletter_Subscriber_Grid_Renderer_Telephone'
        ), 'city');

        $this->addColumnAfter('dayofbirth', array(
            'header'    => Mage::helper('newsletter')->__('Day of Birth'),
            'index'     => 'subscriber_dayofbirth',
            'renderer'  => 'Fortuneglobe_OptivoNewsletter_Adminhtml_Block_Newsletter_Subscriber_Grid_Renderer_Dayofbirth'
        ), 'telephone');

        $this->addColumnAfter('signip', array(
            'header'    => Mage::helper('newsletter')->__('Signip'),
            'index'     => 'subscriber_signip',
            'renderer'  => 'Fortuneglobe_OptivoNewsletter_Adminhtml_Block_Newsletter_Subscriber_Grid_Renderer_Signip'
        ), 'dayofbirth');
        $this->addColumnAfter('signtimestamp', array(
            'header'    => Mage::helper('newsletter')->__('SignTimestamp'),
            'index'     => 'subscriber_signtimestamp',
            'renderer'  => 'Fortuneglobe_OptivoNewsletter_Adminhtml_Block_Newsletter_Subscriber_Grid_Renderer_SignTimestamp'
        ), 'signip');
        $this->addColumnAfter('activateip', array(
            'header'    => Mage::helper('newsletter')->__('Activateip'),
            'index'     => 'subscriber_activateip',
            'renderer'  => 'Fortuneglobe_OptivoNewsletter_Adminhtml_Block_Newsletter_Subscriber_Grid_Renderer_Activateip'
        ), 'signtimestamp');
        $this->addColumnAfter('activatetimestamp', array(
            'header'    => Mage::helper('newsletter')->__('ActivateTimestamp'),
            'index'     => 'subscriber_activatetimestamp',
            'renderer'  => 'Fortuneglobe_OptivoNewsletter_Adminhtml_Block_Newsletter_Subscriber_Grid_Renderer_ActivateTimestamp'
        ), 'activateip');
        
		// manually sort again, that our custom order works
		$this->sortColumnsByOrder();
		
        return $this;
    }
}
