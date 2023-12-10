<?php

declare(strict_types=1);

namespace Lsp\Mapper\Exception;

use CuyZ\Valinor\Mapper\Tree\Message\NodeMessage;
use CuyZ\Valinor\Mapper\Tree\Node;
use Lsp\Contracts\Mapper\Exception\HydrationExceptionInterface;

/**
 * @template-implements \IteratorAggregate<array-key, HydrationExceptionInterface>
 */
class MappingListException extends \RuntimeException implements
    HydrationExceptionInterface,
    \IteratorAggregate,
    \Countable
{
    private readonly HydrationExceptionInterface $first;

    /**
     * @param non-empty-list<HydrationExceptionInterface> $exceptions
     */
    final public function __construct(
        private readonly array $exceptions,
    ) {
        $this->first = \reset($this->exceptions);

        parent::__construct(
            message: $this->first->getMessage(),
            code: $this->first->getCode(),
            previous: $this->first->getPrevious(),
        );
    }

    public function getPath(): array
    {
        return $this->first->getPath();
    }

    public function getExpectedType(): string
    {
        return $this->first->getExpectedType();
    }

    public function getActualType(): ?string
    {
        return $this->first->getActualType();
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->exceptions);
    }

    /**
     * @return int<1, max>
     */
    public function count(): int
    {
        return \count($this->exceptions);
    }


    /**
     * @param non-empty-list<Node> $nodes
     */
    public static function fromNodes(iterable $nodes): self
    {
        $result = [];

        foreach ($nodes as $node) {
            $result[] = MappingException::fromNode($node);
        }

        return new static($result);
    }

    /**
     * @param non-empty-list<NodeMessage> $messages
     *
     * @return static
     */
    public static function fromNodeMessages(iterable $messages): self
    {
        $result = [];

        foreach ($messages as $message) {
            $result[] = MappingException::fromNodeMessage($message);
        }

        return new static($result);
    }
}
