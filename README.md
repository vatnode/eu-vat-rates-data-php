# eu-vat-rates-data · PHP

[![Packagist Version](https://img.shields.io/packagist/v/vatnode/eu-vat-rates-data)](https://packagist.org/packages/vatnode/eu-vat-rates-data)
[![PHP Version](https://img.shields.io/packagist/php-v/vatnode/eu-vat-rates-data)](https://packagist.org/packages/vatnode/eu-vat-rates-data)
[![Last updated](https://img.shields.io/github/last-commit/vatnode/eu-vat-rates-data-php?path=data%2Feu-vat-rates-data.json&label=last%20updated)](https://github.com/vatnode/eu-vat-rates-data-php/commits/main)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)

VAT rates for **44 European countries** — EU-27 plus Norway, Switzerland, UK, and more. EU rates sourced from the European Commission TEDB and checked daily. Non-EU rates maintained manually.

- Standard, reduced, super-reduced, and parking rates
- `eu_member` flag on every country — `true` for EU-27, `false` for non-EU
- `vat_name` — official name of the VAT tax in the country's primary official language
- `vat_abbr` — short abbreviation used locally (e.g. "ALV", "MwSt", "TVA")
- No dependencies — pure PHP 8.1+
- Data bundled in the package — works offline, no network calls
- EU rates checked daily via GitHub Actions, new version published only when rates change

Also available in: [JavaScript/TypeScript (npm)](https://www.npmjs.com/package/eu-vat-rates-data) · [Python (PyPI)](https://pypi.org/project/eu-vat-rates-data/) · [Go](https://pkg.go.dev/github.com/vatnode/eu-vat-rates-data-go) · [Ruby (RubyGems)](https://rubygems.org/gems/eu_vat_rates_data)

---

## Installation

```bash
composer require vatnode/eu-vat-rates-data
```

---

## Usage

```php
use VATNode\EuVatRates\EuVatRates;

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

// Dataset membership check (all 44 countries)
if (EuVatRates::hasRate($userInput)) {
    $rate = EuVatRates::getRate($userInput);
}

// All 44 countries at once
foreach (EuVatRates::getAllRates() as $code => $rate) {
    echo "{$code}: {$rate['standard']}%\n";
}

// When were EU rates last fetched?
echo EuVatRates::dataVersion();  // e.g. "2026-03-27"
```

---

## Data source & update frequency

- EU-27 rates: **European Commission TEDB**, refreshed **daily at 07:00 UTC**
- Non-EU rates: maintained manually, updated on official rate changes
- Published to Packagist only when actual rates change

---

## Covered countries

**EU-27** (daily auto-updates via EC TEDB):

`AT` `BE` `BG` `CY` `CZ` `DE` `DK` `EE` `ES` `FI` `FR` `GR` `HR` `HU` `IE` `IT` `LT` `LU` `LV` `MT` `NL` `PL` `PT` `RO` `SE` `SI` `SK`

**Non-EU Europe** (manually maintained):

`AD` `AL` `BA` `CH` `GB` `GE` `IS` `LI` `MC` `MD` `ME` `MK` `NO` `RS` `TR` `UA` `XK`

---

## Need to validate VAT numbers?

This package provides **VAT rates** only. If you also need to **validate EU VAT numbers** against the official VIES database — confirming a business is VAT-registered — check out [vatnode.dev](https://vatnode.dev), a simple REST API with a free tier.

```bash
curl https://api.vatnode.dev/v1/vat/FI17156132 \
  -H "Authorization: Bearer vat_live_..."
# → { "valid": true, "companyName": "Suomen Pehmeä Ikkuna Oy" }
```

---

## License

MIT

If you find this useful, a ⭐ on GitHub is appreciated.
