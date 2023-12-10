<?php

declare(strict_types=1);

namespace Lsp\Mapper\Exception;

use Lsp\Contracts\Mapper\Exception\ErrorPathProviderInterface;

/**
 * @pslam-require-implements ErrorPathProviderInterface
 * @mixin ErrorPathProviderInterface
 */
trait ErrorPathProvider
{
    /**
     * @var list<non-empty-string>
     */
    private readonly array $path;

    public function getPath(): array
    {
        return $this->path ?? [];
    }
}
