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

	/**
	 * @param $string
	 * @return mixed
	 */
	public static function stripInvalidXML($string) {
		if (empty($string)) return $string;
		$ret = "";
		$current = null;
		$length = strlen($string);
		for ($i=0; $i < $length; $i++) {
			$current = ord($string{$i});
			if (($current == 0x9) || ($current == 0xA) || ($current == 0xD) || (($current >= 0x20) && ($current <= 0xD7FF)) ||
					(($current >= 0xE000) && ($current <= 0xFFFD)) || (($current >= 0x10000) && ($current <= 0x10FFFF))) {
				$ret .= chr($current);
			} else {
				$ret .= " ";
			}
		}
		return $ret;
	}

}