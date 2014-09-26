<?php

namespace Recognize\ExtraBundle\Utility;

use Symfony\Component\HttpFoundation\Response;

/**
 * Class DateTimeUtilities
 * @package Recognize\ExtraBundle\Component\DateTime
 * @author Nick Obermeijer <n.obermeijer@recognize.nl>
 */
class DateTimeUtilities {

	/**
	 * Simple timestamp validation
	 *
	 * @param $timestamp
	 * @return bool
	 */
	protected static function isValidTimeStamp($timestamp) {
		return ((string) (int) $timestamp === $timestamp) && ($timestamp <= PHP_INT_MAX) && ($timestamp >= ~PHP_INT_MAX);
	}

	/**
	 * @param string $error
	 * @param mixed $timestamp
	 * @return string
	 * @throws \Exception
	 */
	protected static function getError($error, $timestamp) {
		switch($error) {
			case 'invalid.unix.timestamp':
				return sprintf('Value "%s" is not an valid UNIX timestamp', $timestamp);
				break;

			case 'invalid.multiple.timestamp':
				$timestamps = (is_array($timestamp)) ? implode(', ', $timestamp) : $timestamp;
				throw new \Exception(sprintf('One or more timestamps are invalid (%s)', $timestamps), Response::HTTP_INTERNAL_SERVER_ERROR);
				break;

			case 'invalid.timestamp':
				throw new \Exception(sprintf('Value "%s" is not an valid timestamp', $timestamp), Response::HTTP_INTERNAL_SERVER_ERROR);
				break;

			default:
				throw new \Exception('Unknown error occurred', Response::HTTP_INTERNAL_SERVER_ERROR);
				break;
		}
	}

	/**
	 * @param string $modification
	 * @param null $timestamp
	 * @return \DateTime
	 * @throws \Exception
	 */
	public static function getModifiedDateTime($modification, $timestamp = null) {
		if(!$timestamp || self::isValidTimeStamp($timestamp)) {
			$dateTime = new \DateTime();
			if(!empty($timestamp)) $dateTime->setTimestamp($timestamp);
			$dateTime->modify($modification);
			return $dateTime;
		} else throw new \Exception(self::getError('invalid.timestamp', $timestamp), Response::HTTP_INTERNAL_SERVER_ERROR);
	}

	/**
	 * @param $format
	 * @param null $timestamp
	 * @param bool $returnValue
	 * @throws \Exception
	 * @return string
	 */
	public static function getFormattedDateTime($format, $timestamp = null, $returnValue = true) {
		if(!is_null($timestamp) && self::isValidTimeStamp($timestamp)) { // Validate if required
			throw new \Exception(self::getError('invalid.timestamp', $timestamp), Response::HTTP_INTERNAL_SERVER_ERROR);
		}
		$dateTime = new \DateTime('now');
		if(!empty($timestamp)) $dateTime->setTimestamp($timestamp);
		return ($returnValue) ? $dateTime->format($format) : $dateTime;
	}

	/**
	 * @param $timestamp
	 * @param string $format
	 * @return int
	 */
	public static function getFormattedTime($timestamp, $format = 'Y-m-d') {
		$timestamp = !is_int($timestamp) ? strtotime($timestamp) : $timestamp;
		return strtotime(DateTimeUtilities::getFormattedDateTime($format, $timestamp));
	}

	/**
	 * @param $timestamp
	 * @return int
	 * @throws \Exception
	 */
	public static function toMillisecondsTimeStamp($timestamp) {
		$timestamp = ($timestamp * 1000);
		if(self::isValidTimeStamp($timestamp)) {
			return $timestamp;
		} else throw new \Exception(self::getError('invalid.timestamp', $timestamp), Response::HTTP_INTERNAL_SERVER_ERROR);
	}

	/**
	 * @param $timestamp
	 * @return int
	 * @throws \Exception
	 */
	public static function fromMillisecondsTimeStamp($timestamp) {
		$timestamp = ceil($timestamp / 1000);
		if(self::isValidTimeStamp($timestamp)) {
			return $timestamp;
		}
		else throw new \Exception(self::getError('invalid.unix.timestamp', $timestamp), Response::HTTP_INTERNAL_SERVER_ERROR);
	}

	/**
	 * @param int $timestamp1
	 * @param int $timestamp2
	 * @return \DateInterval
	 */
	public static function getTimeStampDiff($timestamp1, $timestamp2) {
		if($timestamp1 > $timestamp2) return self::getDateTimeFromTimeStamp($timestamp1)->diff(self::getDateTimeFromTimeStamp($timestamp2));
		else return self::getDateTimeFromTimeStamp($timestamp2)->diff(self::getDateTimeFromTimeStamp($timestamp1));
	}

