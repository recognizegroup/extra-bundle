<?php

namespace Recognize\ExtraBundle\Service;

use Symfony\Component\Routing\Router;

/**
 * Class RoutingService
 * @package Recognize\ExtraBundle\Service
 * @author Nick Obermeijer <n.obermeijer@recognize.nl>
 */
class RoutingService {

	/**
	 * @var \Symfony\Component\Routing\Router
	 */
	protected $router;

	/**
	 * @param \Symfony\Component\Routing\Router $router
	 */
	public function __construct(Router $router) {
		$this->router = $router;
	}

	/**
	 * Returns all available routes
	 * @param null $filter
	 * @return array
	 */
	public function getRoutes($filter = null) {
		$routes = array();
		$availableRoutes = $this->router->getRouteCollection()->all();
		foreach($availableRoutes as $name => $route) {
			if(is_null($filter) || (0 === strpos($name, $filter))) {
				$routes[] = array(
					'name' => $name,
					'path' => $route->getPath()
				);
			}
		}
		return $routes;
	}

}