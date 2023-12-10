<?php

declare(strict_types=1);

namespace Lsp\Mapper;

use CuyZ\Valinor\Cache\FileWatchingCache;
use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Object\Exception\PermissiveTypeNotAllowed;
use CuyZ\Valinor\Mapper\Tree\Message\Messages;
use CuyZ\Valinor\Mapper\Tree\Message\NodeMessage;
use CuyZ\Valinor\Mapper\TreeMapper;
use CuyZ\Valinor\MapperBuilder;
use Lsp\Contracts\Mapper\HydratorInterface;
use Lsp\Mapper\Exception\HydratorException;
use Lsp\Mapper\Exception\MappingException;
use Lsp\Mapper\Exception\MappingListException;
use Lsp\Mapper\Exception\TypeDefinitionException;
use Psr\SimpleCache\CacheInterface;

final class Hydrator implements HydratorInterface
{
    private readonly MapperBuilder $builder;
    private readonly TreeMapper $mapper;

    public function __construct(
        bool $debug = false,
        ?CacheInterface $cache = null,
    ) {
        $this->builder = $this->createBuilder($debug, $cache);
        $this->mapper = $this->builder->mapper();
    }

    private function createBuilder(bool $debug, ?CacheInterface $cache): MapperBuilder
    {
        $builder = new MapperBuilder();

        if ($cache !== null) {
            $builder = $builder->withCache($this->getCache($debug, $cache));
        }

        return $builder;
    }

    private function getCache(bool $debug, CacheInterface $cache): ?CacheInterface
    {
        if (!$debug) {
            return $cache;
        }

        return new FileWatchingCache($cache);
    }

    /**
     * @param class-string $dto
     * @param class-string ...$other
     */
    public function warmup(string $dto, string ...$other): void
    {
        try {
            $this->builder->warmup($dto, ...$other);
        } catch (PermissiveTypeNotAllowed $e) {
            throw TypeDefinitionException::fromTypeException($e);
        } catch (\Throwable $e) {
            throw HydratorException::fromInternalException($e);
        }
    }

    public function hydrate(string $type, mixed $data): mixed
    {
        try {
            return $this->mapper->map($type, $data);
        } catch (MappingError $e) {
            /** @var list<NodeMessage> $messages */
            $messages = Messages::flattenFromNode($e->node())
                ->errors()
                ->toArray();

            throw match (\count($messages)) {
                0 => HydratorException::fromInternalException($e),
                1 => MappingException::fromNodeMessage($messages[0]),
                default => MappingListException::fromNodeMessages($messages),
            };
        } catch (PermissiveTypeNotAllowed $e) {
            throw TypeDefinitionException::fromTypeException($e);
        } catch (\Throwable $e) {
            throw HydratorException::fromInternalException($e);
        }
    }
}
