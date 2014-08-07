<?php

namespace Recognize\ExtraBundle\Component\Form;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class FormUtilities
 * @package Recognize\ExtraBundle\Component\Form
 * @author Nick Obermeijer <n.obermeijer@recognize.nl>
 */
class FormUtilities {

	/**
	 * @param Form $form
	 * @param $error
	 * @throws \Exception
	 */
	public static function addFromError(Form &$form, $error) {
		if(!is_string($error)) throw new \Exception('Unable to add error to form, type of error has to be string', Response::HTTP_PRECONDITION_FAILED);
		$form->addError(new FormError($error));
	}

	/**
	 * @param Form $form
	 * @return array
	 */
	public static function errorsToArray(Form $form) {
		$errors = array();
		foreach($form->getErrors() as $error) {
			/** @var \Symfony\Component\Form\FormError $error */
			$errors[] = $error->getMessage();
		}
		return $errors;
	}

}