	/**
	 * @param int $timestamp
	 * @param int $offset
	 * @param bool $unix
	 * @throws \Exception
	 * @return int
	 */
	public static function getTimeStampOffset($timestamp, $offset, $unix = false) {
		if(self::isValidTimeStamp($timestamp)) {
			$offsetTimestamp = ($timestamp + (86400 * $offset));
			if(self::isValidTimeStamp($offsetTimestamp)) {
				return ($unix) ? self::toMillisecondsTimeStamp($offsetTimestamp) : $offsetTimestamp;
			} else throw new \Exception(self::getError('invalid.outcome.timestamp', $offsetTimestamp), Response::HTTP_INTERNAL_SERVER_ERROR);
		} else throw new \Exception(self::getError('invalid.timestamp', $timestamp), Response::HTTP_INTERNAL_SERVER_ERROR);
	}

	/**
	 * @param int $timestamp
	 * @param int $repeat
	 * @param bool $unix
	 * @throws \Exception
	 * @return array
	 */
	public static function getTimeStampOffsets($timestamp, $repeat = 1, $unix = false) {
		if(self::isValidTimeStamp($timestamp)) {
			$timestamps = array();
			for($i = 0; $i < $repeat; $i++) {
				$timestamps[] = self::getTimeStampOffset($timestamp, $i, $unix);
			}
			return $timestamps;
		} else throw new \Exception(self::getError('invalid.timestamp', $timestamp), Response::HTTP_INTERNAL_SERVER_ERROR);
	}

	/**
	 * @param array $timestamps
	 * @param bool $unix
	 * @return array
	 */
	public static function getTimeStampsWeekendFlags(array $timestamps, $unix = false) {
		$weekendFlagged = array();
		foreach($timestamps as $timestamp) {
			$weekendFlagged[] = array('timestamp' => (($unix) ? self::toMillisecondsTimeStamp($timestamp) : $timestamp), 'weekend' => self::isWeekend($timestamp));
		}
		return $weekendFlagged;
	}

	/**
	 * @param int $timestamp
	 * @param int $fromTimeStamp
	 * @param int $untilTimeStamp
	 * @return bool
	 * @throws \Exception
	 */
	public static function isTimeStampBetween($timestamp, $fromTimeStamp, $untilTimeStamp) {
		if(self::isValidTimeStamp($timestamp) && self::isValidTimeStamp($fromTimeStamp) && self::isValidTimeStamp($untilTimeStamp)) {
			return ($timestamp > $fromTimeStamp && $timestamp < $untilTimeStamp);
		} else throw new \Exception(self::getError('invalid.multiple.timestamp', array($timestamp, $fromTimeStamp, $untilTimeStamp)), Response::HTTP_INTERNAL_SERVER_ERROR);
	}


	/**
	 * @param int $timestamp
	 * @return int
	 * @throws \Exception
	 */
	public static function getTimeStampMidnight($timestamp) {
		if(self::isValidTimeStamp($timestamp)) {
			return strtotime(date('Y-m-d', $timestamp));
		} throw new \Exception(self::getError('invalid.timestamp', $timestamp), Response::HTTP_INTERNAL_SERVER_ERROR);
	}

	/**
	 * @param $timestamp
	 * @throws \Exception
	 * @return bool
	 */
	public static function isWeekend($timestamp) {
		if(self::isValidTimeStamp($timestamp)) return (date('N', $timestamp) >= 6);
		else throw new \Exception(self::getError('invalid.timestamp', $timestamp), Response::HTTP_INTERNAL_SERVER_ERROR);
	}

	/**
	 * @param int $timestamp
	 * @param int|array $day
	 * @return bool
	 * @throws \Exception
	 */
	public static function isDayOfWeek($timestamp, $day) {
		if(self::isValidTimeStamp($timestamp)) {
			$dow = date('N', $timestamp); // Get day number
			return ((is_array($day)) ? in_array($dow, $day) : ($dow == $day));
		}
		else throw new \Exception(self::getError('invalid.timestamp', $timestamp), Response::HTTP_INTERNAL_SERVER_ERROR);
	}

	/**
	 * @param $timestamp
	 * @return \DateTime
	 */
	public static function getDateTimeFromTimeStamp($timestamp) {
		$dateTime = new \DateTime();
		$dateTime->setTimestamp($timestamp);
		return $dateTime;
	}

}