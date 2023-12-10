<?php

declare(strict_types=1);

namespace Lsp\Mapper\Exception;

use Lsp\Contracts\Mapper\Exception\MapperExceptionInterface;

class TypeDefinitionException extends MapperException implements MapperExceptionInterface
{
    final public const CODE_PERMISSIVE_TYPE = 0x01 + parent::CODE_LAST;

    protected const CODE_LAST = self::CODE_PERMISSIVE_TYPE + parent::CODE_LAST;

    public static function fromTypeException(\Throwable $e): self
    {
        return new static($e->getMessage(), self::CODE_PERMISSIVE_TYPE, $e);
    }
}
