<?php

$_3121821835ff0a946ba794d7ae94b03cbd811eaa
=
$this;
$_3121821835ff0a946ba794d7ae94b03cbd811eaa->startSetup();
$_3121821835ff0a946ba794d7ae94b03cbd811eaa->getConnection()->addColumn($_3121821835ff0a946ba794d7ae94b03cbd811eaa->getTable('customer_sublogin'),
'is_subscribed',
'TINYINT(1)');
$_3121821835ff0a946ba794d7ae94b03cbd811eaa->getConnection()->addColumn($_3121821835ff0a946ba794d7ae94b03cbd811eaa->getTable('customer_sublogin'),
'prefix',
'VARCHAR(15)');
$_3121821835ff0a946ba794d7ae94b03cbd811eaa->endSetup();