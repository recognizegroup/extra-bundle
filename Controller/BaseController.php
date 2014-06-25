<?php

namespace Recognize\ExtraBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller,
	Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * Class BaseController
 * @package Recognize\ExtraBundle\Controller
 * @author Nick Obermeijer <n.obermeijer@recognize.nl>
 */
class BaseController extends Controller {

	/**
	 * Wrapper for setting flash messages
	 * @param String $type
	 * @param String $message
	 */
	protected function setFlashMessage($type, $message) {
		$this->get('session')->getFlashBag()->add($type, $message);
	}

	/**
	 * @param $type
	 * @return mixed
	 */
	protected function hasFlashMessage($type) {
		return $this->get('session')->getFlashBag()->has($type);
	}

}
