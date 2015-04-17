<?php

namespace Recognize\ExtraBundle\Form\Type;

use Symfony\Component\Form\AbstractType,
	Symfony\Component\Form\FormBuilderInterface,
	Symfony\Component\Form\DataTransformerInterface;

/**
 * Class HiddenEntityType
 * @package Recognize\ExtraBundle\Form\Type
 * @author Nick Obermeijer <n.obermeijer@recognize.nl>
 */
class HiddenEntityType extends AbstractType {

	/**
	 * @var \Symfony\Component\Form\DataTransformerInterface $transformer
	 */
	private $transformer;

	/**
	 * @param \Symfony\Component\Form\DataTransformerInterface $transformer
	 */
	public function __construct(DataTransformerInterface $transformer) {
		$this->transformer = $transformer;
	}

	/**
	 * @param FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder->addViewTransformer($this->transformer);
	}

	/**
	 * @inheritDoc
	 */
	public function getParent() {
		return 'hidden';
	}

	/**
	 * @inheritDoc
	 */
	public function getName() {
		return 'entity_hidden';
	}

}