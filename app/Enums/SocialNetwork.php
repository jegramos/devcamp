<?php

namespace App\Enums;

use App\Enums\Traits\HasToArray;

enum SocialNetwork: string
{
    use HasToArray;

    case FACEBOOK = 'Facebook';
    case TWITTER = 'X/Twitter';
    case LINKEDIN = 'LinkedIn';
    case GITHUB = 'Github';
    case INSTAGRAM = 'Instagram';
    case YOUTUBE = 'Youtube';
    case WHATSAPP = 'WhatsApp';
    case VIBER = 'Viber';
}
