<?php

declare(strict_types=1);

namespace Ferror\AsyncapiDocBundle;

/**
 * PHP Type to AsyncAPI Type
 */
enum PropertyType: string
{
    case STRING = 'string';
    case BOOLEAN = 'boolean';
    case DATETIME = 'date-time';
    case INTEGER = 'integer';
    case FLOAT = 'number';
}
