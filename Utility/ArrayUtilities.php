<?php

namespace Recognize\ExtraBundle\Utility;

use Symfony\Component\Serializer\Encoder\JsonEncoder,
	Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;

/**
 * Class ArrayUtilities
 * @package Recognize\ExtraBundle\Utility
 * @author Nick Obermeijer <n.obermeijer@recognize.nl>
 */
class ArrayUtilities {

	/**
	 * @param array $data
	 * @param object|string $class
	 * @throws \Exception
	 * @return object
	 */
	public static function getAsObject(array $data, $class) {
		if($jsonData = json_encode($data)) {
			$normalizer = new GetSetMethodNormalizer();
			$normalizer->setCamelizedAttributes(array_keys($data));
			$serializer = new Serializer(array($normalizer), array(new JsonEncoder()));
			return $serializer->deserialize($jsonData, ((is_object($class)) ? get_class($class) : $class), 'json');
		}
		throw new \Exception('Failed to convert array to json');
	}

	/**
	 * @param array $array
	 * @param string $column
	 * @param mixed $value
	 * @return array|null
	 */
	public static function findAllByColumnValue(array $array, $column, $value) {
		$results = array();
		foreach($array as $element) {
			if(is_array($element) && array_key_exists($column, $element) && $element[$column] == $value) {
				$results[] = $element;
			}
		}
		return (!empty($results)) ? $results : null;
	}

	/**
	 * @param array $array
	 * @param string $column
	 * @param string $value
	 * @throws \Exception
	 * @return mixed
	 */
	public static function findByColumnValue(array $array, $column, $value) {
		if($results = self::findAllByColumnValue($array, $column, $value)) {
			if(sizeof($results) > 1) throw new \Exception('Array contains more than one result matching criteria');
			return array_shift($results);
		}
		return null;
	}

	/**
	 * @param array $array
	 * @param string $column
	 * @param array $values
	 * @param bool $nested
	 * @return array
	 */
	public static function getColumnValues(array $array, $column, array &$values = array(), $nested = true) {
		foreach($array as $key => $item) {
			if($key == $column && (!is_array($item) || is_array($item) && !$nested)) {
				$values[] = $item;
			} elseif($nested && is_array($item)) {
				self::getColumnValues($item, $column, $values, $nested);
			}
		}
		return $values;
	}

	/**
	 * @param array $haystack
	 * @param array $keys
	 * @param bool $deep
	 */
	public static function unsetColumnsByKeys(array &$haystack, array $keys, $deep = false) {
		foreach($haystack as &$item) {
			if($deep && is_array($item)) {
				self::unsetColumnsByKeys($item, $keys, $deep);
			} else {
				foreach($keys as $key) {
					if(array_key_exists($key, $haystack)) {
						unset($haystack[$key]);
					}
				}
			}
		}
	}
	
	/**
	 * @param array $haystack
	 * @param array $key
	 * @param function $func
	 * @param bool $deep
	 * 
	 * @example
	 * In this example we search for all fields with the name 'image_paths' and explode the value (we know it is a comma delimited string) into an array.
	 * 
	 * ArrayUtilities::funcColumnByKey($data, 'image_paths', function($value) {
	 * 		return explode(',', $value);
	 * }, true);
	 */
	public static function funcColumnByKey(array &$haystack, $column, $func, $nested = true) {
		foreach($haystack as $key => &$item) {
			if($key == $column && (!is_array($item) || is_array($item) && !$nested)) {
				if (is_callable($func)) $haystack[$key] = call_user_func($func, $item);
			} elseif($nested && is_array($item)) {
				self::funcColumnByKey($item, $column, $func, $nested);
			}
		}
	}

}