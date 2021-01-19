<?php

use BUS_LOCATOR\Domain\Constants;

$db_host = 'localhost';
$db_name = 'touchandpay_lamata_bus_locator';
$db_user = 'root';
$db_pass = '';

$basePath = Constants::DEVELOPMENT_BASE_PATH;

if ($_SERVER['HTTP_HOST'] == Constants::PRODUCTION_HOST) {
  $db_host = 'localhost';
  $db_name = 'touchandpay_lamata_bus_locator';
  $db_user = 'touchandpay_admin';
  $db_pass = 'sanwo.me199x';

  $basePath = Constants::PRODUCTION_BASE_PATH;
}
