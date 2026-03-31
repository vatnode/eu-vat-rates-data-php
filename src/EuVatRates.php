<?php

declare(strict_types=1);

namespace VATNode\EuVatRates;

/**
 * VAT rates for 44 European countries (EU-27 + 17 non-EU).
 *
 * EU rates sourced from the European Commission TEDB (Taxes in Europe Database),
 * checked daily. Non-EU rates maintained manually.
 *
 * Usage:
 *   $rate = EuVatRates::getRate('FI');
 *   // ['country' => 'Finland', 'currency' => 'EUR', 'eu_member' => true,
 *   //  'standard' => 25.5, 'reduced' => [10.0, 13.5], ...]
 *
 *   EuVatRates::getStandardRate('DE');  // 19.0
 *   EuVatRates::isEuMember('NO');       // false
 *   EuVatRates::isEuMember('FR');       // true
 *   EuVatRates::dataVersion();          // "2026-03-18"
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
     * @param  string $countryCode  ISO 3166-1 alpha-2 code (e.g. "FI", "DE", "NO")
     * @return array{country:string,currency:string,eu_member:bool,vat_name:string,vat_abbr:string,standard:float,reduced:float[],super_reduced:float|null,parking:float|null,format:string,pattern:string|null}|null
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
     * Return all 44 country rate arrays keyed by ISO country code.
     *
     * @return array<string, array>
     */
    public static function getAllRates(): array
    {
        return self::load()['rates'];
    }

    /**
     * Return true if the country is an EU-27 member state.
     *
     * Returns false for non-EU countries in the dataset (GB, NO, CH, etc.)
     * and for unknown country codes.
     *
     * @param  string $countryCode  ISO 3166-1 alpha-2 code
     * @return bool
     */
    public static function isEuMember(string $countryCode): bool
    {
        $rate = self::getRate($countryCode);
        return $rate ? (bool) $rate['eu_member'] : false;
    }

    /**
     * Return true if the country code is present in the dataset (all 44 countries).
     *
     * Use this to check dataset membership for any European country.
     * For EU membership specifically, use {@see isEuMember()}.
     *
     * @param  string $countryCode  ISO 3166-1 alpha-2 code
     * @return bool
     */
    public static function hasRate(string $countryCode): bool
    {
        return isset(self::load()['rates'][strtoupper($countryCode)]);
    }

    /**
     * Return true if $vatId matches the expected format for its country.
     *
     * Input must include the country code prefix (e.g. "ATU12345678").
     * Returns false when the country has no standardised format or the ID
     * does not match.
     *
     * Note: Greece uses the "EL" prefix, not "GR".
     *
     * @param  string $vatId  VAT number string including country code prefix
     * @return bool
     */
    public static function validateFormat(string $vatId): bool
    {
        $code = strtoupper(substr($vatId, 0, 2));
        $rate = self::getRate($code);
        if ($rate === null || empty($rate['pattern'])) {
            return false;
        }
        return (bool) preg_match('/' . $rate['pattern'] . '/', strtoupper($vatId));
    }

    /**
     * Return the ISO 8601 date when EU data was last fetched from EC TEDB.
     *
     * @return string  e.g. "2026-03-18"
     */
    public static function dataVersion(): string
    {
        return self::load()['version'];
    }

    /**
     * Return the flag emoji for a 2-letter ISO 3166-1 alpha-2 country code.
     *
     * Computed from regional indicator symbols — no lookup table needed.
     *
     * @param  string $countryCode  ISO 3166-1 alpha-2 code (e.g. "FI", "DE")
     * @return string  Flag emoji (e.g. "🇫🇮"), or empty string if input is invalid
     */
    public static function getFlag(string $countryCode): string
    {
        $code = strtoupper($countryCode);
        if (strlen($code) !== 2) {
            return "";
        }
        $base = 0x1F1E6;
        return mb_chr($base + ord($code[0]) - ord("A"), "UTF-8")
             . mb_chr($base + ord($code[1]) - ord("A"), "UTF-8");
    }
}
