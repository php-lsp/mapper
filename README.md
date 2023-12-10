# Simple Data Mapper

---

<p align="center">
    <a href="https://packagist.org/packages/php-lsp/mapper"><img src="https://poser.pugx.org/php-lsp/mapper/require/php?style=for-the-badge" alt="PHP 8.1+"></a>
    <a href="https://packagist.org/packages/php-lsp/mapper"><img src="https://poser.pugx.org/php-lsp/mapper/version?style=for-the-badge" alt="Latest Stable Version"></a>
    <a href="https://packagist.org/packages/php-lsp/mapper"><img src="https://poser.pugx.org/php-lsp/mapper/v/unstable?style=for-the-badge" alt="Latest Unstable Version"></a>
    <a href="https://raw.githubusercontent.com/php-lsp/mapper/blob/master/LICENSE"><img src="https://poser.pugx.org/php-lsp/mapper/license?style=for-the-badge" alt="License MIT"></a>
</p>
<p align="center">
    <a href="https://github.com/php-lsp/mapper/actions"><img src="https://github.com/php-lsp/mapper/workflows/tests/badge.svg"></a>
</p>


Simple PHP DTO extractor (normalizer) and hydrator (denormalizer) implementation.

## Requirements

- PHP 8.1+

## Installation

- Add `php-lsp/mapper` as composer dependency.

```json
{
    "require": {
        "php-lsp/mapper": "^1.0"
    }
}
```

## Usage

### Denormalization (Hydrator)

```php
final readonly class Example 
{
    /**
     * @param list<int<0, max>> $example
     */
    public function __construct(
        public array $example,
    ) {}
}

$hydrator = new Lsp\Mapper\Hydrator(
    debug: true, // Optional. Default: false
    cache: null, // Optional. Default: null
);

$result = $hydrator->hydrate(Example::class, ['example' => [1, 2, 3]]);

var_dump($result);
//
// Expected Output:
//
// object(Example) {
//    example: array{ 1, 2, 3 }
// }
//
```

### Normalization (Extractor)

```php
$extractor = new Lsp\Mapper\Extractor(
    normalizers: [],    // Optional. Default: []
    skipNulls: false,   // Optional. Default: false
);

$result = $extractor->extract(new Example([1, 2, 3]));

var_dump($result);
//
// Expected Output:
//
// array{
//    example: array{ 1, 2, 3 }
// }
//
```

#### Extraction Normalizer

```php
class DateTimeNormalizer implements \Lsp\Mapper\Extractor\NormalizerInterface 
{
    public function match(mixed $value): bool
    {
        return $value instanceof \DateTimeInterface;
    }
    
    public function normalize(mixed $value, ExtractorInterface $extractor): string
    {
        return $value->format(\DateTimeInterface::RFC3339);
    }
}

$extractor = new Lsp\Hydrator\Extractor(
    normalizers: [new DateTimeNormalizer()],
);
```
