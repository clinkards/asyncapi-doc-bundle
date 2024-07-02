<?php

declare(strict_types=1);

namespace Ferror\AsyncapiDocBundle\Schema\V2;

use Ferror\AsyncapiDocBundle\PropertyTypeTranslator;

class MessageRenderer
{
    private function build(array $document): array
    {
        $properties = [];
        $required = [];

        foreach ($document['properties'] as $property) {
            $properties[$property['name']]['type'] = PropertyTypeTranslator::translate($property['type']);

            if (!empty($property['description'])) {
                $properties[$property['name']]['description'] = $property['description'];
            }

            if (!empty($property['format'])) {
                $properties[$property['name']]['format'] = $property['format'];
            }

            if (!empty($property['example'])) {
                $properties[$property['name']]['example'] = $property['example'];
            }

            if (count($property['children'])) {
                $childDocument = [];
                foreach ($property['children'] as $prop) {
                    $childDocument['properties'][] = $prop->toArray();
                }

                $child = $this->build($childDocument);

                $properties[$property['name']]['items'] = [
                    'type' => 'object',
                    'properties' => $child['properties'],
                    'required' => $child['required']
                ];
            }

            if (isset($property['required']) && $property['required']) {
                $required[] = $property['name'];
            }
        }

        return [
            'properties' => $properties,
            'required' => $required
        ];
    }

    public function render(array $document): array
    {
        $properties = $this->build($document);

        $message[$document['name']] = [
            'payload' => [
                'type' => 'object',
                'properties' => $properties['properties'],
                'required' => $properties['required'],
            ],
        ];

        return $message;
    }
}
