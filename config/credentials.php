<?php

use Liveet\Domain\Constants;

$db_host = 'localhost';
$db_name = 'touchandpay_liveet';
$db_user = 'root';
$db_pass = '';

$basePath = Constants::DEVELOPMENT_BASE_PATH;

if ($_SERVER['HTTP_HOST'] == Constants::PRODUCTION_HOST) {
  $db_host = 'localhost';
  $db_name = 'touchandpay_liveet';
  $db_user = 'root';
  $db_pass = '';

  $basePath = Constants::PRODUCTION_BASE_PATH;
}
