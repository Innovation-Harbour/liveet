<?php

namespace Liveet\Domain;

class Constants
{
  const PRODUCTION_HOST = '';
  const DEVELOPMENT_HOST = 'localhost';
  const DEVELOPMENT_BASE_PATH = "/liveet/liveet-apis";
  const PRODUCTION_BASE_PATH = "";

  const USERTYPE_USER = 4;
  const USERTYPE_ADMIN = 0;
  const USER_TYPE_ORGANIZATION = 1;

  const TRANSACTION_TYPE_DEBIT  = 0;

  const TRANSACTION_PENDING = 0;
  const TRANSACTION_APPROVED = 1;
  const TRANSACTION_DISAPPROVED = 2;

  const TRANSACTION_CHECK_IN = 'CHECK_IN';
  const TRANSACTION_CHECK_OUT = 'CHECK_OUT';
  const TRANSACTION_CHECK_IN_OUT = 'CHECK_IN_OUT';

  const CARD_TYPE = ['TAP'];

  const USER_VERIFIED = 1;
  const EMAIL_VERIFIED = 1;

  const DEFAULT_RESET_PASSWORD = 'Liveet_12345';

  const PRIVILEDGE_CREATE_ADMIN = 0;
  const PRIVILEDGE_GET_ANY_ADMIN = 1;
  const PRIVILEDGE_UPDATE_ANY_ADMIN = 2;
  const PRIVILEDGE_RESET_PASSWORDS = 3;
  const PRIVILEDGE_DELETE_ANY_ADMIN = 4;
  const PRIVILEDGE_LOGOUT_ANY_ADMIN = 5;

  const ERROR_NOT_FOUND = "Not found";
  const ERROR_EMPTY_DATA = 'No more data';
}
