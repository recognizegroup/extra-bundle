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
	 */
	public function getElementsByColumnValue(array $array, $column, $value) {
		$results = array();
		foreach($array as $element) {
			if(is_array($element) && array_key_exists($column, $element) && $element[$column] == $value) {
				$results[] = $element;
			}
		}
		return $results;
	}

}