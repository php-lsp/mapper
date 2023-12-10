<?php

declare(strict_types=1);

namespace Lsp\Mapper\Exception;

use Lsp\Contracts\Mapper\Exception\MapperExceptionInterface;

class MapperException extends \LogicException implements MapperExceptionInterface
{
    final public const CODE_INTERNAL_ERROR = 0x01;

    protected const CODE_LAST = self::CODE_INTERNAL_ERROR;

    final public function __construct(string $message, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public static function fromInternalException(\Throwable $e): self
    {
        return new static($e->getMessage(), self::CODE_INTERNAL_ERROR, $e);
    }
}
