<?php

namespace VormiaQueryPhp\Helpers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class VormiaSecurityHelper
{
    /**
     * Check if the request domain is whitelisted.
     * @param array|null $allowedDomains
     * @return bool
     */
    public static function isDomainAllowed($allowedDomains = null)
    {
        $domain = request()->getHost();
        $allowed = $allowedDomains ?? config('vormiaquery.allowed_domains', []);
        return in_array($domain, $allowed);
    }

    /**
     * Validate the API token from the request (Bearer or token param).
     * @param string $mode 'normal' or 'advanced'
     * @return bool
     */
    public static function validateApiToken($mode = 'normal')
    {
        $token = request()->bearerToken() ?: request('token');
        if ($mode === 'advanced') {
            // Example: check token against a custom table or external service
            // Replace with your own logic
            return self::advancedTokenValidation($token);
        }
        // Default: use Laravel Auth once
        return Auth::once(['api_token' => $token]);
    }

    /**
     * Example advanced token validation (customize as needed).
     * @param string|null $token
     * @return bool
     */
    public static function advancedTokenValidation($token)
    {
        // Example: check token in a custom table or call an external service
        // return DB::table('api_tokens')->where('token', $token)->exists();
        // For demo, just check length
        return is_string($token) && strlen($token) > 32;
    }

    /**
     * Check if the request IP is whitelisted.
     * @param array|null $allowedIps
     * @return bool
     */
    public static function isIpAllowed($allowedIps = null)
    {
        $ip = request()->ip();
        $allowed = $allowedIps ?? config('vormiaquery.allowed_ips', []);
        return in_array($ip, $allowed);
    }

    /**
     * Rate limit a request by key (e.g., IP or user ID).
     * @param string $key
     * @param int $maxAttempts
     * @param int $decaySeconds
     * @return bool True if allowed, false if rate limited
     */
    public static function rateLimit($key, $maxAttempts = 60, $decaySeconds = 60)
    {
        $cacheKey = 'vormiaquery:rate:' . $key;
        $attempts = Cache::get($cacheKey, 0);
        if ($attempts >= $maxAttempts) {
            return false;
        }
        Cache::put($cacheKey, $attempts + 1, $decaySeconds);
        return true;
    }

    /**
     * Brute-force protection: block after N failed attempts for a key (e.g., IP or user).
     * @param string $key
     * @param int $maxAttempts
     * @param int $decaySeconds
     * @return bool True if allowed, false if blocked
     */
    public static function bruteForceProtect($key, $maxAttempts = 5, $decaySeconds = 300)
    {
        $cacheKey = 'vormiaquery:brute:' . $key;
        $attempts = Cache::get($cacheKey, 0);
        if ($attempts >= $maxAttempts) {
            return false;
        }
        Cache::put($cacheKey, $attempts + 1, $decaySeconds);
        return true;
    }

    /**
     * Reset brute-force attempts for a key (e.g., after successful login).
     * @param string $key
     */
    public static function resetBruteForce($key)
    {
        $cacheKey = 'vormiaquery:brute:' . $key;
        Cache::forget($cacheKey);
    }

    /**
     * Log a security-relevant request or event.
     * @param string $event
     * @param array $context
     */
    public static function logSecurityEvent($event, $context = [])
    {
        Log::channel('security')->info($event, $context + [
            'ip' => request()->ip(),
            'user_id' => Auth::id(),
            'url' => request()->fullUrl(),
        ]);
    }

    /**
     * Call a custom callback on a security event (e.g., block, alert).
     * @param string $event
     * @param callable $callback
     * @param array $context
     */
    public static function onSecurityEvent($event, callable $callback, $context = [])
    {
        $callback($event, $context + [
            'ip' => request()->ip(),
            'user_id' => Auth::id(),
            'url' => request()->fullUrl(),
        ]);
    }

    /**
     * Check if the authenticated user has a given role.
     * @param string $role
     * @return bool
     */
    public static function userHasRole($role)
    {
        $user = Auth::user();
        return $user && method_exists($user, 'hasRole') ? $user->hasRole($role) : false;
    }

    /**
     * Check if the authenticated user has a given ability/permission.
     * @param string $ability
     * @return bool
     */
    public static function userCan($ability)
    {
        $user = Auth::user();
        return $user && method_exists($user, 'can') ? $user->can($ability) : false;
    }
}
