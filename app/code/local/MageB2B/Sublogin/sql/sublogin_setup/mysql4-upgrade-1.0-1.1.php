<?php

$_3121821835ff0a946ba794d7ae94b03cbd811eaa
=
$this;
$_3121821835ff0a946ba794d7ae94b03cbd811eaa->startSetup();
$_3121821835ff0a946ba794d7ae94b03cbd811eaa->getConnection()->addColumn($_3121821835ff0a946ba794d7ae94b03cbd811eaa->getTable('customer_sublogin'),
'rp_token',
'varchar(255) NOT NULL DEFAULT "0"');
$_3121821835ff0a946ba794d7ae94b03cbd811eaa->getConnection()->addColumn($_3121821835ff0a946ba794d7ae94b03cbd811eaa->getTable('customer_sublogin'),
'rp_token_created_at',
'varchar(255) NOT NULL DEFAULT "0"');
