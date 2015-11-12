<?php

$_3121821835ff0a946ba794d7ae94b03cbd811eaa
=
$this;
$_3121821835ff0a946ba794d7ae94b03cbd811eaa->startSetup();
if
(!$_3121821835ff0a946ba794d7ae94b03cbd811eaa->tableExists($_3121821835ff0a946ba794d7ae94b03cbd811eaa->getTable('customer_sublogin_acl')))
{
$_3121821835ff0a946ba794d7ae94b03cbd811eaa->run("
        CREATE TABLE {$_3121821835ff0a946ba794d7ae94b03cbd811eaa->getTable('customer_sublogin_acl')} (
          `acl_id` int(10) unsigned NOT NULL auto_increment,
          `name` varchar(30) NOT NULL DEFAULT '',
          `identifier` VARCHAR( 255 ) NOT NULL,
          PRIMARY KEY (`acl_id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
    ");
}
$_3121821835ff0a946ba794d7ae94b03cbd811eaa->getConnection()->addColumn($_3121821835ff0a946ba794d7ae94b03cbd811eaa->getTable('customer_sublogin'),
'acl',
'text');
$_3121821835ff0a946ba794d7ae94b03cbd811eaa->endSetup();