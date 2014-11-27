<?php

namespace Recognize\ExtraBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * Class JSONAnnotationListener
 * @package Recognize\ExtraBundle\EventListener
 * @author Nick Obermeijer <n.obermeijer@recognize.nl>
 */
class I18nListener {

	/**
	 * @param GetResponseEvent $event
	 */
	public function onKernelRequest(GetResponseEvent $event) {
		if($routeParams = $event->getRequest()->get('_route_params')) {
			if(array_key_exists('_locale', $routeParams)) {
				unset($routeParams['_locale']); // Remove from route params
				$event->getRequest()->attributes->set('_route_params', $routeParams);
			}
		}
	}

}