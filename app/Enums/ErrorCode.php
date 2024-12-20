<?php

namespace App\Enums;

enum ErrorCode: string
{
    case INVALID_CREDENTIALS = 'INVALID_CREDENTIALS_ERROR';
    case TOO_MANY_REQUESTS = 'TOO_MANY_REQUESTS_ERROR';
    case EMAIL_ALREADY_VERIFIED = 'EMAIL_ALREADY_VERIFIED_ERROR';
    case VALIDATION = 'VALIDATION_ERROR';
    case RESOURCE_NOT_FOUND = 'RESOURCE_NOT_FOUND_ERROR';
    case UNAUTHORIZED = 'UNAUTHORIZED_ERROR';
    case UNKNOWN_ROUTE = 'UNKNOWN_ROUTE_ERROR';
    case SERVER = 'SERVER_ERROR';
    case PAYLOAD_TOO_LARGE = 'PAYLOAD_TOO_LARGE_ERROR';
    case EXTERNAL_ACCOUNT_EMAIL_CONFLICT = 'EXTERNAL_ACCOUNT_EMAIL_CONFLICT_ERROR';
    case ACCOUNT_DEACTIVATED = 'ACCOUNT_DEACTIVATED_ERROR';
    case BAD_REQUEST = 'BAD_REQUEST_ERROR';
}
