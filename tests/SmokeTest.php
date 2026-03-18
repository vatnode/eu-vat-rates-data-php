<?php

declare(strict_types=1);

namespace VATNode\EuVatRates\Tests;

use PHPUnit\Framework\TestCase;
use VATNode\EuVatRates\EuVatRates;

final class SmokeTest extends TestCase
{
    public function testDeStandardRate(): void
    {
        $this->assertSame(19.0, EuVatRates::getStandardRate('DE'));
    }

    public function testEeStandardRate(): void
    {
        $this->assertSame(24.0, EuVatRates::getStandardRate('EE'));
    }

    public function testFrIsEuMember(): void
    {
        $this->assertTrue(EuVatRates::isEuMember('FR'));
    }

    public function testGbIsNotEuMember(): void
    {
        $this->assertFalse(EuVatRates::isEuMember('GB'));
    }

    public function testDatasetHas44Countries(): void
    {
        $this->assertCount(44, EuVatRates::getAllRates());
    }

    public function testEuMemberField(): void
    {
        $this->assertTrue(EuVatRates::getRate('DE')['eu_member']);
        $this->assertFalse(EuVatRates::getRate('NO')['eu_member']);
    }

    public function testDataVersionFormat(): void
    {
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', EuVatRates::dataVersion());
    }
}
