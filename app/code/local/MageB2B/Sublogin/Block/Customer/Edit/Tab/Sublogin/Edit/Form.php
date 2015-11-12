<?php

class
MageB2B_Sublogin_Block_Customer_Edit_Tab_Sublogin_Edit_Form
extends
Mage_Adminhtml_Block_Widget_Form
{
protected
function
_prepareForm()
{
$_b803c43bc8b3ef64b7d19b78f670cdbd11dacf8c
=
Mage::registry('current_customer');
$_384d479d3c396afbfb7e832d2d450608e3145d55
=
Mage::registry('subloginModel');
$_fb05a43927cff438da7fdbd15552ff3227bd4b96
=
new
Varien_Data_Form(
array(
'id'
=>
'edit_form',
'action'
=>
$this->getUrl('*/*/save',
array('id'
=>
$this->getRequest()->getParam('id'))),
'method'
=>
'post',
)
);
$_64af779d81eb451f44e50893e14780ab31bf8125
=
$_fb05a43927cff438da7fdbd15552ff3227bd4b96->addFieldset('base_fieldset',
array(
'legend'
=>
Mage::helper('sublogin')->__('Sublogin'),
'class'
=>
'fieldset-wide'
));
if
($_384d479d3c396afbfb7e832d2d450608e3145d55->getId())
{
$_64af779d81eb451f44e50893e14780ab31bf8125->addField('id',
'hidden',
array(
'name'
=>
'id',
'value'
=>
$_384d479d3c396afbfb7e832d2d450608e3145d55->getId(),
));
$_64af779d81eb451f44e50893e14780ab31bf8125->addField('entity_id',
'text',
array(
'name'
=>
'entity_id',
'label'
=>
Mage::helper('sublogin')->__('Customer'),
'title'
=>
Mage::helper('sublogin')->__('Customer'),
'required'
=>
true,
'value'
=>
$_384d479d3c396afbfb7e832d2d450608e3145d55->getEntityId(),
));
}else{
$_64af779d81eb451f44e50893e14780ab31bf8125->addField('entity_id',
'text',
array(
'name'
=>
'entity_id',
'label'
=>
Mage::helper('sublogin')->__('Customer'),
'title'
=>
Mage::helper('sublogin')->__('Customer'),
'required'
=>
true,
'value'
=>
(int)$_384d479d3c396afbfb7e832d2d450608e3145d55->getEntityId(),
));
}
$_5f127681ddf82c8ec56d5513ad4e79c86e6fb9d6
=
Mage::getSingleton('core/layout')->createBlock('sublogin/admin_autocompleter');
$_5f127681ddf82c8ec56d5513ad4e79c86e6fb9d6->setAutocompleteData($_5f127681ddf82c8ec56d5513ad4e79c86e6fb9d6->getCustomers());
$_fb05a43927cff438da7fdbd15552ff3227bd4b96->getElement('entity_id')->setRenderer($_5f127681ddf82c8ec56d5513ad4e79c86e6fb9d6);
$_84422f5df8f0879613ad4290a9c062a687e6767b
=
Mage::helper('sublogin')->getGridFields($_b803c43bc8b3ef64b7d19b78f670cdbd11dacf8c);
foreach
($_84422f5df8f0879613ad4290a9c062a687e6767b
as
$_54323be853e934686a9fca46e2f91c8ead26624e)
{
if
($_54323be853e934686a9fca46e2f91c8ead26624e['type']
==
'html')
{
if
($_54323be853e934686a9fca46e2f91c8ead26624e['name']
==
'expire_date')
{
$_34e10a97491b23c9aea7561ab241b7cdb26e7af7
=
array(
'name'
=>
$_54323be853e934686a9fca46e2f91c8ead26624e['name'],
'label'
=>
$_54323be853e934686a9fca46e2f91c8ead26624e['label'],
'value'
=>
$_384d479d3c396afbfb7e832d2d450608e3145d55->getId(),
'required'
=>
isset($_54323be853e934686a9fca46e2f91c8ead26624e['required'])
?
1
:
0,

'format'
=>
"y-M-dd",
'image'
=>
$this->getSkinUrl('images/grid-cal.gif'),
'value'
=>
$_384d479d3c396afbfb7e832d2d450608e3145d55->getExpireDate()
==
0
?
null
:
$_384d479d3c396afbfb7e832d2d450608e3145d55->getExpireDate(),
);
$_64af779d81eb451f44e50893e14780ab31bf8125->addField($_54323be853e934686a9fca46e2f91c8ead26624e['name'],
'date',
$_34e10a97491b23c9aea7561ab241b7cdb26e7af7);
}
}
else
if
($_54323be853e934686a9fca46e2f91c8ead26624e['name']
==
'password')
{
$_34e10a97491b23c9aea7561ab241b7cdb26e7af7
=
$_54323be853e934686a9fca46e2f91c8ead26624e;
$_34e10a97491b23c9aea7561ab241b7cdb26e7af7['value']
=
'';
if
($_384d479d3c396afbfb7e832d2d450608e3145d55->getId())
{
$_34e10a97491b23c9aea7561ab241b7cdb26e7af7['required']
=
false;
$_34e10a97491b23c9aea7561ab241b7cdb26e7af7['readonly']
=
false;
$_34e10a97491b23c9aea7561ab241b7cdb26e7af7['disabled']
=
false;
$_34e10a97491b23c9aea7561ab241b7cdb26e7af7['class']
=
'validate-password';
$_34e10a97491b23c9aea7561ab241b7cdb26e7af7['onlyNewRequired']
=
false;
$_34e10a97491b23c9aea7561ab241b7cdb26e7af7['onlyNewValue']
=
false;
$_34e10a97491b23c9aea7561ab241b7cdb26e7af7['after_element_html']
=
Mage::helper('sublogin')->__('Only type in password if you need to change. Otherwise the password will not be set.');
if($_384d479d3c396afbfb7e832d2d450608e3145d55->getPassword()
!=
""){

$_34e10a97491b23c9aea7561ab241b7cdb26e7af7['value']
=
"******";
}
}

unset($_34e10a97491b23c9aea7561ab241b7cdb26e7af7['readonly']);
unset($_34e10a97491b23c9aea7561ab241b7cdb26e7af7['disabled']);
$_64af779d81eb451f44e50893e14780ab31bf8125->addField($_54323be853e934686a9fca46e2f91c8ead26624e['name'],
'text',
$_34e10a97491b23c9aea7561ab241b7cdb26e7af7);
}

else
if
($_54323be853e934686a9fca46e2f91c8ead26624e['type']
==
'multiselect')
{
$_34e10a97491b23c9aea7561ab241b7cdb26e7af7
=
$_54323be853e934686a9fca46e2f91c8ead26624e;
$_0683c0576ccd7dbb07809847d06050574ea06777
=
array();
foreach
($_34e10a97491b23c9aea7561ab241b7cdb26e7af7['options']
as
$_3f8de01169eabbb582c7c5ffb762d5f7d1f1bf07
=>
$_2509854ca05e2fd2bdc6a42399c2d4b7c555d617)
{
$_dc687ebfa14dff53d58abe39bffe8f184505e815
=
array(
'value'
=>
$_3f8de01169eabbb582c7c5ffb762d5f7d1f1bf07,
'label'
=>
$_2509854ca05e2fd2bdc6a42399c2d4b7c555d617,
);
$_0683c0576ccd7dbb07809847d06050574ea06777[]
=
$_dc687ebfa14dff53d58abe39bffe8f184505e815;
}
$_34e10a97491b23c9aea7561ab241b7cdb26e7af7['values']
=
$_0683c0576ccd7dbb07809847d06050574ea06777;

$_34e10a97491b23c9aea7561ab241b7cdb26e7af7['value']
=
explode(',',
$_384d479d3c396afbfb7e832d2d450608e3145d55->getData($_54323be853e934686a9fca46e2f91c8ead26624e['name']));
$_64af779d81eb451f44e50893e14780ab31bf8125->addField($_54323be853e934686a9fca46e2f91c8ead26624e['name'],
'multiselect',
$_34e10a97491b23c9aea7561ab241b7cdb26e7af7);
}
else
{
$_34e10a97491b23c9aea7561ab241b7cdb26e7af7
=
array(
'name'
=>
$_54323be853e934686a9fca46e2f91c8ead26624e['name'],
'label'
=>
$_54323be853e934686a9fca46e2f91c8ead26624e['label'],
'value'
=>
$_384d479d3c396afbfb7e832d2d450608e3145d55->getId(),
'required'
=>
$_54323be853e934686a9fca46e2f91c8ead26624e['required'],
'value'
=>
$_384d479d3c396afbfb7e832d2d450608e3145d55->getData($_54323be853e934686a9fca46e2f91c8ead26624e['name']),
);
foreach
(array('options'=>'options',
'cssclass'=>'class')
as
$_dc7f46f519b41520210eda842ff23db60112e8e7=>$_ece7873e79ae520373bf2e8185e60698a2e7a5f6)
{
if
(isset($_54323be853e934686a9fca46e2f91c8ead26624e[$_dc7f46f519b41520210eda842ff23db60112e8e7]))
{
$_34e10a97491b23c9aea7561ab241b7cdb26e7af7[$_ece7873e79ae520373bf2e8185e60698a2e7a5f6]
=
$_54323be853e934686a9fca46e2f91c8ead26624e[$_dc7f46f519b41520210eda842ff23db60112e8e7];
}
}
if
(isset($_54323be853e934686a9fca46e2f91c8ead26624e['readonly'])
&&
$_54323be853e934686a9fca46e2f91c8ead26624e['readonly'])
$_34e10a97491b23c9aea7561ab241b7cdb26e7af7['readonly']
=
true;
if
($_54323be853e934686a9fca46e2f91c8ead26624e['type']
==
'checkbox')
{
$_34e10a97491b23c9aea7561ab241b7cdb26e7af7['value']
=
1;
$_34e10a97491b23c9aea7561ab241b7cdb26e7af7['checked']
=
(boolean)$_384d479d3c396afbfb7e832d2d450608e3145d55->getData($_54323be853e934686a9fca46e2f91c8ead26624e['name']);
}
$_64af779d81eb451f44e50893e14780ab31bf8125->addField($_54323be853e934686a9fca46e2f91c8ead26624e['name'],
$_54323be853e934686a9fca46e2f91c8ead26624e['type'],$_34e10a97491b23c9aea7561ab241b7cdb26e7af7);
}
}
$_770c1e8f1753a285f5ae6a3cad9b0a0df66730d6
=
$_64af779d81eb451f44e50893e14780ab31bf8125->addField('temp_ele_for_script',
'hidden',
array(
'name'
=>
'temp_ele_for_script',
));
$_e3ce98a6b1b926c089fe249b4f91a540b04af6ac
=
'';
if
(Mage::getModel('sublogin/source_formfields')->isFieldAllowed('expire_date'))
{
$_e3ce98a6b1b926c089fe249b4f91a540b04af6ac
.=
'
			document.observe("dom:loaded", function() {
				var currentDate = new Date('.(time()*1000).');
				
				// The number of milliseconds in one day
				var ONE_DAY = 1000 * 60 * 60 * 24
				// dateformat which gets used
				var dateFormat = "%Y-%m-%d";

				function days_between(date1, date2) {
					// Convert both dates to milliseconds
					var date1_ms = date1.getTime();
					var date2_ms = date2.getTime();
					// Calculate the difference in milliseconds
					var difference_ms = date2_ms - date1_ms;
					// Convert back to days and return
					return Math.ceil(difference_ms/ONE_DAY);
				}

				function generatePassword() {
					var length = 6,
						charset = "abcdefghijklnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789",
						retVal = "";
					for (var i = 0, n = charset.length; i < length; ++i) {
						retVal += charset.charAt(Math.floor(Math.random() * n));
					}
					return retVal;
				}

				function updateDaysFromDates(currentDate, expDate) {
					if (days_between(currentDate, expDate) < 1) // only update positive days
						return;
					$("days_to_expire").value = days_between(currentDate, expDate);
					$("expire_date").value = expDate.print(dateFormat);
				}
				
				function updateDateFromDays(currentDate, days) {
					if (days == "" || !parseInt(days))
						$("expire_date").value = "";
					else {
						var newDate = new Date(currentDate.getTime() + days * ONE_DAY);
						$("expire_date").value = newDate.print(dateFormat);
					}
				}

				$("expire_date").observe("change", function(event) {
					newDateFormated = Date.parseDate($(this).value, dateFormat);
					updateDaysFromDates(currentDate, newDateFormated);
				});

				// register days_to_expire handler - when editing this field the expire_date should be updated
				$("days_to_expire").observe("change", function(event) {
					var days = $("days_to_expire").value;
					updateDateFromDays(currentDate, days);
				});

				if ($("id") !== null)
				{
					var exp_date = Date.parseDate($("expire_date").value, dateFormat);
					updateDaysFromDates(currentDate, exp_date);
				}
				else
				{
					$("password").value = generatePassword();
					$("days_to_expire").value = 90;
					$("active").checked = true;
					$("send_backendmails").checked = true;
					$("create_sublogins").checked = false;
					updateDateFromDays(currentDate, 90);
				}

				/** when an inactive field gets activated - set the days back to 90 */
				if ($("active"))
				{
					$("active").observe("change", function(event) {
						var el = $(this);
						if (el.checked) {
							var id = el.readAttribute("rel");
							$("days_to_expire").value = 90;
							var days = $("days_to_expire").value;
							updateDateFromDays(currentDate, days);
						}
					});
				}
			});';
}
if
($_e3ce98a6b1b926c089fe249b4f91a540b04af6ac
!=
'')
{
$_e3ce98a6b1b926c089fe249b4f91a540b04af6ac
=
'<script type="text/javascript">'.$_e3ce98a6b1b926c089fe249b4f91a540b04af6ac.'</script>';
$_770c1e8f1753a285f5ae6a3cad9b0a0df66730d6->setAfterElementHtml($_e3ce98a6b1b926c089fe249b4f91a540b04af6ac);
}
$_fb05a43927cff438da7fdbd15552ff3227bd4b96->setUseContainer(true);
$this->setForm($_fb05a43927cff438da7fdbd15552ff3227bd4b96);
return
parent::_prepareForm();
}
}
