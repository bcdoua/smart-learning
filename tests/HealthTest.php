<?php

namespace App\Tests;

use App\Kernel;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class HealthTest extends TestCase
{
    public function testHealthRepondOk(): void
    {
        $kernel = new Kernel('test', false);
        $response = $kernel->handle(Request::create('/health'));

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('ok', json_decode((string) $response->getContent(), true)['status']);
        self::assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));

        $kernel->shutdown();
    }
}
