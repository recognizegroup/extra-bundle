<?php

namespace Recognize\ExtraBundle\Utility;

/**
 * Class MercatorUtilities
 * @package Recognize\ExtraBundle\Utility
 * @author Nick Obermeijer <n.obermeijer@recognize.nl>
 */
class MercatorUtilities {

	/**
	 * @param float $longitude
	 * @return float
	 */
	public static function lon2x($longitude) {
		return deg2rad($longitude) * 6378137.0;
	}

	/**
	 * @param float $latitude
	 * @return float
	 */
	public static function lat2y($latitude) {
		return log(tan(M_PI_4 + deg2rad($latitude) / 2.0)) * 6378137.0;
	}

	/**
	 * @param float $x
	 * @return float
	 */
	public static function x2Lon($x) {
		return rad2deg($x / 6378137.0);
	}

	/**
	 * @param float $y
	 * @return float
	 */
	public static function y2Lat($y) {
		return rad2deg(2.0 * atan(exp($y / 6378137.0)) - M_PI_2);
	}

	/**
	 * @param float $longitude
	 * @return float
	 */
	public static function mercX($longitude) {
		$r_major = 6378137.000;
		return $r_major * deg2rad($longitude);
	}

	/**
	 * @param float $latitude
	 * @return float
	 */
	public static function mercY($latitude) {
		if ($latitude > 89.5) $latitude = 89.5;
		if ($latitude < -89.5) $latitude = -89.5;
		$r_major = 6378137.000;
		$r_minor = 6356752.3142;
		$temp = $r_minor / $r_major;
		$es = 1.0 - ($temp * $temp);
		$eccent = sqrt($es);
		$phi = deg2rad($latitude);
		$sinphi = sin($phi);
		$con = $eccent * $sinphi;
		$com = 0.5 * $eccent;
		$con = pow((1.0-$con)/(1.0+$con), $com);
		$ts = tan(0.5 * ((M_PI*0.5) - $phi))/$con;
		$y = - $r_major * log($ts);
		return $y;
	}

	/**
	 * @param float $x
	 * @param float $y
	 * @return array
	 */
	public static function merc($x,$y) {
		return array('x'=>self::mercX($x),'y'=>self::mercY($y));
	}

}