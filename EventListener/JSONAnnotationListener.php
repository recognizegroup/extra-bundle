<?php

namespace Recognize\ExtraBundle\EventListener;

use Doctrine\Common\Annotations\FileCacheReader,
	Doctrine\Common\Util\ClassUtils;

use Psr\Log\LoggerInterface;

use Symfony\Component\HttpFoundation\Response,
	Symfony\Component\HttpKernel\KernelEvents,
	Symfony\Component\HttpKernel\Event\FilterControllerEvent,
	Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent,
	Symfony\Component\EventDispatcher\EventSubscriberInterface,
	Symfony\Component\Security\Core\SecurityContextInterface,
	Symfony\Component\Security\Core\User\UserInterface,
	Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent,
	Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

use Recognize\ExtraBundle\Configuration\JSONResponse as JSONAnnotation,
	Recognize\ExtraBundle\Component\HttpFoundation\JsonResponse;

/**
 * Class JSONAnnotationListener
 * @package Recognize\ExtraBundle\EventListener
 * @author Nick Obermeijer <n.obermeijer@recognize.nl>
 */
class JSONAnnotationListener implements EventSubscriberInterface {

	/**
	 * @var \Doctrine\Common\Annotations\FileCacheReader
	 */
	private $reader;

	/**
	 * @var \Psr\Log\LoggerInterface
	 */
	private $logger;

	/**
	 * @var \Symfony\Component\Security\Core\SecurityContextInterface
	 */
	private $context;

	/**
	 * @var \Symfony\Component\HttpFoundation\Request
	 */
	private $request;

	/**
	 * @param \Doctrine\Common\Annotations\FileCacheReader $reader
	 * @param \Psr\Log\LoggerInterface $logger
	 * @param \Symfony\Component\Security\Core\SecurityContextInterface $context
	 */
	public function __construct(FileCacheReader $reader, LoggerInterface $logger, SecurityContextInterface $context) {
		$this->reader = $reader;
		$this->logger = $logger;
		$this->context = $context;
	}

	/**
	 * @param array $annotations
	 * @return array
	 */
	private function getAnnotation(Array $annotations) {
		return array_filter($annotations, function($annotation) {
			return $annotation instanceof JSONAnnotation;
		});
	}

	/**
	 * @return string
	 */
	private function getCurrentUser() {
		$userName = 'guest';
		if($this->context->getToken() instanceof TokenInterface) { // When there's a token
			if($user = $this->context->getToken()->getUser()) {
				if($user instanceof UserInterface) { // Validate if there's an user
					$userName = $user->getUsername();
				}
			}
		}
		return $userName;
	}

	/**
	 * @param GetResponseForControllerResultEvent|GetResponseForExceptionEvent $event
	 */
	private function logResponse($event) {
		try {
			$statusCode = $event->getResponse()->getStatusCode();
			$status = sprintf('[%s][%s]:', $statusCode, $this->getCurrentUser());
			$info = array(
				'attributes' => $this->request->attributes->all(),
				'request' => $this->request->request->all(),
				'query' => $this->request->query->all(),
				'response' => (($event instanceof GetResponseForControllerResultEvent)
					? $event->getControllerResult() : $event->getResponse()->getContent()
				)
			);
			if($statusCode == 200) $this->logger->info($status, $info);
			else $this->logger->error($status, $info);
		} catch(\Exception $e) {
			// Prevent possible crashes for event logging
		}
	}

	/**
	 * @param \Symfony\Component\HttpKernel\Event\FilterControllerEvent $event
	 */
	public function onKernelController(FilterControllerEvent $event) {
		if(!is_array($controller = $event->getController())) return; // Return when response is not an array
		list($object, $method) = $controller; // Get object and method

		$reflectionClass = new \ReflectionClass(ClassUtils::getClass($object));
		$reflectionMethod = $reflectionClass->getMethod($method);
		if($jsonAnnotations = $this->getAnnotation($this->reader->getMethodAnnotations($reflectionMethod))) {
			$event->getRequest()->attributes->set('_json_response', true); // Set JSON response to true
			$this->request = $event->getRequest(); // Store request locally
		}
	}

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
	 * @param \Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent $event
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
			$this->logResponse($event);
		}
	}

	/**
	 * @param \Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent $event
	 */
	public function onKernelView(GetResponseForControllerResultEvent $event) {
		if (!$event->getRequest()->attributes->get('_json_response')) return;

		$controllerData = $event->getControllerResult();
		$jsonResponse = new JsonResponse();
		if(array_key_exists('http_status_code', $controllerData)) {
			$code = $controllerData['http_status_code'];
			$jsonResponse->setStatusCode(($code != 0) ? $code : Response::HTTP_INTERNAL_SERVER_ERROR);
			unset($controllerData['http_status_code']); // Remove from response
		}
		$jsonResponse->setData($controllerData);
		$event->setResponse($jsonResponse);
		$this->logResponse($event);
	}

	/**
	 * @return array
	 */
	public static function getSubscribedEvents() {
		return array(
			KernelEvents::CONTROLLER => array('onKernelController', -128),
			KernelEvents::EXCEPTION => 'onKernelException',
			KernelEvents::VIEW => 'onKernelView'
		);
	}

}