<?php

namespace Recognize\ExtraBundle\EventListener;

use Doctrine\Common\Annotations\FileCacheReader,
	Doctrine\Common\Util\ClassUtils;

use Symfony\Component\HttpKernel\KernelEvents,
	Symfony\Component\HttpKernel\Event\FilterControllerEvent,
	Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent,
	Symfony\Component\EventDispatcher\EventSubscriberInterface,
	Symfony\Component\HttpFoundation\JsonResponse;

use Recognize\ExtraBundle\Configuration\JSONResponse as JSONAnnotation;

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
	 * @param \Doctrine\Common\Annotations\FileCacheReader $reader
	 */
	public function __construct(FileCacheReader $reader) {
		$this->reader = $reader;
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
	 * @param FilterControllerEvent $event
	 */
	public function onKernelController(FilterControllerEvent $event) {
		if(!is_array($controller = $event->getController())) return; // Return when response is not an array
		list($object, $method) = $controller; // Get object and method

		$reflectionClass = new \ReflectionClass(ClassUtils::getClass($object));
		$reflectionMethod = $reflectionClass->getMethod($method);
		if($jsonAnnotations = $this->getAnnotation($this->reader->getMethodAnnotations($reflectionMethod))) {
			$event->getRequest()->attributes->set('_json_response', true); // Set JSON response to true
		}
	}

	/**
	 * @param GetResponseForControllerResultEvent $event
	 */
	public function onKernelView(GetResponseForControllerResultEvent $event) {
		if (!$event->getRequest()->attributes->get('_json_response')) return;
		$event->setResponse(new JsonResponse($event->getControllerResult()));
	}

	/**
	 * @return array
	 */
	public static function getSubscribedEvents() {
		return array(
			KernelEvents::CONTROLLER => array('onKernelController', -128),
			KernelEvents::VIEW => 'onKernelView'
		);
	}

}