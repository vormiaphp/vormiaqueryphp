<?php

namespace VormiaQueryPhp\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use phpseclib3\Crypt\RSA;

class EncryptVormiaResponse
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        $publicKey = env('VORMIA_PUBLIC_KEY');
        if ($publicKey && ($request->expectsEncryptedResponse || $request->header('X-Vormia-Encrypted') === '1')) {
            $rsa = RSA::loadPublicKey($publicKey);
            $data = $response instanceof JsonResponse ? $response->getData(true) : $response->getContent();
            $encrypted = base64_encode($rsa->encrypt(json_encode($data)));
            return response()->json(['encrypted' => $encrypted]);
        }
        return $response;
    }
}
