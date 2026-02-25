# eu-vat-rates-data · PHP

[![Packagist Version](https://img.shields.io/packagist/v/vatnode/eu-vat-rates-data)](https://packagist.org/packages/vatnode/eu-vat-rates-data)
[![PHP Version](https://img.shields.io/packagist/php-v/vatnode/eu-vat-rates-data)](https://packagist.org/packages/vatnode/eu-vat-rates-data)
[![Last updated](https://img.shields.io/github/last-commit/vatnode/eu-vat-rates-data-php?path=data%2Feu-vat-rates.json&label=last%20updated)](https://github.com/vatnode/eu-vat-rates-data-php/commits/main)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)

EU VAT rates for all **27 EU member states** plus the **United Kingdom**, sourced from the [European Commission TEDB](https://taxation-customs.ec.europa.eu/tedb/vatRates.html). Checked daily, published automatically when rates change.

- Standard, reduced, super-reduced, and parking rates
- No dependencies — pure PHP 8.1+
- Data bundled in the package — works offline, no network calls
- Checked daily via GitHub Actions, new version published only when rates change

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
//   'standard'      => 25.5,
//   'reduced'       => [10.0, 13.5],
//   'super_reduced' => null,
//   'parking'       => null,
// ]

// Just the standard rate
EuVatRates::getStandardRate('DE');  // → 19.0

// Type guard
if (EuVatRates::isEuMember($userInput)) {
    $rate = EuVatRates::getRate($userInput);  // always non-null here
}

// All 28 countries at once
foreach (EuVatRates::getAllRates() as $code => $rate) {
    echo "{$code}: {$rate['standard']}%\n";
}

// When were these rates last fetched?
echo EuVatRates::dataVersion();  // e.g. "2026-02-25"
```

---

## Data source & update frequency

Rates are fetched from the **European Commission Taxes in Europe Database (TEDB)**:

- Canonical data repo: **https://github.com/vatnode/eu-vat-rates-data**
- Refreshed: **daily at 08:00 UTC**
- Published to Packagist only when actual rates change

---

## Covered countries

EU-27 member states + United Kingdom (28 countries total):

`AT BE BG CY CZ DE DK EE ES FI FR GB GR HR HU IE IT LT LU LV MT NL PL PT RO SE SI SK`

---

## License

MIT
