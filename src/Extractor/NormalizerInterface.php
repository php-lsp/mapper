<?php

declare(strict_types=1);

namespace Lsp\Mapper\Extractor;

use Lsp\Contracts\Mapper\ExtractorInterface;

/**
 * @template TInValue of mixed
 * @template TOutValue of mixed
 */
interface NormalizerInterface
{
    /**
     * @return ($value is TInValue ? true : false)
     */
    public function match(mixed $value): bool;

    /**
     * @param TInValue $value
     * @param-out list<non-empty-string> $path
     *
     * @return TOutValue
     */
    public function normalize(mixed $value, ExtractorInterface $extractor, array &$path = []): mixed;
}
