<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use VormiaQueryPhp\Services\VormiaResponseService;

class VormiaResponseServiceTest extends TestCase
{
    public function testFormatReturnsStandardStructure()
    {
        $data = [['id' => 1, 'name' => 'Test']];
        $result = VormiaResponseService::format($data, 'OK');
        $this->assertArrayHasKey('response', $result);
        $this->assertArrayHasKey('message', $result);
        $this->assertArrayHasKey('meta', $result);
        $this->assertEquals('OK', $result['message']);
        $this->assertEquals($data, $result['response']);
        $this->assertEquals(1, $result['meta']['total']);
    }
}
