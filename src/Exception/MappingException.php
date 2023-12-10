<?php

declare(strict_types=1);

namespace Lsp\Mapper\Exception;

use CuyZ\Valinor\Mapper\Tree\Message\NodeMessage;
use CuyZ\Valinor\Mapper\Tree\Node;
use Lsp\Contracts\Mapper\Exception\HydrationExceptionInterface;
use Lsp\Hydrator\Valinor\Internal\ActualTypeFactory;
use Lsp\Hydrator\Valinor\Internal\ExpectedTypeFactory;

class MappingException extends \RuntimeException implements HydrationExceptionInterface
{
    use ErrorPathProvider;
    use ErrorTypeProvider;

    final public const CODE_INVALID_TYPE = 0x01;

    final public const CODE_INVALID_FIELD_TYPE = 0x02;

    final public const CODE_NOT_PASSED = 0x03;

    final public const CODE_FIELD_NOT_PASSED = 0x04;

    protected const CODE_LAST = self::CODE_FIELD_NOT_PASSED;

    /**
     * @param non-empty-string $expected
     * @param non-empty-string|null $actual
     * @param list<non-empty-string> $path
     */
    final public function __construct(
        string $message,
        string $expected,
        ?string $actual = null,
        array $path = [],
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        $this->expected = $expected;
        $this->actual = $actual;
        $this->path = $path;

        parent::__construct($message, $code, $previous);
    }

    /**
     * @throws \ReflectionException
     */
    public static function fromRootNode(Node $node): self
    {
        $expected = ExpectedTypeFactory::createFromNode($node);

        if ($node->sourceFilled()) {
            $actual = ActualTypeFactory::createFromNode($node);

            $message = 'The value should be of type %s, but %s were passed';
            $message = \sprintf($message, $expected, $actual);

            return new self($message, $expected, $actual, [], self::CODE_INVALID_TYPE);
        }

        $message = 'The required value of type %s has not been passed';
        $message = \sprintf($message, $expected);

        return new self($message, $expected, null, [], self::CODE_NOT_PASSED);
    }

    /**
     * @throws \ReflectionException
     */
    public static function fromFieldNode(Node $node): self
    {
        $path = $node->path();
        $chunks = \explode('.', $path);

        $expected = ExpectedTypeFactory::createFromNode($node);

        if ($node->sourceFilled()) {
            $actual = ActualTypeFactory::createFromNode($node);

            $message = 'Field "%s" should be of type %s, but %s were passed';
            $message = \sprintf($message, $path, $expected, $actual);

            return new self($message, $expected, $actual, $chunks, self::CODE_INVALID_FIELD_TYPE);
        }

        $message = 'The required field "%s" of type %s has not been passed';
        $message = \sprintf($message, $path, $expected);

        return new self($message, $expected, null, $chunks, self::CODE_FIELD_NOT_PASSED);
    }

    public static function fromNode(Node $node): self
    {
        return $node->isRoot() ? self::fromRootNode($node) : self::fromFieldNode($node);
    }

    public static function fromNodeMessage(NodeMessage $message): self
    {
        $node = $message->node();

        return $node->isRoot() ? self::fromRootNode($node) : self::fromFieldNode($node);
    }
}
