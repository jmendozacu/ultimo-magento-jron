<?php

$_3121821835ff0a946ba794d7ae94b03cbd811eaa
=
$this;
$_3121821835ff0a946ba794d7ae94b03cbd811eaa->startSetup();
if
(!$_3121821835ff0a946ba794d7ae94b03cbd811eaa->tableExists($_3121821835ff0a946ba794d7ae94b03cbd811eaa->getTable('customer_sublogin_budget')))
{
$_3121821835ff0a946ba794d7ae94b03cbd811eaa->run("
        CREATE TABLE {$_3121821835ff0a946ba794d7ae94b03cbd811eaa->getTable('customer_sublogin_budget')} (
          `budget_id` int(10) unsigned NOT NULL auto_increment,
          `sublogin_id` int(10) unsigned NOT NULL DEFAULT '0',
          `year` VARCHAR( 4 ) NOT NULL default '',
          `month` VARCHAR( 2 ) NOT NULL default '',
          `day` VARCHAR( 2 ) NOT NULL default '',
          `per_order` decimal( 10, 4 ) NOT NULL,
          `amount` decimal( 10, 4 ) NOT NULL,
          PRIMARY KEY (`budget_id`),
          CONSTRAINT `FK_CUSTOMER_SUBLOGIN_BUDGET_SUBLOGIN_ID` FOREIGN KEY (`sublogin_id`) REFERENCES {$this->getTable('customer_sublogin')} (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
    ");
}
$_3121821835ff0a946ba794d7ae94b03cbd811eaa->endSetup();