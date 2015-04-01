<?php

namespace Recognize\ExtraBundle\Form\ChoiceList;

use Recognize\ExtraBundle\Entity\EntityInterface,
	Recognize\ExtraBundle\Repository\RepositoryInterface;

use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceListInterface,
	Symfony\Component\Form\Extension\Core\View\ChoiceView;

/**
 * Class AnonymousChoiceList
 * @package BeagleBoxx\AdminBundle\Form\ChoiceList
 * @author Nick Obermeijer <n.obermeijer@recognize.nl>
 */
class AnonymousChoiceList implements ChoiceListInterface {

	/**
	 * @var \Recognize\ExtraBundle\Entity\EntityInterface[]
	 */
	protected $entities = array();

	/**
	 * @var \Recognize\ExtraBundle\Repository\RepositoryInterface $repository
	 */
	protected $repository;


	/**
	 * @param \Recognize\ExtraBundle\Entity\EntityInterface[] $entities
	 * @param \Recognize\ExtraBundle\Repository\RepositoryInterface $repository
	 */
	public function __construct(array $entities, RepositoryInterface $repository) {
		$this->entities = $entities;
		$this->repository = $repository;
	}

	/**
	 * @return array
	 */
	public function getChoices() {
		return array();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getValues() {
		return array();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getPreferredViews() {
		$views = array();
		foreach($this->entities as $entity) {
			$views[] = new ChoiceView($entity, $entity->getChoiceViewId(), $entity->getChoiceViewName());
		}
		return $views;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getRemainingViews() {
		return array();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getChoicesForValues(array $values) {
		return ((!empty($values)) ? $this->repository->getEntitiesByArray($values) : array());
	}

	/**
	 * {@inheritdoc}
	 */
	public function getValuesForChoices(array $choices) {
		$choiceArr = array();
		foreach($choices as $choice) {
			if($choice instanceof EntityInterface) {
				$choiceArr[] = $choice->getChoiceViewId();
			} else throw new \Exception(sprintf('%s should implement EntityInterface', get_class($choice)));
		}
		return $choiceArr;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getIndicesForChoices(array $choices) {
		return $choices;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getIndicesForValues(array $values) {
		return $values;
	}

}