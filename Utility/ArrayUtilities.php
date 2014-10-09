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
	 * @param bool $deep
	 * @param bool $unique
	 * @return array
	 */
	public static function getColumnValues(array $array, $column, $deep = true, $unique = false, array &$values = array()) {
		foreach($array as $key => $item) {
			if($key == $column && (!is_array($item) || is_array($item) && !$deep)) {
				if($unique && !in_array($item, $values)) {
					$values[] = $item;
				} elseif(!$unique) $values[] = $item;
			} elseif($deep && is_array($item)) {
				self::getColumnValues($item, $column, $deep, $unique, $values);
			}
		}
		return $values;
	}

	/**
	 * @param array $array
	 * @param array $columns
	 * @param bool $deep
	 * @return array
	 */
	public static function getColumnsValues(array $array, array $columns, $deep = true) {
		$results = array();
		if($deep) { // When deep is set, dive deeper and unset the key from it's collection
			foreach($array as $key => $item) {
				if(is_array($item)) {
					array_merge_recursive($results, self::getColumnsValues($item, $columns, $deep));
					unset($array[$key]); // Unset after recursive search
				}
			}
		}
		return array_merge_recursive($results, array_intersect_key($array, array_flip($columns)));
	}

	/**
	 * @param array $haystack
	 * @param string|int $key
	 */
	public static function unsetByKey(array &$haystack, $key) {
		if(array_key_exists($key, $haystack)) { // Make sure key exists
			unset($haystack[$key]);
		}
	}

	/**
	 * @param array $haystack
	 * @param mixed $value
	 */
	public static function unsetByValue(array &$haystack, $value) {
		if($index = array_search($value, $haystack)) {
			unset($haystack[$index]);
		}
	}

	/**
	 * @param array $haystack
	 * @param array|string|int $keys
	 * @param bool $deep
	 */
	public static function unsetColumnsByKeys(array &$haystack, $keys, $deep = false) {
		foreach($haystack as &$item) {
			if($deep && is_array($item)) {
				self::unsetColumnsByKeys($item, $keys, $deep);
			} else {
				if(is_array($keys)) { // when array loop over values to unset
					foreach($keys as $key) {
						self::unsetByKey($haystack, $key);
					}
				} else self::unsetByKey($haystack, $keys);
			}
		}
	}

	/**
	 * @param array $haystack
	 * @param array|mixed $values
	 * @param bool $deep
	 */
	public static function unsetColumnsByValue(array &$haystack, $values, $deep = false) {
		foreach($haystack as &$item) {
			if($deep && is_array($item)) {
				self::unsetColumnsByValue($item, $values, $deep);
			} else {
				if(is_array($values)) {
					foreach($values as $value) {
						self::unsetByValue($haystack, $value);
					}
				} else self::unsetByValue($haystack, $values);
			}
		}
	}

	/**
	 * @param array $haystack
	 * @param array $replace
	 * @param bool $deep
	 */
	public static function replaceValues(array &$haystack, array $replace, $deep = false) {
		foreach($haystack as &$item) {
			if($deep && is_array($item)) {
				self::replaceValues($item, $replace, $deep);
			} else {
				foreach($replace as $key => $replacement) {
					if($index = array_search($key, $haystack)) {
						$haystack[$index] = $replacement;
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
			if($key === $column && (!is_array($item) || is_array($item) && !$nested)) {
				if (is_callable($func)) $haystack[$key] = call_user_func($func, $item);
			} elseif($nested && is_array($item)) {
				self::funcColumnByKey($item, $column, $func, $nested);
			}
		}
	}

}