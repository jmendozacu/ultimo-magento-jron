<?php

$_3121821835ff0a946ba794d7ae94b03cbd811eaa
=
$this;
$_3121821835ff0a946ba794d7ae94b03cbd811eaa->startSetup();
$_3121821835ff0a946ba794d7ae94b03cbd811eaa->getConnection()->addColumn($_3121821835ff0a946ba794d7ae94b03cbd811eaa->getTable('customer_sublogin'),
'firstname',
'varchar(255) NOT NULL DEFAULT ""');
$_3121821835ff0a946ba794d7ae94b03cbd811eaa->getConnection()->addColumn($_3121821835ff0a946ba794d7ae94b03cbd811eaa->getTable('customer_sublogin'),
'lastname',
'varchar(255) NOT NULL DEFAULT ""');
$_3121821835ff0a946ba794d7ae94b03cbd811eaa->getConnection()->addColumn($_3121821835ff0a946ba794d7ae94b03cbd811eaa->getTable('customer_sublogin'),
'expire_date',
'DATE NOT NULL');
$_3121821835ff0a946ba794d7ae94b03cbd811eaa->getConnection()->addColumn($_3121821835ff0a946ba794d7ae94b03cbd811eaa->getTable('customer_sublogin'),
'active',
'TINYINT(1) NOT NULL');
