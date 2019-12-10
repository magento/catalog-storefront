<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\StoreFrontGraphQl\Plugin;

use Magento\Framework\GraphQlSchemaStitching\GraphQlReader;

/**
 * Delete resolvers from GQL schema for fields, that should be processed by Store Front service
 * Accept list of resolvers in the following format:
 * [
 *    "type|interface" => ["field1", ...]
 * ]
 */
class SchemaReader
{
    /**
     * GraphQL schema type
     */
    private const FIELD_TYPE = 'graphql_type';

    /**
     * GraphQL schema interface
     */
    private const FIELD_INTERFACE = 'graphql_interface';

    /**
     * List of resolvers that need to be removed from field in format
     *
     * @var array
     */
    private $resolvers;

    /**
     * @param array $resolvers
     */
    public function __construct(array $resolvers)
    {
        $this->resolvers = $resolvers;
    }

    /**
     * Delete resolver from schema
     *
     * @param GraphQlReader $subject
     * @param array $schema
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterRead(GraphQlReader $subject, array $schema)
    {
        foreach ($this->resolvers as $typeName => $fields) {
            if (!isset($schema[$typeName]['fields'])) {
                continue ;
            }
            $schema = $this->removeResolver($schema, $typeName, $fields);
            $schema = $this->handleInterfaceImplementation($schema, $typeName, $fields);
        }

        return $schema;
    }

    /**
     * Find interface implementation and remove resolver as well
     *
     * @param array $schema
     * @param string $interface
     * @param array $fields
     * @return array
     */
    private function handleInterfaceImplementation(array $schema, string $interface, array $fields): array
    {
        if ($schema[$interface]['type'] !== self::FIELD_INTERFACE) {
            return $schema;
        }
        foreach ($schema as $typeName => $type) {
            if ($type['type'] === self::FIELD_TYPE && isset($type['implements'])) {
                $interfaces = \array_keys($type['implements']);
                if (\in_array($interface, $interfaces, true)) {
                    $schema = $this->removeResolver($schema, $typeName, $fields);
                }
            }
        }

        return $schema;
    }

    /**
     * Remove resolver from specified $fields in $schema for $typeName
     *
     * @param array $schema
     * @param string $typeName
     * @param array $fields
     * @return array
     */
    private function removeResolver(array $schema, string $typeName, array $fields): array
    {
        foreach ($fields as $field) {
            if (isset($schema[$typeName]['fields'][$field]['resolver'])) {
                unset($schema[$typeName]['fields'][$field]['resolver']);
            }
        }

        return $schema;
    }
}
