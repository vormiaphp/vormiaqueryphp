<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use PHPUnit\Framework\TestCase;
use phpseclib3\Crypt\RSA;
use VormiaQueryPhp\Http\Middleware\DecryptVormiaRequest;

class DecryptVormiaRequestTest extends TestCase
{
    public function testDecryptsAndMergesData()
    {
        // Generate test key pair
        $key = RSA::createKey(2048);
        $privateKey = $key->toString('PKCS1');
        $publicKey = $key->getPublicKey()->toString('PKCS1');

        // Encrypt test data
        $data = ['foo' => 'bar'];
        $rsa = RSA::loadPublicKey($publicKey);
        $encrypted = base64_encode($rsa->encrypt(json_encode($data)));

        // Mock request
        $request = Request::create('/test', 'POST', ['encrypted' => $encrypted]);
        // Set env for private key
        putenv("VORMIA_PRIVATE_KEY={$privateKey}");

        $middleware = new DecryptVormiaRequest();
        $middleware->handle($request, function ($req) use ($data) {
            $this->assertEquals('bar', $req->input('foo'));
            return response('ok');
        });
    }
}
