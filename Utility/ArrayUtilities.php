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
	 * @param string $value
	 * @return mixed
	 */
	public static function findOneByColumnValue(array $array, $column, $value) {
		return self::findByColumnValue($array, $column, $value);
	}

	/**
	 * @param array $array
	 * @param $column
	 * @param $value
	 * @return mixed
	 */
	public static function findAllByColumnValue(array $array, $column, $value) {
		return self::findByColumnValue($array, $column, $value, true);
	}


	/**
	 * @param array $array
	 * @param string $column
	 * @param string $value
	 * @param bool $multiple
	 * @throws \Exception
	 * @return mixed
	 */
	private static function findByColumnValue(array $array, $column, $value, $multiple = false) {
		$results = array();
		foreach($array as $element) {
			if(is_array($element) && array_key_exists($column, $element) && $element[$column] == $value) {
				$results[] = $element;
			}
		}
		if(!$multiple && sizeof($results) > 1) throw new \Exception('Array contains more than one result matching criteria');
		return (!empty($results)) ? (($multiple) ? $results : array_shift($results)) : null;
	}

	/**
	 * @param array $array
	 * @param array $values
	 * @param bool $deep
	 * @return array
	 */
	public static function findAllByColumnsValues(array $array, array $values, $deep = false) {
		return self::findByColumnsValues($array, $values, $deep, true);
	}

	/**
	 * @param array $array
	 * @param array $values
	 * @param bool $deep
	 * @return array
	 */
	public static function findOneByColumnsValues(array $array, array $values, $deep = false) {
		return self::findByColumnsValues($array, $values, $deep);
	}

	/**
	 * @param array $array
	 * @param array $values
	 * @param bool $deep
	 * @param bool $multiple
	 * @throws \Exception
	 * @return array
	 */
	private static function findByColumnsValues(array $array, array $values, $deep = false, $multiple = false) {
		$results = array();
		if($deep) { // When deep
			foreach($array as $value) {
				if(is_array($value)) { // Head deeper when it's an array
					if($result = self::findByColumnsValues($value, $values, $deep)) {
						$results[] = $result;
					}
				}
			}
		}

		$intersected = @array_intersect($array, $values);
		if(sizeof(array_diff($values, $intersected)) == 0) {
			$results[] = $array;
		}

		if(!$multiple && sizeof($results) > 1) throw new \Exception('Array contains more than one result matching criteria');
		return (!empty($results)) ? (($multiple) ? $results : array_shift($results)) : null;
	}

	/**
	 * @param array $array
	 * @param string $column
	 * @param array $values
	 * @param bool $nested
	 * @param bool $unique
	 * @return array
	 */
	public static function getColumnValues(array $array, $column, $nested = true, $unique = false, array &$values = array()) {
		foreach($array as $key => $item) {
			if($key == $column && (!is_array($item) || is_array($item) && !$nested)) {
				if($unique && !in_array($item, $values)) {
					$values[] = $item;
				} elseif(!$unique) $values[] = $item;
			} elseif($nested && is_array($item)) {
				self::getColumnValues($item, $column, $nested, $unique, $values);
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
	 * @param $column
	 * @param callback $func
	 * @param bool $nested
	 * @example
	 * In this example we search for all fields with the name 'image_paths' and explode the value (we know it is a comma delimited string) into an array.
	 *
	 * ArrayUtilities::funcColumnByKey($data, 'image_paths', function($value) {
	 *        return explode(',', $value);
	 * }, true);
	 */
	public static function funcColumnByKey(array &$haystack, $column, $func, $nested = false) {
		foreach($haystack as $key => &$item) {
			if($key == $column && (!is_array($item) || is_array($item) && !$nested)) {
				if (is_callable($func)) $haystack[$key] = call_user_func($func, $item);
			} elseif($nested && is_array($item)) {
				self::funcColumnByKey($item, $column, $func, $nested);
			}
		}
	}

}