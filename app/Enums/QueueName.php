<?php

namespace App\Enums;

enum QueueName: string
{
    case DEFAULT = 'default';
    case EMAILS = 'emails';
    case NOTIFICATIONS = 'notifications';
}
