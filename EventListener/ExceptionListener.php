<?php

namespace Recognize\ExtraBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent,
	Symfony\Component\HttpFoundation\Response;

use Recognize\ExtraBundle\Component\HttpFoundation\JsonResponse;

/**
 * Class ExceptionListener
 * @package Recognize\ExtraBundle\EventListener
 * @author Nick Obermeijer <n.obermeijer@recognize.nl>
 */
class ExceptionListener {

	/**
	 * @param \Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent $event
	 * @return int
	 */
	protected function getStatusCode(GetResponseForExceptionEvent $event = null) {
		if($response = $event->getResponse()) { // When response is set...
			return ($response->getStatusCode() != 0) ? $response->getStatusCode() : Response::HTTP_INTERNAL_SERVER_ERROR;
		}
		return ($event->getException()->getCode() != 0) ? $event->getException()->getCode() : Response::HTTP_INTERNAL_SERVER_ERROR;
	}

	/**
	 * @param GetResponseForExceptionEvent $event
	 */
	public function onKernelException(GetResponseForExceptionEvent $event) {
		if(0 === strpos($event->getRequest()->headers->get('Accept'), 'application/json')) {
			$exception = $event->getException(); // Exception

			$jsonResponse = new JsonResponse();
			$jsonResponse->setStatusCode($this->getStatusCode($event));
			$jsonResponse->setData(array(
				'status' => 'failed',
				'data' => array('message' => $exception->getMessage())
			));

			$event->setResponse($jsonResponse);
		}
	}

}