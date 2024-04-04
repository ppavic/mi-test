<?php

namespace App\Tests\Infrastructure;

use PHPUnit\Framework\TestCase;

class MainCommandTest extends TestCase
{
    public function testLookupThrowsExceptionWhenNoMapping(): void
    {
        $this->expectException(\RuntimeException::class);
    }
}