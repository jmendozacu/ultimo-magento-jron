<?php

$_3121821835ff0a946ba794d7ae94b03cbd811eaa
=
$this;
$_3121821835ff0a946ba794d7ae94b03cbd811eaa->startSetup();
$_3121821835ff0a946ba794d7ae94b03cbd811eaa->run("UPDATE {$_3121821835ff0a946ba794d7ae94b03cbd811eaa->getTable('customer_sublogin')} set expire_date =ADDDATE(CURDATE(), INTERVAL 90 DAY), active=1;");
