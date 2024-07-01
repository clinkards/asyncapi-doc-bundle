<?php

declare(strict_types=1);

namespace Ferror\AsyncapiDocBundle\Schema;

/**
 * AsyncAPI Types
 */
enum PropertyType: string
{
    case STRING = 'string';
    case BOOLEAN = 'boolean';
    case INTEGER = 'integer';
    case FLOAT = 'number';
    case ARRAY = 'array';

    public static function fromNative(string $type): self
    {
        return match ($type) {
            'bool', 'boolean' => self::BOOLEAN,
            'int', 'integer' => self::INTEGER,
            'float', 'number' => self::FLOAT,
            'array' => self::ARRAY,
            default => self::STRING,
        };
    }
}
