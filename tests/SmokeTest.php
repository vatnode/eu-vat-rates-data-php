<?php

declare(strict_types=1);

namespace vatnode\EuVatRates\Tests;

use PHPUnit\Framework\TestCase;
use vatnode\EuVatRates\EuVatRates;

final class SmokeTest extends TestCase
{
    public function testDeIsEuMember(): void
    {
        $this->assertTrue(EuVatRates::isEuMember('DE'));
    }

    public function testGbIsNotEuMember(): void
    {
        $this->assertFalse(EuVatRates::isEuMember('GB'));
    }

    public function testNoIsNotEuMember(): void
    {
        $this->assertFalse(EuVatRates::isEuMember('NO'));
    }

    public function testDatasetHas45Countries(): void
    {
        $this->assertCount(45, EuVatRates::getAllRates());
    }

    public function testAllStandardRatesPositive(): void
    {
        foreach (EuVatRates::getAllRates() as $code => $rate) {
            $this->assertGreaterThan(0, $rate['standard'], "$code: standard rate is {$rate['standard']}");
        }
    }

    public function testEuMemberFieldIsBool(): void
    {
        foreach (EuVatRates::getAllRates() as $code => $rate) {
            $this->assertIsBool($rate['eu_member'], "$code: eu_member is not bool");
        }
    }

    public function testAllVatNamesNonEmpty(): void
    {
        foreach (EuVatRates::getAllRates() as $code => $rate) {
            $this->assertIsString($rate['vat_name'], "$code: vat_name is not string");
            $this->assertNotEmpty($rate['vat_name'], "$code: vat_name is empty");
        }
    }

    public function testDataVersionFormat(): void
    {
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', EuVatRates::dataVersion());
    }

    public function testUnknownCountryReturnsNull(): void
    {
        $this->assertNull(EuVatRates::getRate('XX'));
    }

    public function testValidateFormatAcceptsWellFormedIds(): void
    {
        $this->assertTrue(EuVatRates::validateFormat('ATU12345678'));
        $this->assertTrue(EuVatRates::validateFormat('DE123456789'));
        $this->assertTrue(EuVatRates::validateFormat('atu12345678'));
    }

    public function testValidateFormatRejectsMalformedInput(): void
    {
        $this->assertFalse(EuVatRates::validateFormat(''));
        $this->assertFalse(EuVatRates::validateFormat('INVALID'));
        $this->assertFalse(EuVatRates::validateFormat('DE12'));
    }

    /** Greek VAT numbers carry the VIES prefix EL while the dataset keys Greece as GR. */
    public function testValidateFormatHandlesGreekElPrefix(): void
    {
        $this->assertTrue(EuVatRates::validateFormat('EL123456789'));
        $this->assertTrue(EuVatRates::validateFormat('el123456789'));
        $this->assertFalse(EuVatRates::validateFormat('EL12345678'));
    }
}
