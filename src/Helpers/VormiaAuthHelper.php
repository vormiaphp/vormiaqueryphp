<?php

namespace VormiaQueryPhp\Helpers;

use Illuminate\Support\Facades\Auth;

class VormiaAuthHelper
{
    public static function isAuthenticated()
    {
        return Auth::check();
    }

    public static function user()
    {
        return Auth::user();
    }

    public static function token()
    {
        return Auth::user() ? Auth::user()->token() : null;
    }

    public static function requestDomain()
    {
        return request()->getHost();
    }
}
