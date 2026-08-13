# eu-vat-rates-data · PHP

[![Packagist Version](https://img.shields.io/packagist/v/vatnode/eu-vat-rates-data)](https://packagist.org/packages/vatnode/eu-vat-rates-data)
[![PHP Version](https://img.shields.io/packagist/php-v/vatnode/eu-vat-rates-data)](https://packagist.org/packages/vatnode/eu-vat-rates-data)
[![Last updated](https://img.shields.io/github/last-commit/vatnode/eu-vat-rates-data-php?path=data%2Feu-vat-rates-data.json&label=last%20updated)](https://github.com/vatnode/eu-vat-rates-data-php/commits/main)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)

VAT rates for **45 European countries** — EU-27 plus Norway, Switzerland, UK, and more. EU rates sourced from the European Commission TEDB and checked daily. Non-EU rates maintained manually.

- Standard, reduced, super-reduced, and parking rates
- `eu_member` flag on every country — `true` for EU-27, `false` for non-EU
- `vat_name` — official name of the VAT tax in the country's primary official language
- `vat_abbr` — short abbreviation used locally (e.g. "ALV", "MwSt", "TVA")
- **`format` — human-readable VAT number format (e.g. `"ATU + 8 digits"`)** — unique to this package
- **`pattern` — regex for VAT number validation + built-in `validateFormat()` — free, no API key needed** — unique to this package
- No dependencies — pure PHP 8.1+
- Data bundled in the package — works offline, no network calls
- EU rates checked daily via GitHub Actions, new version published only when rates change

Also available in: [JavaScript/TypeScript (npm)](https://www.npmjs.com/package/eu-vat-rates-data) · [Python (PyPI)](https://pypi.org/project/eu-vat-rates-data/) · [Go](https://pkg.go.dev/github.com/vatnode/eu-vat-rates-data-go) · [Ruby (RubyGems)](https://rubygems.org/gems/eu_vat_rates_data)

---

## Need live VIES validation?

This package gives you VAT **rates** and **format checks** for free, offline, in your code. It does **not** call VIES — `validateFormat()` only checks the shape of a VAT number, not whether it actually exists.

For **live VIES validation** — confirming a VAT ID is real, pulling the registered company name and address, and getting the VIES consultation number as your reference for the check — there's **[vatnode](https://vatnode.dev?ref=rates-readme-php)**:

- Live VIES validation, with national-database fallback when VIES is down
- Registered company name, address, registration date
- VIES consultation number for compliance and audit trails
- Webhooks for VAT status changes
- Official [MCP server](https://www.npmjs.com/package/vatnode-mcp) so AI agents (Claude, Cursor, ChatGPT) can validate VAT IDs directly
- Free tier — no credit card needed

```bash
curl https://api.vatnode.dev/v1/vat/IE6388047V \
  -H "Authorization: Bearer YOUR_API_KEY"
```

[**See what the API adds →**](https://vatnode.dev/vat-rates?ref=rates-readme-php#beyond-rates) · [Get a free API key](https://vatnode.dev/login?ref=rates-readme-php)

---

## Installation

```bash
composer require vatnode/eu-vat-rates-data
```

---

## Usage

```php
use vatnode\EuVatRates\EuVatRates;

// Full rate array for a country
$fi = EuVatRates::getRate('FI');
// [
//   'country'       => 'Finland',
//   'currency'      => 'EUR',
//   'eu_member'     => true,
//   'vat_name'      => 'Arvonlisävero',
//   'vat_abbr'      => 'ALV',
//   'standard'      => 25.5,
//   'reduced'       => [10.0, 13.5],
//   'super_reduced' => null,
//   'parking'       => null,
// ]

// Just the standard rate
EuVatRates::getStandardRate('DE');  // → 19.0

// EU membership check — false for non-EU countries (GB, NO, CH, ...)
if (EuVatRates::isEuMember($userInput)) {
    $rate = EuVatRates::getRate($userInput);
}

// Dataset membership check (all 45 countries)
if (EuVatRates::hasRate($userInput)) {
    $rate = EuVatRates::getRate($userInput);
}

// All 45 countries at once
foreach (EuVatRates::getAllRates() as $code => $rate) {
    echo "{$code}: {$rate['standard']}%\n";
}

// When were EU rates last fetched?
echo EuVatRates::dataVersion();  // e.g. "2026-03-27"

// VAT number format validation — no API key, no network call
EuVatRates::validateFormat('ATU12345678');  // → true
EuVatRates::validateFormat('DE123456789');  // → true
EuVatRates::validateFormat('INVALID');      // → false

// Access format metadata directly
$at = EuVatRates::getRate('AT');
echo $at['format'];   // "ATU + 8 digits"
echo $at['pattern'];  // "^ATU\d{8}$"

// Flag emoji from a 2-letter country code — no lookup table, computed from regional indicator symbols
EuVatRates::getFlag('FI');  // → "🇫🇮"
EuVatRates::getFlag('DE');  // → "🇩🇪"
EuVatRates::getFlag('XX');  // → "" (empty string for unknown/invalid codes)
```

---

## Example: charging VAT on an invoice

Rates on their own rarely answer the question you actually have, which is what
to put on the invoice. Two rules cover most of it: charge the buyer's domestic
rate, unless the sale is cross-border B2B inside the EU, where the reverse
charge applies and you invoice 0%.

```php
use vatnode\EuVatRates\EuVatRates;

/** Money in minor units (cents). Never floats. */
function invoiceTotal(
    int $netCents,
    string $sellerCountry,
    string $buyerCountry,
    ?string $buyerVatId = null
): array {
    $isCrossBorderB2B = $buyerCountry !== $sellerCountry
        && $buyerVatId !== null
        && EuVatRates::validateFormat($buyerVatId);

    if ($isCrossBorderB2B) {
        return ['vat_cents' => 0, 'total_cents' => $netCents, 'reverse_charge' => true];
    }

    $rate = EuVatRates::getStandardRate($buyerCountry);
    $vatCents = (int) round($netCents * $rate / 100);

    return [
        'vat_cents' => $vatCents,
        'total_cents' => $netCents + $vatCents,
        'reverse_charge' => false,
    ];
}

// Domestic sale in Finland — 25.5%
invoiceTotal(10000, 'FI', 'FI');
// → ['vat_cents' => 2550, 'total_cents' => 12550, 'reverse_charge' => false]

// Finnish seller, German business buyer — reverse charge
invoiceTotal(10000, 'FI', 'DE', 'DE123456789');
// → ['vat_cents' => 0, 'total_cents' => 10000, 'reverse_charge' => true]
```

`validateFormat()` only checks the shape of the number. Applying the reverse charge
requires the buyer to actually be VAT-registered, which is a VIES lookup — see
above.

---

## Data source & update frequency

How the daily check works, and what changed when: [vatnode.dev/data](https://vatnode.dev/data?ref=rates-readme-php).

- EU-27 rates: **European Commission TEDB**, checked against the source **daily at 07:00 UTC**, updated on any change
- Non-EU rates: maintained manually, updated on official rate changes
- Published to Packagist only when actual rates change

---


## Keeping rates current

Rates are bundled at install time. A new package version is published automatically whenever rates change — but your installed version will not update itself.

**Recommended:** add [Renovate](https://renovatebot.com) or [Dependabot](https://docs.github.com/en/code-security/dependabot) to your repo. They detect new versions and open a PR automatically whenever rates change — no manual update commands needed.

**Need real-time accuracy?** Fetch the always-current JSON directly:

```
https://cdn.jsdelivr.net/gh/vatnode/eu-vat-rates-data@main/data/eu-vat-rates-data.json
```

No package needed — parse it with a single `fetch()` / `http.get()` / `file_get_contents()` call and cache locally.

---

## Covered countries

**EU-27** (checked daily against EC TEDB, updated on any change):

`AT` `BE` `BG` `CY` `CZ` `DE` `DK` `EE` `ES` `FI` `FR` `GR` `HR` `HU` `IE` `IT` `LT` `LU` `LV` `MT` `NL` `PL` `PT` `RO` `SE` `SI` `SK`

**Non-EU Europe** (manually maintained):

`AD` `AL` `BA` `CH` `GB` `GE` `IS` `LI` `MC` `MD` `ME` `MK` `NO` `RS` `TR` `UA` `XK`

---

## Changelog

See [CHANGELOG.md](CHANGELOG.md).

---

## License

MIT

If you find this useful, a ⭐ on GitHub is appreciated.
