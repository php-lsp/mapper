<?php

declare(strict_types=1);

namespace Lsp\Mapper\Internal;

use CuyZ\Valinor\Mapper\Tree\Node;

/**
 * @internal This is an internal library class, please do not use it in your code.
 * @psalm-internal Lsp\Mapper
 */
final class ExpectedTypeFactory
{
    /**
     * @return non-empty-string
     * @throws \ReflectionException
     */
    public static function createFromNode(Node $node): string
    {
        $type = $node->type();

        if (\class_exists($type)) {
            return self::createFromObject($type);
        }

        return $type;
    }

    /**
     * @param class-string $class
     *
     * @return non-empty-string
     * @throws \ReflectionException
     */
    private static function createFromObject(string $class): string
    {
        $fields = [];

        $reflection = new \ReflectionClass($class);

        foreach ($reflection->getProperties(\ReflectionProperty::IS_PUBLIC) as $prop) {
            $fields[] = $prop->getName();
        }

        return 'object{' . \implode(', ', $fields) . '}';
    }
}
