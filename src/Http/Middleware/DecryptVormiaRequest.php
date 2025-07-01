<?php

namespace VormiaQueryPhp\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use phpseclib3\Crypt\RSA;

class DecryptVormiaRequest
{
    public function handle(Request $request, Closure $next)
    {
        $privateKey = env('VORMIA_PRIVATE_KEY');
        if ($request->has('encrypted') && $privateKey) {
            $rsa = RSA::loadPrivateKey($privateKey);
            $decrypted = $rsa->decrypt(base64_decode($request->input('encrypted')));
            $data = json_decode($decrypted, true);
            if (is_array($data)) {
                $request->merge($data);
            }
        }
        return $next($request);
    }
}
