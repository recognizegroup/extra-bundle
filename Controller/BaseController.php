<?php

namespace Recognize\ExtraBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

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

	/**
	 * @return string
	 */
	protected function getRootDir() {
		return $this->get('kernel')->getRootDir();
	}

	/**
	 * @return string
	 */
	protected function getWebDir() {
		return $this->get('kernel')->getRootDir() . '/../web';
	}

	/**
	 * @param Request $request
	 * @return string
	 */
	protected function getRequestDir(Request $request = null) {
		return $this->get('kernel')->getRootDir() . '/../web' . ($request != null ? $request->getBasePath() : '');
	}

}
