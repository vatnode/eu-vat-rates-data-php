<?php

declare(strict_types=1);

namespace VATNode\EuVatRates;

/**
 * EU VAT rates for all 27 member states + UK.
 *
 * Data sourced from the European Commission TEDB (Taxes in Europe Database).
 * Updated daily, published automatically when rates change.
 *
 * Usage:
 *   $rate = EuVatRates::getRate('FI');
 *   // ['country' => 'Finland', 'currency' => 'EUR', 'standard' => 25.5, ...]
 *
 *   EuVatRates::getStandardRate('DE');  // 19.0
 *   EuVatRates::isEuMember('FR');       // true
 *   EuVatRates::dataVersion();          // "2026-02-25"
 */
final class EuVatRates
{
    private static ?array $dataset = null;

    private static function load(): array
    {
        if (self::$dataset === null) {
            $path = __DIR__ . '/../data/eu-vat-rates-data.json';
            $json = file_get_contents($path);
            if ($json === false) {
                throw new \RuntimeException('eu-vat-rates: cannot read data file: ' . $path);
            }
            self::$dataset = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        }
        return self::$dataset;
    }

    /**
     * Return the full VAT rate array for a country, or null if not found.
     *
     * @param  string $countryCode  ISO 3166-1 alpha-2 code (e.g. "FI", "DE", "GB")
     * @return array{country:string,currency:string,standard:float,reduced:float[],super_reduced:float|null,parking:float|null}|null
     */
    public static function getRate(string $countryCode): ?array
    {
        $rates = self::load()['rates'];
        return $rates[strtoupper($countryCode)] ?? null;
    }

    /**
     * Return the standard VAT rate for a country, or null if not found.
     *
     * @param  string $countryCode  ISO 3166-1 alpha-2 code
     * @return float|null
     */
    public static function getStandardRate(string $countryCode): ?float
    {
        $rate = self::getRate($countryCode);
        return $rate ? (float) $rate['standard'] : null;
    }

    /**
     * Return all 28 country rate arrays keyed by ISO country code.
     *
     * @return array<string, array>
     */
    public static function getAllRates(): array
    {
        return self::load()['rates'];
    }

    /**
     * Return true if the country code is in the dataset (EU-27 + GB).
     *
     * @param  string $countryCode  ISO 3166-1 alpha-2 code
     * @return bool
     */
    public static function isEuMember(string $countryCode): bool
    {
        return isset(self::load()['rates'][strtoupper($countryCode)]);
    }

    /**
     * Return the ISO 8601 date when data was last fetched from EC TEDB.
     *
     * @return string  e.g. "2026-02-25"
     */
    public static function dataVersion(): string
    {
        return self::load()['version'];
    }
}
