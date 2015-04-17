<?php

namespace Recognize\ExtraBundle\Utility;

use Symfony\Component\HttpFoundation\Request;

/**
 * Class SQLLimitUtilities
 * @package Recognize\ExtraBundle\Utility
 * @author Nick Obermeijer <n.obermeijer@recognize.nl>
 */
class SQLLimitUtilities {

	const DEFAULT_PAGINATION_OFFSET	= 0;
	const DEFAULT_PAGINATION_LIMIT	= 10;

	/**
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 * @return array
	 */
	public static function getOffsetLimitFromRequest(Request $request) {
		return array(self::getOffsetFromRequest($request), self::getLimitFromRequest($request));
	}

	/**
	 * @param Request $request
	 * @return int
	 */
	public static function getOffsetFromRequest(Request $request) {
		$limit = self::getLimitFromRequest($request);
		return self::getOffset($request->get('offset'), $limit);
	}

	/**
	 * @param Request $request
	 * @return int
	 */
	public static function getLimitFromRequest(Request $request) {
		return self::getLimit($request->get('limit'));
	}

	/**
	 * @param null|int $offset
	 * @param null|int $limit
	 * @return array
	 */
	public static function getOffsetLimit($offset = null, $limit = null) {
		return array(self::getOffset($offset, $limit), $limit);
	}

	/**
	 * @param null|int $offset
	 * @param null|int $limit
	 * @return int
	 */
	public static function getOffset($offset = null, $limit = null) {
		return  (((!$offset) ? self::DEFAULT_PAGINATION_OFFSET : ($offset - 1)) * $limit);
	}

	/**
	 * @param null|int $limit
	 * @return int
	 */
	public static function getLimit($limit = null) {
		return  ((!$limit) ? self::DEFAULT_PAGINATION_LIMIT : $limit);
	}

}