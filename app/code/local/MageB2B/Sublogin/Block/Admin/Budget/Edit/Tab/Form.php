<?php

class
MageB2B_Sublogin_Block_Admin_Budget_Edit_Tab_Form
extends
Mage_Adminhtml_Block_Widget_Form
{
protected
function
_prepareForm()
{
$_fb05a43927cff438da7fdbd15552ff3227bd4b96
=
new
Varien_Data_Form();
$this->setForm($_fb05a43927cff438da7fdbd15552ff3227bd4b96);
$_64af779d81eb451f44e50893e14780ab31bf8125
=
$_fb05a43927cff438da7fdbd15552ff3227bd4b96->addFieldset('budget_form',
array('legend'=>Mage::helper('sublogin')->__('Budget')));
$_aa144973cf82c701d4f476014bdd1cc8b374e8fc
=
Mage::registry('budget_data');
if
($_aa144973cf82c701d4f476014bdd1cc8b374e8fc->getId())
{
$_64af779d81eb451f44e50893e14780ab31bf8125->addField('budget_id',
'hidden',
array(
'label'
=>
Mage::helper('sublogin')->__('ID'),
'class'
=>
'',
'name'
=>
'budget_id',
));
}
$_64af779d81eb451f44e50893e14780ab31bf8125->addField('sublogin_id',
'text',
array(
'label'
=>
Mage::helper('sublogin')->__('Sublogin'),
'class'
=>
'required-entry',
'required'
=>
true,
'name'
=>
'sublogin_id',

));
$_5f127681ddf82c8ec56d5513ad4e79c86e6fb9d6
=
Mage::getSingleton('core/layout')->createBlock('sublogin/admin_autocompleter');
$_5f127681ddf82c8ec56d5513ad4e79c86e6fb9d6->setAutocompleteData($_5f127681ddf82c8ec56d5513ad4e79c86e6fb9d6->getSublogins());
$_fb05a43927cff438da7fdbd15552ff3227bd4b96->getElement('sublogin_id')->setRenderer($_5f127681ddf82c8ec56d5513ad4e79c86e6fb9d6);

$_aa144973cf82c701d4f476014bdd1cc8b374e8fc->getBudgetType();
$_64af779d81eb451f44e50893e14780ab31bf8125->addField('budget_type',
'select',
array(
'label'
=>
Mage::helper('sublogin')->__('Budget Type'),
'class'
=>
'required-entry',
'required'
=>
true,
'name'
=>
'budget_type',
'values'
=>
Mage::helper('sublogin')->getBudgetTypesArray(),
));
$_64af779d81eb451f44e50893e14780ab31bf8125->addField('year',
'select',
array(
'label'
=>
Mage::helper('sublogin')->__('Year'),
'class'
=>
'ymd_fields',
'required'
=>
true,
'name'
=>
'year',
'values'
=>
Mage::helper('sublogin')->getYearsArray(),
'container_id'
=>
'year_container',
));
if
($_aa144973cf82c701d4f476014bdd1cc8b374e8fc->getBudgetType()
==
MageB2B_Sublogin_Model_Budget::TYPE_MONTH)
{
$_aa144973cf82c701d4f476014bdd1cc8b374e8fc->setMonth($_aa144973cf82c701d4f476014bdd1cc8b374e8fc->getYear().'-'.$_aa144973cf82c701d4f476014bdd1cc8b374e8fc->getMonth());
}
$_64af779d81eb451f44e50893e14780ab31bf8125->addField('month',
'select',
array(
'label'
=>
Mage::helper('sublogin')->__('Month'),
'class'
=>
'ymd_fields',
'required'
=>
true,
'name'
=>
'month',
'values'
=>
Mage::helper('sublogin')->getMonthsArray(),
'container_id'
=>
'month_container',
));
if
($_aa144973cf82c701d4f476014bdd1cc8b374e8fc->getBudgetType()
==
MageB2B_Sublogin_Model_Budget::TYPE_DAY)
{
$_aa144973cf82c701d4f476014bdd1cc8b374e8fc->setDay($_aa144973cf82c701d4f476014bdd1cc8b374e8fc->getYear().'-'.$_aa144973cf82c701d4f476014bdd1cc8b374e8fc->getMonth().'-'.$_aa144973cf82c701d4f476014bdd1cc8b374e8fc->getDay());
}
$_64af779d81eb451f44e50893e14780ab31bf8125->addField('day',
'date',
array(
'name'
=>
'day',
'label'
=>
Mage::helper('sublogin')->__('Day'),
'title'
=>
Mage::helper('sublogin')->__('Day'),
'image'
=>
$this->getSkinUrl('images/grid-cal.gif'),
'input_format'
=>
Varien_Date::DATE_INTERNAL_FORMAT,
'format'
=>
'yyyy-MM-d',
'container_id'
=>
'day_container',
'class'
=>
'ymd_fields',
'required'
=>
true,
));
$_64af779d81eb451f44e50893e14780ab31bf8125->addField('per_order',
'text',
array(
'label'
=>
Mage::helper('sublogin')->__('Per Order'),
'class'
=>
'',
'required'
=>
false,
'name'
=>
'per_order',
));
$_64af779d81eb451f44e50893e14780ab31bf8125->addField('amount',
'text',
array(
'label'
=>
Mage::helper('sublogin')->__('Amount'),
'class'
=>
'required-entry',
'required'
=>
true,
'name'
=>
'amount',
));
$_7ede06c73a9ed9f1150a4d2074b620fd7e6f3aeb
=
'<script type="text/javascript">
			$("budget_type").observe("change", function(){
				selectedValue = $(this).value;
				subloginDisplayContainerByBudgetType(selectedValue);
			});
			
			subloginDisplayContainerByBudgetType = function(selectedValue){
				// remove required validation from all
				$$(".ymd_fields").each(function(elem){
					elem.removeClassName("required-entry")
				});
			
				if (selectedValue == "year") {
					$("year_container").show();
					$("year").addClassName("required-entry")
					
					$("month_container").hide();
					$("day_container").hide();
				} else if (selectedValue == "month") {
					$("month_container").show();
					$("month").addClassName("required-entry")
					
					$("year_container").hide();
					$("day_container").hide();
				} else if (selectedValue == "day") {
					$("day_container").show();
					$("day").addClassName("required-entry")
					
					$("year_container").hide();
					$("month_container").hide();
				} else {
					$("day_container").hide();
					$("year_container").hide();
					$("month_container").hide();
				}
			}
			
			subloginDisplayContainerByBudgetType("'.$_aa144973cf82c701d4f476014bdd1cc8b374e8fc->getBudgetType().'");
		</script>';
$_64af779d81eb451f44e50893e14780ab31bf8125->addField('js_scripts',
'hidden',
array(
'class'
=>
'',
'name'
=>
'js_scripts',
'after_element_html'
=>
$_7ede06c73a9ed9f1150a4d2074b620fd7e6f3aeb,
));
$_fb05a43927cff438da7fdbd15552ff3227bd4b96->setValues($_aa144973cf82c701d4f476014bdd1cc8b374e8fc->getData());









return
parent::_prepareForm();
}











}
