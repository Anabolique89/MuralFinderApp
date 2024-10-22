<?php

namespace App\Enums;

enum ActivityType: string
{
    case POST_LIKED = 'post_liked';
    case POST_COMMENTED = 'post_commented';
    case ARTWORK_LIKED = 'artwork_liked';
    case ARTWORK_COMMENTED = 'artwork_commented';
    case POST_SHARED = 'post_shared';

    case USER_FOLLOWED = 'user_followed';
}
