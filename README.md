# vormiaqueryphp

[![Packagist](https://img.shields.io/packagist/v/vormiaphp/vormiaqueryphp.svg)](https://packagist.org/packages/vormiaphp/vormiaqueryphp)
[![GitHub](https://img.shields.io/github/stars/vormiaphp/vormiaqueryphp.svg)](https://github.com/vormiaphp/vormiaqueryphp)

Laravel middleware and helpers for VormiaQuery encrypted API integration.

## Installation

### Using Artisan Command (Recommended)

1. Install via Composer:

```bash
composer require vormiaphp/vormiaqueryphp
composer require phpseclib/phpseclib
```

2. Run the installation command:

```bash
php artisan vormiaquery:install
```

This command will:

- Prompt you to install Sanctum API features if not already installed (Laravel 12+)
- Add VormiaQuery environment variables to your `.env` and `.env.example` files
- Prompt you to publish CORS configuration if not already published

You will be interactively asked to run:

- `php artisan install:api` (for Sanctum)
- `php artisan vendor:publish --tag=cors` (for CORS)

3. Add your RSA keys to `.env`:

```env
VORMIA_PRIVATE_KEY="<contents of vormia_private.pem>"
VORMIA_PUBLIC_KEY="<contents of vormia_public.pem>"
```

### Uninstallation

To remove VormiaQuery integration:

```bash
php artisan vormiaquery:uninstall
```

This command will:

- Remove VormiaQuery environment variables from `.env` and `.env.example` files
- Remove CORS configuration file

### Update

To update VormiaQuery integration (re-run setup steps):

```bash
php artisan vormiaquery:update
```

This command will:

- Re-apply environment variables and configuration as needed
- Prompt for any new setup steps in future versions

---

**Note:**

- There is currently no separate `update` command. Use the install command to re-run setup steps as needed.

## JavaScript Client Package

For optimal performance and RSA encryption support, install the companion JavaScript package:

```bash
npm install vormiaqueryjs
```

For complete documentation and examples, visit:

- [GitHub Repository](https://github.com/vormiaphp/vormiaqueryjs)
- [NPM Package](https://www.npmjs.com/package/vormiaqueryjs)

## Middleware Usage

Register the middleware in your `app/Http/Kernel.php`:

```php
protected $routeMiddleware = [
    // ...
    $middleware->alias([
        'vormia.decrypt' => \VormiaQueryPhp\Http\Middleware\DecryptVormiaRequest::class,
        'vormia.encrypt' => \VormiaQueryPhp\Http\Middleware\EncryptVormiaResponse::class,
    ]);
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
