<?php

namespace Recognize\ExtraBundle\Utility;

/**
 * Class ArrayUtilities
 * @package Recognize\ExtraBundle\Utility
 * @author Nick Obermeijer <n.obermeijer@recognize.nl>
 */
class ArrayUtilities {

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

}