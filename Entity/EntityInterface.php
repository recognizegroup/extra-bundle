<?php

namespace Recognize\ExtraBundle\Entity;

/**
 * Interface EntityInterface
 * @package Recognize\ExtraBundle\Entity
 * @author Nick Obermeijer <n.obermeijer@recognize.nl>
 */
interface EntityInterface {

	/**
	 * @return int
	 */
	public function getChoiceViewId();

	/**
	 * @return string
	 */
	public function getChoiceViewName();

}