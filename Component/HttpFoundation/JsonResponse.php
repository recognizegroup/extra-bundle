<?php

namespace Recognize\ExtraBundle\Component\HttpFoundation;

use Symfony\Component\HttpFoundation\JsonResponse as BaseJsonResponse;

/**
 * Class JsonResponse
 * @package Recognize\ExtraBundle\Component\HttpFoundation
 * @author Nick Obermeijer <n.obermeijer@recognize.nl>
 */
class JsonResponse extends BaseJsonResponse {

	/**
	 * Overrides Symfony's jsonresponse set method
	 *
	 * @param array $data
	 * @return BaseJsonResponse
	 * @throws \InvalidArgumentException
	 */
	public function setData($data = array()) {
		$this->data = json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_NUMERIC_CHECK);

		return $this->update();
	}

}