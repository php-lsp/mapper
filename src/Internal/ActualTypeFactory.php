<?php

declare(strict_types=1);

namespace Lsp\Mapper\Internal;

use CuyZ\Valinor\Mapper\Tree\Node;
use CuyZ\Valinor\Utility\ValueDumper;

/**
 * @internal This is an internal library class, please do not use it in your code.
 * @psalm-internal Lsp\Mapper
 */
final class ActualTypeFactory
{
    /**
     * @return non-empty-string
     */
    public static function createFromNode(Node $node): string
    {
        if (!$node->sourceFilled()) {
            return '<nothing>';
        }

        $value = $node->sourceValue();

        if (\is_object($value)) {
            $fields = [];

            foreach (\get_object_vars($value) as $field => $value) {
                $string = \is_object($value) ? 'object{...}' : ValueDumper::dump($value);
                $string = \str_replace('array{', 'object{', $string);

                $fields[] = $field . ': ' . $string;
            }

            return 'object{' . \implode(', ', $fields) . '}';
        }

        $result = self::dump($node);

        return \str_replace('array{', 'object{', $result);
    }

    private static function dump(Node $node): string
    {
        return ValueDumper::dump($node->sourceValue());
    }
}
