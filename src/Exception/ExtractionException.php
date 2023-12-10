<?php

declare(strict_types=1);

namespace Lsp\Mapper\Exception;

use Lsp\Contracts\Mapper\Exception\ExtractionExceptionInterface;

final class ExtractionException extends \RuntimeException implements ExtractionExceptionInterface
{
    use ErrorPathProvider;

    final public const CODE_OBJECT_RECURSION = 0x01;

    final public const CODE_INTERNAL_NORMALIZER = 0x02;

    protected const CODE_LAST = self::CODE_INTERNAL_NORMALIZER;

    /**
     * @param list<non-empty-string> $path
     */
    public function __construct(
        string $message,
        array $path = [],
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        $this->path = $path;

        parent::__construct($message, $code, $previous);
    }

    /**
     * @param list<non-empty-string> $path
     */
    public static function fromObjectRecursion(object $reference, array $path): self
    {
        $message = \sprintf('An object %s refers to itself', $reference::class);

        return new self($message, $path, self::CODE_OBJECT_RECURSION);
    }

    public static function fromNormalizerException(\Throwable $e, array $path): self
    {
        return new self($e->getMessage(), $path, self::CODE_INTERNAL_NORMALIZER, $e);
    }
}
