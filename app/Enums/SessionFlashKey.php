<?php

namespace App\Enums;

enum SessionFlashKey: string
{
    case CMS_SUCCESS = 'CMS_SUCCESS';
    case CMS_ERROR = 'CMS_ERROR';
    case CMS_LOGIN_SUCCESS = 'CMS_LOGIN_SUCCESS';
    case CMS_EMAIL_VERIFIED = 'CMS_EMAIL_VERIFIED';
    case CMS_EMAIL_UPDATE_CONFIRMED = 'CMS_EMAIL_UPDATE_CONFIRMED';
}
