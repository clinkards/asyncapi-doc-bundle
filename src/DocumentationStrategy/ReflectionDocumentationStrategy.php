<?php

declare(strict_types=1);

namespace Ferror\AsyncapiDocBundle\DocumentationStrategy;

use Ferror\AsyncapiDocBundle\Attribute\Message;
use Ferror\AsyncapiDocBundle\Attribute\Property;
use Ferror\AsyncapiDocBundle\Attribute\PropertyArray;
use Ferror\AsyncapiDocBundle\Schema\PropertyType;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use Symfony\Component\DependencyInjection\ContainerBuilder;

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

        $message = new Message($reflection->getShortName());

        $parameters = new \ReflectionMethod($class, '__construct');

        $properties = $parameters->getParameters();

        $doc = $parameters->getDocComment();

        foreach ($properties as $property) {
            /** @var ReflectionNamedType|null $type */
            $type = $property->getType();
            $name = $property->getName();

            if (null === $type) {
                break;
            }

            if ($convertToSnakeCase) {
                $name = preg_replace('/(?<!^)[A-Z]/', '_$0', $property->getName());
                $name = strtolower($name);
            }

            $parentProperty = new Property(
                name: $name,
                type: PropertyType::fromNative($type->getName()),
                required: !$type->allowsNull(),
            );

            $message->addProperty($parentProperty);

            if ($type->getName() === 'array') {
                $pattern = '/@param\s+array<int,\s*array\{\s*(.*?)\s*\}>\s+\$(\w+)/';

                preg_match_all($pattern, $doc, $matches, PREG_SET_ORDER);

                $params = [];

                foreach ($matches as $match) {
                    $paramName = $match[2];
                    $paramDetails = $match[1];

                    preg_match_all('/(\w+):\s*(\??\w+)/', $paramDetails, $elementMatches, PREG_SET_ORDER);

                    $paramElements = [];
                    foreach ($elementMatches as $elementMatch) {

                        $isNullable = $elementMatch[2][0] === '?';

                        $type = $isNullable ? substr($elementMatch[2], 1) : $elementMatch[2];

                        $paramElements[$elementMatch[1]] = [
                            'type' => $type,
                            'required' => !$isNullable
                        ];
                    }

                    $params[$paramName] = $paramElements;
                }

                foreach ($params[$property->getName()] as $name => $details) {
                    $parentProperty->addChild(new Property(
                        name: $name,
                        type: PropertyType::fromNative($details['type']),
                        required: $details['required'],
                    ));
                }
            }
        }

        return $message;
    }
}
