<?php

declare(strict_types=1);

namespace Ferror\AsyncapiDocBundle;

use Ferror\AsyncapiDocBundle\Tests\UserSignedUp;
use ReflectionClass;
use ReflectionNamedType;

class ReflectionDocumentation
{
    public function document(): array
    {
        $reflection = new ReflectionClass(UserSignedUp::class);
        $properties = $reflection->getProperties();

        $message['name'] = $reflection->getShortName();

        foreach ($properties as $property) {
            /** @var ReflectionNamedType|null $type */
            $type = $property->getType();
            $name = $property->getName();

            $message['properties'][] = [
                'name' => $name,
                'type' => $type?->getName(),
            ];
        }

        return $message;
    }
}