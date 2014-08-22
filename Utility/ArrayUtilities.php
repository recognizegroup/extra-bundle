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

}