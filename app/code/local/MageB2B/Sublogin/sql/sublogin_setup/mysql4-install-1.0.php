<?php

$_3121821835ff0a946ba794d7ae94b03cbd811eaa
=
$this;
$_3121821835ff0a946ba794d7ae94b03cbd811eaa->startSetup();
if
(!$_3121821835ff0a946ba794d7ae94b03cbd811eaa->tableExists($_3121821835ff0a946ba794d7ae94b03cbd811eaa->getTable('customer_sublogin')))
{
$_3121821835ff0a946ba794d7ae94b03cbd811eaa->run("
        CREATE TABLE {$_3121821835ff0a946ba794d7ae94b03cbd811eaa->getTable('customer_sublogin')} (
          `id` int(10) unsigned NOT NULL auto_increment,
          `entity_id` int(10) unsigned NOT NULL DEFAULT '0',
          `customer_id` VARCHAR( 255 ) NOT NULL,
          `email` VARCHAR( 255 ) NOT NULL,
          `password` VARCHAR( 255 ) NOT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
    ");
}
