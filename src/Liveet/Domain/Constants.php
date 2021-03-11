<?php

namespace Liveet\Domain;

class Constants
{
  const PRODUCTION_HOST = '';
  const DEVELOPMENT_HOST = 'localhost';
  const DEVELOPMENT_BASE_PATH = "/liveet/liveet-apis";
  const PRODUCTION_BASE_PATH = "";

  const USERTYPE_ADMIN = 0;
  const USER_TYPE_ORGANIZATION = 1;
  const USERTYPE_USER = 2;


  const USER_VERIFIED = 1;
  const EMAIL_VERIFIED = 1;

  const DEFAULT_RESET_PASSWORD = 'Liveet_12345';

  const PRIVILEDGE_ADMIN_EVENT = "EVENT";
  const PRIVILEDGE_ADMIN_ORGANISER = "ORGANISER";
  const PRIVILEDGE_ADMIN_ACTIVITY_LOG = "ACTIVITY_LOG";
  const PRIVILEDGE_ADMIN_REPORT = "REPORT";
  const PRIVILEDGE_ADMIN_ADMIN = "ADMIN";

  const ERROR_NOT_FOUND = "Not found";
  const ERROR_EMPTY_DATA = 'No more data';

  const IMAGE_PATH = "assets/images";
  const IMAGE_TYPES_ACCEPTED = ["jpg", "jpeg", "png", "svg"];
}
