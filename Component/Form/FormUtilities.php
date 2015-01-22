<?php

namespace Recognize\ExtraBundle\Component\Form;

use Symfony\Component\Form\Form,
	Symfony\Component\Form\FormError,
	Symfony\Component\HttpFoundation\Response,
	Symfony\Component\Validator\ConstraintViolation;

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
		foreach($form->getErrors(true) as $error) {
			/** @var \Symfony\Component\Form\FormError $error */
			if($error instanceof ConstraintViolation && method_exists($error, 'getCause')) {
				$cause = $error->getCause();
				$key = $cause->getPropertyPath();
				$errors[$key] = $error->getMessage();
			} else {
				$errors[] = $error->getMessage();
			}
		}
		return $errors;
	}

	/**
	 * @param Form $form
	 * @return int
	 */
	public static function hasErrors(Form $form) {
		return (sizeof($form->getErrors(true)));
	}

}