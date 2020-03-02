<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefrontGraphQl\Plugin;

use Magento\CatalogGraphQl\Model\Config\CategoryAttributeReader as Reader;

/**
 * Delete category specified resolvers from GQL schema for fields, that should be processed by Store Front service
 * Accept list of resolvers in the following format:
 * [
 *    "type|interface" => ["field1", ...]
 * ]
 */
class CategoryAttributeReader
{
    /**
     * List of category resolvers that need to be removed from field in format
     *
     * @var array
     */
    private $resolvers;

    /**
     * @param array $resolvers
     */
    public function __construct(array $resolvers = [])
    {
        $this->resolvers = $resolvers;
    }

    /**
     * Delete category resolver from schema
     *
     * @param Reader $subject
     * @param array $schema
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterRead(Reader $subject, array $schema): array
    {
        foreach ($this->resolvers as $typeName => $fields) {
            if (!isset($schema[$typeName]['fields'])) {
                continue ;
            }
            $schema = $this->removeResolver($schema, $typeName, $fields);
        }

        return $schema;
    }

    /**
     * Remove category resolver from specified $fields in $schema for $typeName
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
