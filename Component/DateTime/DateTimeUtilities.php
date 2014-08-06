<?php

namespace Recognize\ExtraBundle\Component\DateTime;

class DateTimeUtilities {

	/**
	 * @param $modification
	 * @param null $timestamp
	 * @return \DateTime
	 */
	public static function getModifiedDateTime($modification, $timestamp = null) {
		$dateTime = new \DateTime();
		if(!empty($timestamp)) $dateTime->setTimestamp($timestamp);
		$dateTime->modify($modification);
		return $dateTime;
	}

}