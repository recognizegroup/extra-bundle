<?php

namespace Recognize\ExtraBundle\Service;

use Symfony\Component\HttpFoundation\Request;

use Recognize\ExtraBundle\Utility\ArrayUtilities;

/**
 * Class RequestDataService
 * @package Recognize\ExtraBundle\Service
 * @author Nick Obermeijer <n.obermeijer@recognize.nl>
 */
class RequestDataService {

	/**
	 * @var \Psr\Log\LoggerInterface
	 */
	protected $logger;

	/**
	 * @var array
	 */
	protected $config;


	/**
	 * @param array $config
	 */
	public function __construct(array $config = array()) {
		$this->config = $config;
	}

	/**
	 * @param Request $request
	 * @return array
	 */
	public function getRequestData(Request $request = null) {
		if(empty($request)) return array();

		$data = array( // Data used for an request
			'attributes' => $request->attributes->all(),
			'headers' => $request->headers->all(),
			'request' => $request->request->all(),
			'query' => $request->query->all()
		);

		ArrayUtilities::removeValues($data, ArrayUtilities::getColumnValue($this->config, 'exclude_fields', array()), true);

		return array_filter($data); // Filter empty arrays
	}

}