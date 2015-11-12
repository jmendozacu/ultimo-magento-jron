<?php

/**
 * Licentia Enhanced CMS
 *
 * NOTICE OF LICENSE
 * This source file is subject to the European Union Public Licence
 * It is available through the world-wide-web at this URL:
 * http://joinup.ec.europa.eu/software/page/eupl/licence-eupl
 *
 * @title      Licentia Enhanced CMS
 * @category   Easy of Use
 * @package    Licentia
 * @author     Bento Vilas Boas <bento@licentia.pt>
 * @copyright  Copyright (c) 2012 Licentia - http://licentia.pt
 * @license    European Union Public Licence
 */
class Licentia_Ecms_Block_Adminhtml_Block_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('cmsBlockGrid');
        $this->setDefaultSort('block_identifier');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
    }

    protected function _prepareCollection() {
        $collection = Mage::getModel('cms/block')->getCollection();
        /* @var $collection Mage_Cms_Model_Mysql4_Block_Collection */
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $baseUrl = $this->getUrl();

        $this->addColumn('title', array(
            'header' => Mage::helper('cms')->__('Title'),
            'align' => 'left',
            'index' => 'title',
        ));

        $this->addColumn('identifier', array(
            'header' => Mage::helper('cms')->__('Identifier'),
            'align' => 'left',
            'index' => 'identifier'
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header' => Mage::helper('cms')->__('Store View'),
                'index' => 'store_id',
                'type' => 'store',
                'store_all' => true,
                'store_view' => true,
                'sortable' => false,
                'filter_condition_callback'
                => array($this, '_filterStoreCondition'),
            ));
        }

        $this->addColumn('is_active', array(
            'header' => Mage::helper('cms')->__('Status'),
            'index' => 'is_active',
            'type' => 'options',
            'options' => array(
                0 => Mage::helper('cms')->__('Disabled'),
                1 => Mage::helper('cms')->__('Enabled')
            ),
        ));

        $this->addColumn('creation_time', array(
            'header' => Mage::helper('cms')->__('Date Created'),
            'index' => 'creation_time',
            'type' => 'datetime',
        ));

        $this->addColumn('update_time', array(
            'header' => Mage::helper('cms')->__('Last Modified'),
            'index' => 'update_time',
            'type' => 'datetime',
        ));

        $this->addColumn('edit', array(
            'header' => $this->__('Edit'),
            'type' => 'action',
            'align' => 'center',
            'width' => '80px',
            'filter' => false,
            'sortable' => false,
            'actions' => array(array(
                    'url' => $this->getUrl('*/*/edit', array('block_id' => '$block_id')),
                    'caption' => $this->__('Edit'),
                )),
            'index' => 'type',
            'sortable' => false
        ));

        return parent::_prepareColumns();
    }

    protected function _afterLoadCollection() {
        $this->getCollection()->walk('afterLoad');
        parent::_afterLoadCollection();
    }

    protected function _filterStoreCondition($collection, $column) {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }

        $this->getCollection()->addStoreFilter($value);
    }

    /**
     * Row click url
     *
     * @return string
     */
    public function getRowUrl($row) {
        return $this->getUrl('*/*/edit', array('block_id' => $row->getId()));
    }

    public function getGridUrl() {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }

    protected function _prepareMassaction() {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('page');

        $this->getMassactionBlock()->addItem('delete', array(
            'label' => Mage::helper('ecms')->__('Delete'),
            'url' => $this->getUrl('*/*/massDelete'),
            'confirm' => Mage::helper('ecms')->__('Are you sure?')
        ));

        $statuses = array(
            array('value' => '0', 'label' => Mage::helper('ecms')->__('Disabled')),
            array('value' => '1', 'label' => Mage::helper('ecms')->__('Enabled')),
        );

        array_unshift($statuses, array('label' => '', 'value' => ''));


        $this->getMassactionBlock()->addItem('status', array(
            'label' => Mage::helper('ecms')->__('Change status'),
            'url' => $this->getUrl('*/*/massStatus', array('_current' => true)),
            'additional' => array(
                'visibility' => array(
                    'name' => 'status',
                    'type' => 'select',
                    'class' => 'required-entry',
                    'label' => Mage::helper('ecms')->__('Status'),
                    'values' => $statuses
                )
            )
        ));

        $storesViewList = array();
        foreach (Mage::app()->getWebsites() as $website) {
            foreach ($website->getGroups() as $group) {
                $stores = $group->getStores();
                foreach ($stores as $store) {
                    $storesViewList[$store->getId()] = $website->getName() . ' / ' . $group->getName() . ' / ' . $store->getName();
                }
            }
        }


        $this->getMassactionBlock()->addItem('copyView', array(
            'label' => Mage::helper('ecms')->__('Copy to Store View'),
            'url' => $this->getUrl('*/*/massCopy', array('_current' => true)),
            'additional' => array(
                'store' => array(
                    'name' => 'storeview',
                    'type' => 'select',
                    'class' => 'required-entry',
                    'style' => 'max-width:100px',
                    'label' => Mage::helper('ecms')->__('Store View'),
                    'values' => $storesViewList
                ),
                'suffix' => array(
                    'name' => 'suffix',
                    'type' => 'text',
                    'style' => 'width:50px',
                    'label' => Mage::helper('ecms')->__('Suffix'),
                )
            )
        ));

        return $this;
    }

}
