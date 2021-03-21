<?php

namespace Liveet\Domain;

class Constants
{
  const PRODUCTION_HOST = "liveet.rollcallservice.com";
  const DEVELOPMENT_HOST = "localhost";
  const DEVELOPMENT_BASE_PATH = "/liveet/liveet-apis";
  const PRODUCTION_BASE_PATH = "/liveet-apis";

  const ENVIRONMENT_DEVELOPMENT = "development";

  const USERTYPE_ADMIN = "ADMIN";
  const USERTYPE_ORGANISER_ADMIN = "ORGANIZER_ADMIN";
  const USERTYPE_ORGANISER_STAFF = "ORGANIZER_STAFF";
  const USERTYPE_USER = "USER";


  const USER_VERIFIED = 1;
  const EMAIL_VERIFIED = 1;

  const DEFAULT_RESET_PASSWORD = "Liveet_12345";

  const PRIVILEDGE_ADMIN_EVENT = "EVENT";
  const PRIVILEDGE_ADMIN_ORGANISER = "ORGANISER";
  const PRIVILEDGE_ADMIN_ACTIVITY_LOG = "ACTIVITY_LOG";
  const PRIVILEDGE_ADMIN_REPORT = "REPORT";
  const PRIVILEDGE_ADMIN_ADMIN = "ADMIN";

  const PRIVILEDGE_ORGANISER_EVENT = "EVENT";
  const PRIVILEDGE_ORGANISER_ORGANISER = "ORGANISER";
  const PRIVILEDGE_ORGANISER_ACTIVITY_LOG = "ACTIVITY_LOG";
  const PRIVILEDGE_ORGANISER_REPORT = "REPORT";

  const ERROR_NOT_FOUND = "Not found";
  const ERROR_EMPTY_DATA = "No more data";

  const IMAGE_PATH = "assets/images/";
  const IMAGE_TYPES_ACCEPTED = ["jpg", "jpeg", "png", "svg"];
}
