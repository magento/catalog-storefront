<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefrontGraphQl\Model;

use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Resolve fields for query.
 */
class FieldResolver
{
    /**
     * @var array
     */
    private $fieldNamesCache = [];

    /**
     * Get fields for schema type.
     *
     * @param ResolveInfo $info
     * @param string[] $schemaTypes
     * @param string|null $requestedField
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getSchemaTypeFields(ResolveInfo $info, array $schemaTypes, string $requestedField = null): array
    {
        $fieldNames = [];
        foreach ($info->fieldNodes as $node) {
            $schemaType = $node->name->value;
            if (!\in_array($schemaType, $schemaTypes, true)) {
                continue;
            }
            if (null === $requestedField && isset($this->fieldNamesCache[$schemaType])) {
                return $this->fieldNamesCache[$schemaType];
            }
            foreach ($node->selectionSet->selections as $selection) {
                if (null !== $requestedField && $selection->name->value !== $requestedField) {
                    continue;
                }
                $schemaTypeField = $schemaType . $requestedField;
                if (null !== $requestedField && isset($this->fieldNamesCache[$schemaTypeField])) {
                    return $this->fieldNamesCache[$schemaTypeField];
                }

                if (isset($selection->selectionSet, $selection->selectionSet->selections)) {
                    if (null !== $requestedField && $selection->name->value === $requestedField) {
                        $fieldNames = $this->getFieldNames($selection, $fieldNames);
                    } elseif ($selection->kind === 'InlineFragment') {
                        $fieldNames = $this->getFieldNames($selection, $fieldNames);
                    } else {
                        $fieldNames[$selection->name->value] = $this->getFieldNames($selection, []);
                    }
                } else {
                    $fieldNames = $this->getFieldNames($selection, $fieldNames);
                }

                $this->fieldNamesCache[$schemaTypeField] = $fieldNames;
            }
            $this->fieldNamesCache[$schemaType] = $fieldNames;
        }

        return $fieldNames;
    }

    /**
     * Get field names
     *
     * @param \GraphQL\Language\AST\SelectionNode $selection
     * @param array $fieldNames
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function getFieldNames(\GraphQL\Language\AST\SelectionNode $selection, array $fieldNames): array
    {
        if (!isset($selection->selectionSet) && !isset($selection->selectionSet->selections)) {
            if ($selection->kind === 'Field' && $selection->name->value) {
                $fieldNames[] = $selection->name->value;
            }
            return $fieldNames;
        }
        foreach ($selection->selectionSet->selections as $itemSelection) {
            if ($itemSelection->kind === 'InlineFragment') {
                foreach ($itemSelection->selectionSet->selections as $inlineSelection) {
                    if ($itemSelection->typeCondition->kind === 'NamedType') {
                        $namedType = $itemSelection->typeCondition->name->value;
                        $fieldNames = $this->getNestedFields($fieldNames, $inlineSelection, $namedType);
                        continue;
                    }

                    if ($inlineSelection->kind === 'InlineFragment') {
                        continue;
                    }

                    $fieldNames = $this->getNestedFields($fieldNames, $inlineSelection);
                }
                continue;
            }
            $fieldNames = $this->getNestedFields($fieldNames, $itemSelection);
        }
        return $fieldNames;
    }

    /**
     * Get nested fields
     *
     * @param array $fieldNames
     * @param mixed $itemSelection
     * @param string|null $nameType
     * @return array
     */
    private function getNestedFields(array $fieldNames, $itemSelection, $nameType = null): array
    {
        $itemSelectionName = $itemSelection->name->value;
        if (isset($itemSelection->selectionSet, $itemSelection->selectionSet->selections)) {
            $itemSelectionName = $nameType
                ? $nameType . '.' . $itemSelection->name->value
                : $itemSelection->name->value;

            $fieldNames[$itemSelectionName] = $this->getFieldNames($itemSelection, []);
        } else {
            $fieldNames[] = $itemSelectionName;
        }
        return $fieldNames;
    }
}
