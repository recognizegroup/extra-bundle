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
	 * @param string $seperator
	 * @return string
	 */
	public static function getUrlSlug($input, $seperator = '-') {
		$input = preg_replace('~\'s(\s|\z)~', 's$1', $input);
		$input = preg_replace('~[^\\pL\d]+~u', $seperator, $input);
		$input = trim($input, $seperator);
		if (function_exists('iconv')) { // Handle some translations
			$input = iconv('utf-8', 'us-ascii//TRANSLIT', $input);
		}
		return strtolower($input);
	}

}