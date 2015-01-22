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
	 * @param $data
	 * @return string
	 */
	protected function jsonEncode($data) {
		$this->fixNonNumericFloatValues($data);
		return json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
	}

	/**
	 * @param array $data
	 */
	protected function fixNonNumericFloatValues(&$data) {
		foreach($data as &$value) {
			if(is_array($value) || is_object($value)) $this->fixNonNumericFloatValues($value);
			else { // I frown upon this...
				if(is_string($value) && is_numeric($value) && ($value == 0 || (substr($value, 0, 1) !== '0' && substr($value, 0, 1) != '+'))) {
					$value = (preg_match('/\d+\.\d+/', $value)) ? (float)$value : (int)$value;
				}
			}
		}
	}

	/**
	 * Overrides Symfony's json response set method
	 *
	 * @param array $data
	 * @return BaseJsonResponse
	 * @throws \InvalidArgumentException
	 */
	public function setData($data = array()) {
		$this->data = $this->jsonEncode($data);

		return $this->update();
	}

	/**
	 * @param mixed $contents
	 * @return \Symfony\Component\HttpFoundation\Response|void
	 */
	public function setContent($contents) {
		if(is_object($contents) || is_array($contents)) $contents = $this->jsonEncode($contents);
		parent::setContent($contents);
	}

}