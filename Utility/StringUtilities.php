<?php

namespace Recognize\ExtraBundle\Utility;

/**
 * Class StringUtilities
 * @package Recognize\ExtraBundle\Utility
 * @author Nick Obermeijer <n.obermeijer@recognize.nl>
 */
class StringUtilities {

	/**
	 * @param string $input
	 * @param string $delimiter
	 * @return string
	 */
	public static function getUrlSlug($input, $delimiter = '-') {
		setlocale(LC_ALL, 'en_US.UTF8');
		if (function_exists('iconv')) { // Check if method exists
			$input = iconv('UTF-8', 'ASCII//TRANSLIT', $input);
		}
		$input = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $input);
		$input = strtolower(trim($input, '-'));
		$input = preg_replace("/[\/_|+ -]+/", $delimiter, $input);
		return strtolower($input);
	}

}