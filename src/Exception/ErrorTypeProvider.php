<?php

declare(strict_types=1);

namespace Lsp\Mapper\Exception;

use Lsp\Contracts\Mapper\Exception\ErrorTypeProviderInterface;

/**
 * @pslam-require-implements ErrorTypeProviderInterface
 * @mixin ErrorTypeProviderInterface
 */
trait ErrorTypeProvider
{
    /**
     * @var non-empty-string
     */
    private readonly string $expected;

    /**
     * @var non-empty-string|null
     */
    private readonly ?string $actual;

    public function getExpectedType(): string
    {
        return $this->expected ?? 'mixed';
    }

    public function getActualType(): ?string
    {
        return $this->actual ?? null;
    }
}
