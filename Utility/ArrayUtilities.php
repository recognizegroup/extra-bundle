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
	 * @param array $columns
	 * @param bool $deep
	 * @return bool
	 */
	public static function hasMatchingKeyColumnValues(array $array, array $columns, $deep = false) {
		$match = false;
		foreach($array as $element) { // Loops current array's elements
			if($deep && is_array($element) && !$match && self::hasMatchingKeyColumnValues($element, $columns, $deep)) { // Go deep!
				$match = true;
			} elseif(!$match) {
				$matches = array();
				foreach($columns as $column) { // Loop over columns to match
					if(array_key_exists($column, $array) && (empty($matches) || in_array($array[$column], $matches))) {
						$matches[] = $array[$column]; // Add value to matches
					}
				}
				$match = (sizeof($matches) == sizeof($columns)); // Did we find all values?
			}
			if($match) break; // Stop loop when we've found a match
		}
		return $match;
	}

	/**
	 * @param array $array
	 * @param string $column
	 * @param string $value
	 * @param bool $deep
	 * @return mixed
	 */
	public static function findOneByColumnValue(array $array, $column, $value, $deep = false) {
		return self::findByColumnValue($array, $column, $value, false, $deep);
	}

	/**
	 * @param array $array
	 * @param string $column
	 * @param array|string $value
	 * @return mixed
	 */
	public static function findAllByColumnValue(array $array, $column, $value) {
		return self::findByColumnValue($array, $column, $value, true);
	}

	/**
	 * @param array $array
	 * @param array $columns
	 * @param $value
	 * @return array
	 */
	public static function findAllByColumnsValue(array $array, array $columns, $value) {
		$results = array();
		foreach($columns as $column) {
			if($result = self::findOneByColumnValue($array, $column, $value)) {
				$results[] = $result;
			}
		}
		return (!empty($results)) ? $results : null;
	}

	/**
	 * @param array $array
	 * @param string $column
	 * @param array|string $value
	 * @param bool $multiple
	 * @param bool $deep
	 * @return mixed
	 */
	private static function findByColumnValue(array $array, $column, $value, $multiple = false, $deep = false) {
		$results = array();
		foreach($array as $key => $element) {
			if($key !== $column && $deep && is_array($element)) { // Go deeper
				if($result = self::findByColumnValue($element, $column, $value, $multiple, $deep)) {
					$results[] = $result;
				}
			} elseif($key === $column && $element === $value) {
				$results[] = $element;
			}
		}

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
	 * @param bool $deep
	 * @param bool $unique
	 * @return array
	 */
	public static function getColumnValues(array $array, $column, $deep = true, $unique = false) {
		$values = array();
		foreach($array as $key => $item) {
			if($key == $column && (!is_array($item) || (is_array($item) && !$deep))) {
				$values[] = $item;
			} elseif($deep && is_array($item)) {
				$values = array_merge($values, self::getColumnValues($item, $column, $deep, $unique));
			}
		}
		return (($unique) ? array_unique($values) : $values);
	}

	/**
	 * @param array $array
	 * @param mixed $column
	 * @param bool $deep
	 * @return mixed|null
	 */
	public function getFirstColumnValue(array $array, $column, $deep = true) {
		$values = self::getColumnValues($array, $column, $deep);
		return (!empty($values)) ? array_shift($values) : null;
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
	 * @param array $array
	 * @param string $column
	 * @param null $default
	 * @return null
	 */
	public static function getColumnValue(array $array, $column, $default = null) {
		return ((array_key_exists($column, $array)) ? $array[$column] : $default);
	}

	/**
	 * @param array $array
	 * @param string $column
	 * @param mixed $value
	 * @param bool $deep
	 */
	public static function filterByColumnValue(array &$array, $column, $value, $deep = true) {
		foreach($array as $key => $item) { // loop items
			if($key == $column && $array[$key] == $value) unset($array[$key]); // Remove
			elseif($deep && is_array($item)) { // Head deeper
				self::filterByColumnValue($item, $column, $value);
			}
		}
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
	 * @param array $array
	 * @param string|int $oldKey
	 * @param string|int $newKey
	 */
	public static function renameKey(array &$array, $oldKey, $newKey) {
		if (array_key_exists($oldKey, $array)) {
			$array[$newKey] = $array[$oldKey];
			unset($array[$oldKey]);
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
	 * @param array $values
	 * @param bool $deep
	 */
	public static function removeValues(array &$haystack, array $values, $deep = false) {
		foreach($haystack as &$item) {
			if($deep && is_array($item)) {
				self::removeValues($item, $values, $deep);
			} else {
				foreach($values as $value) {
					unset($haystack[$value]);
				}
			}
		}
	}


	/**
	 * @param array $haystack
	 * @param $column
	 * @param callback $func
	 * @param bool $deep
	 * @example
	 * In this example we search for all fields with the name 'image_paths' and explode the value (we know it is a comma delimited string) into an array.
	 *
	 * ArrayUtilities::funcColumnByKey($data, 'image_paths', function($value) {
	 *        return explode(',', $value);
	 * }, true);
	 */
	public static function funcColumnByKey(array &$haystack, $column, $func, $deep = false) {
		foreach($haystack as $key => &$item) {
			if($key === $column && (!is_array($item) || is_array($item) && !$deep)) {
				if (is_callable($func)) $haystack[$key] = call_user_func($func, $item);
			} elseif($deep && is_array($item)) {
				self::funcColumnByKey($item, $column, $func, $deep);
			}
		}
	}

	/**
	 * Search for array that contains an column with given value and calls callback when found.
	 * @param array $haystack
	 * @param int|string $column
	 * @param mixed $value
	 * @param callable $callback
	 * @param bool $deep
	 * @param null|int $lastKey
	 * @throws \Exception
	 */
	public static function funcArrayByKeyValue(array &$haystack, $column, $value, $callback, $deep = false, $lastKey = null) {
		if(!is_callable($callback)) throw new \Exception('Expected $callback to be callable');
		foreach($haystack as $key => $item) {
			if($deep && is_array($item)) { // Go deep first
				self::funcArrayByKeyValue($item, $column, $value, $callback, $deep, $key);
			} elseif($key == $column && $value == $item) {
				call_user_func_array($callback, array($haystack, $lastKey));
			}
		}
	}

    /**
     * @param array $array
     * @param string $column
     * @param array|string $value
     * @param bool $multiple
     * @param bool $deep
     * @return mixed
     */
    public static function findParentByColumnValue(array $array, $column, $value, $multiple = false, $deep = false) {
        $results = array();
        foreach($array as $key => $element) {
            if(!array_key_exists($column, $element) && $deep && is_array($element)) { // Go deeper
                $results[] = self::findParentByColumnValue($element, $column, $value, $multiple, $deep);
            } elseif(array_key_exists($column, $element)) {
               	if((is_array($value) && in_array($element[$column], $value)) || $element[$column] == $value) {
					$results[] = $element;
				}
            }
        }

        return (!empty($results)) ? (($multiple) ? $results : array_shift($results)) : null;
    }

	// ---- Bart's simpler utilities ----

	/**
	 * @param array $array
	 * @param string $prop
	 * @param mixed $val
	 * @return mixed || null
	 */
	public static function findItemByValue(array $array, $prop, $val) {
		$results = self::findItemsByValue($array, $prop, $val);
		return (!empty($results)) ? array_shift($results) : null;
	}

	/**
	 * @param array $array
	 * @param string $prop
	 * @param mixed $val
	 * @return array
	 */
	public static function findItemsByValue(array $array, $prop, $val) {
		$results = array_filter($array, function($value) use (&$prop, &$val) {
			if (!is_array($value) && is_object($value)) $value = (array)$value;
			return (array_key_exists($prop, $value) && $value[$prop] == $val);
		});
		return array_values($results);
	}
}