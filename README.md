# vormiaqueryphp

[![Packagist](https://img.shields.io/packagist/v/vormiaphp/vormiaqueryphp.svg)](https://packagist.org/packages/vormiaphp/vormiaqueryphp)
[![GitHub](https://img.shields.io/github/stars/vormiaphp/vormiaqueryphp.svg)](https://github.com/vormiaphp/vormiaqueryphp)

Laravel middleware and helpers for VormiaQuery encrypted API integration.

## Installation

1. Install via Composer:

```bash
composer require vormiaphp/vormiaqueryphp
composer require phpseclib/phpseclib
```

2. Add your RSA keys to `.env`:

```env
VORMIA_PRIVATE_KEY="<contents of vormia_private.pem>"
VORMIA_PUBLIC_KEY="<contents of vormia_public.pem>"
```

## Middleware Usage

Register the middleware in your `app/Http/Kernel.php`:

```php
protected $routeMiddleware = [
    // ...
    'vormia.decrypt' => \VormiaQueryPhp\Http\Middleware\DecryptVormiaRequest::class,
    'vormia.encrypt' => \VormiaQueryPhp\Http\Middleware\EncryptVormiaResponse::class,
];
```

Apply the middleware to your API routes:

```php
Route::middleware(['vormia.decrypt', 'vormia.encrypt'])->group(function () {
    Route::post('/vormia/data', [\VormiaQueryPhp\Http\Controllers\VormiaQueryController::class, 'loadData']);
});
```

## Example Controller

```php
namespace VormiaQueryPhp\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

class VormiaQueryController extends Controller
{
    public function loadData(Request $request)
    {
        $data = [
            ['id' => 1, 'name' => 'Alpha'],
            ['id' => 2, 'name' => 'Beta'],
        ];

        $response = [
            'response' => $data,
            'message' => 'Success',
            'meta' => [
                'total' => count($data),
                'page' => 1,
                'perPage' => count($data),
            ],
        ];

        return response()->json($response);
    }
}
```

## How It Works

- **DecryptVormiaRequest**: Decrypts incoming requests with the private key if an `encrypted` field is present.
- **EncryptVormiaResponse**: Encrypts outgoing responses with the public key if the request expects encryption (via header or flag).
- **Standard VormiaQuery Response**: Always return data in the format:
  ```json
  {
    "response": [...],
    "message": "Success",
    "meta": { "total": 2, "page": 1, "perPage": 2 }
  }
  ```

## Security

- Never expose your private key in frontend/browser code.
- Rotate keys as needed and keep them secure.

## Security Helper Examples

### 1. Domain Whitelisting

```php
use VormiaQueryPhp\Helpers\VormiaSecurityHelper;

if (!VormiaSecurityHelper::isDomainAllowed()) {
    abort(403, 'Domain not allowed');
}
```

### 2. API Token Validation

```php
use VormiaQueryPhp\Helpers\VormiaSecurityHelper;

if (!VormiaSecurityHelper::validateApiToken()) {
    abort(401, 'Invalid API token');
}
```

### 3. User Role and Ability Checks

```php
use VormiaQueryPhp\Helpers\VormiaSecurityHelper;

if (!VormiaSecurityHelper::userHasRole('admin')) {
    abort(403, 'Admin role required');
}

if (!VormiaSecurityHelper::userCan('edit-posts')) {
    abort(403, 'Permission denied');
}
```

### 4. Rate Limiting

```php
use VormiaQueryPhp\Helpers\VormiaSecurityHelper;

$key = request()->ip(); // or use Auth::id() for user-based
if (!VormiaSecurityHelper::rateLimit($key, 10, 60)) {
    abort(429, 'Too many requests');
}
```

### 5. IP Whitelisting

```php
use VormiaQueryPhp\Helpers\VormiaSecurityHelper;

if (!VormiaSecurityHelper::isIpAllowed()) {
    abort(403, 'IP not allowed');
}
```

### 6. Advanced Token Validation

```php
use VormiaQueryPhp\Helpers\VormiaSecurityHelper;

if (!VormiaSecurityHelper::validateApiToken('advanced')) {
    abort(401, 'Invalid or insufficient token');
}
```

### 7. Combining Multiple Security Checks

```php
use VormiaQueryPhp\Helpers\VormiaSecurityHelper;

if (!VormiaSecurityHelper::isDomainAllowed()) {
    abort(403, 'Domain not allowed');
}
if (!VormiaSecurityHelper::isIpAllowed()) {
    abort(403, 'IP not allowed');
}
if (!VormiaSecurityHelper::validateApiToken('advanced')) {
    abort(401, 'Invalid or insufficient token');
}
if (!VormiaSecurityHelper::rateLimit(request()->ip(), 5, 60)) {
    abort(429, 'Too many requests');
}
```

### 8. Using Security Checks in Middleware

```php
namespace App\Http\Middleware;

use Closure;
use VormiaQueryPhp\Helpers\VormiaSecurityHelper;

class VormiaApiSecurity
{
    public function handle($request, Closure $next)
    {
        if (!VormiaSecurityHelper::isDomainAllowed()) {
            abort(403, 'Domain not allowed');
        }
        if (!VormiaSecurityHelper::isIpAllowed()) {
            abort(403, 'IP not allowed');
        }
        if (!VormiaSecurityHelper::validateApiToken('advanced')) {
            abort(401, 'Invalid or insufficient token');
        }
        if (!VormiaSecurityHelper::rateLimit($request->ip(), 10, 60)) {
            abort(429, 'Too many requests');
        }
        return $next($request);
    }
}
```

### 9. Custom Advanced Token Validation

```php
use VormiaQueryPhp\Helpers\VormiaSecurityHelper;

// Override the advancedTokenValidation method in your own helper or extend VormiaSecurityHelper
class MySecurityHelper extends VormiaSecurityHelper
{
    public static function advancedTokenValidation($token)
    {
        // Example: check token in a custom DB table
        return \DB::table('api_tokens')->where('token', $token)->where('active', 1)->exists();
    }
}

if (!MySecurityHelper::validateApiToken('advanced')) {
    abort(401, 'Invalid or insufficient token');
}
```

### 10. Brute-force Protection

```php
use VormiaQueryPhp\Helpers\VormiaSecurityHelper;

$key = request()->ip();
if (!VormiaSecurityHelper::bruteForceProtect($key, 5, 300)) {
    VormiaSecurityHelper::logSecurityEvent('Brute-force blocked', ['ip' => $key]);
    abort(429, 'Too many failed attempts');
}
// On successful login or action:
// VormiaSecurityHelper::resetBruteForce($key);
```

### 11. Request Logging

```php
use VormiaQueryPhp\Helpers\VormiaSecurityHelper;

VormiaSecurityHelper::logSecurityEvent('Sensitive action', ['action' => 'delete', 'resource_id' => 123]);
```

### 12. Security Event Hooks

```php
use VormiaQueryPhp\Helpers\VormiaSecurityHelper;

VormiaSecurityHelper::onSecurityEvent('token_invalid', function($event, $context) {
    // Send alert, log, or trigger custom logic
    \Log::warning("Security event: $event", $context);
});
```

You can use these helpers in controllers, middleware, or route closures to add extra security to your VormiaQuery endpoints.

---

For more, see the VormiaQuery JavaScript package documentation.
