<?php

declare(strict_types=1);

namespace Ferror\AsyncapiDocBundle\DocumentationStrategy;

use Ferror\AsyncapiDocBundle\Attribute\Message;
use Ferror\AsyncapiDocBundle\Attribute\Property;
use Ferror\AsyncapiDocBundle\Schema\PropertyType;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;

final readonly class ReflectionDocumentationStrategy implements PrioritisedDocumentationStrategyInterface
{
    public static function getDefaultPriority(): int
    {
        return 20;
    }

    /**
     * @param class-string $class
     *
     * @throws ReflectionException
     * @throws DocumentationStrategyException
     */
    public function document(string $class, bool $convertToSnakeCase = true): Message
    {
        $reflection = new ReflectionClass($class);
        /** @var ReflectionAttribute<Message>[] $messageAttributes */
        $messageAttributes = $reflection->getAttributes(Message::class);

        if (empty($messageAttributes)) {
            throw new DocumentationStrategyException('Error: class ' . $class . ' must have at least ' . Message::class . ' attribute.');
        }

        $message = $messageAttributes[0]->newInstance();

        $parameters = new \ReflectionMethod($class, '__construct');

        $properties = $parameters->getParameters();

        foreach ($properties as $property) {
            /** @var ReflectionNamedType|null $type */
            $type = $property->getType();
            $name = $property->getName();

            if ($convertToSnakeCase) {
                $name = preg_replace('/(?<!^)[A-Z]/', '_$0', $property->getName());
                $name = strtolower($name);
            }

            if (null === $type) {
                break;
            }

            $message->addProperty(
                new Property(
                    name: $name,
                    type: PropertyType::fromNative($type->getName()),
                    required: !$type->allowsNull(),
                )
            );
        }

        return $message;
    }
}
