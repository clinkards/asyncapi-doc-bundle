<?php

declare(strict_types=1);

namespace Ferror\AsyncapiDocBundle\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class PropertyObject extends AbstractProperty implements PropertyInterface
{
    public function __construct(
        string $name,
        public readonly string $class,
        string $description = '',
        public readonly array $items = [],
        public readonly bool $required = true,
    ) {
        parent::__construct($name, $description);
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'type' => 'object',
            'description' => $this->description,
            'items' => array_map(static fn(PropertyInterface $property) => $property->toArray(), $this->items),
        ];
    }
}
