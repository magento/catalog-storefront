<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefrontGraphQl\Model\Converter;

/**
 * Provides array based on provided object
 *
 * Extracts array_keys from getters and convert camel case to snake case
 * (e.g. Object::getAttributeCode -> [attribute_code => value])
 */
class ObjectToArray
{
    /**
     * Holds object structures
     *
     * @var array
     */
    private $reflectionCache = [];

    /**
     * Extract data from provided object and convert it to a new array
     *
     * Optional map argument allows to map specific methods to array keys
     *
     * @param object $object
     * @param array $map
     * @return array
     */
    public function getArray(object $object, array $map = []): array
    {
        if (!is_object($object)) {
            throw new \InvalidArgumentException('First argument must be an object');
        }
        $map = $this->fetchMap($object, $map);
        $result = [];
        foreach ($map as $entry) {
            $method = $entry['method'];
            $value = $object->$method();
            if (is_object($value)) {
                $value = $this->getArray($value);
            }
            if (is_array($value) && !empty($value) && is_object(reset($value))) {
                $arrayValue = [];
                foreach ($value as $item) {
                    $arrayValue[] = $this->getArray($item, $map);
                }
                $value = $arrayValue;
            }
            $result[$entry['key']] = $value;
        }
        return $result;
    }

    /**
     * Extracts default mapping from the object using reflection
     *
     * Method also applies overrides specified in the map.
     *
     * @param object $object
     * @param array $mapOverride
     * @return array [method => array key]
     */
    private function fetchMap(object $object, array $mapOverride = []): array
    {
        if (isset($this->reflectionCache[get_class($object)])) {
            return $this->reflectionCache[get_class($object)];
        }
        $overrides = [];
        foreach ($mapOverride as $interface => $interfaceMap) {
            if (!in_array($interface, class_implements($object))) {
                continue;
            }
            foreach ($interfaceMap as $method => $key) {
                $overrides[$method] = $key;
            }
        }
        $result = [];
        $dataObjectMethods = get_class_methods(get_class($object));
        foreach ($dataObjectMethods as $method) {
            if (0 !== strpos($method, 'get')) {
                //The method is not a getter
                continue;
            }

            $result[] = [
                'method' => $method,
                'key' => $overrides[$method] ?? preg_replace(
                    '~^get_(.*)$~',
                    '$1',
                    strtolower(preg_replace('~([A-Z])~', '_$1', $method))
                )
            ];
        }
        $this->reflectionCache[get_class($object)] = $result;
        return $result;
    }
}
