<?php

namespace Recognize\ExtraBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture,
	Doctrine\Common\Persistence\ObjectManager;

use Recognize\ExtraBundle\Repository\RepositoryInterface;

/**
 * Class SqlConnectionFixture
 * @package Recognize\ExtraBundle\DataFixtures\ORM
 * @author Nick Obermeijer <n.obermeijer@recognize.nl>
 */
class SqlConnectionFixture extends AbstractFixture {

	/**
	 * @var \Doctrine\Common\Persistence\ObjectManager
	 */
	protected $manager;

	/**
	 * @param ObjectManager $manager
	 */
	protected function setManager(ObjectManager $manager) {
		$this->manager = $manager;
	}

	/**
	 * @return \Doctrine\Common\Persistence\ObjectManager
	 */
	protected function getManager() {
		return $this->manager;
	}

	/**
	 * @return string
	 * @throws \Exception
	 */
	protected function getEntity() {
		if(!isset($this->entity)) throw new \Exception('Variable entity has not been set');

		return $this->entity;
	}

	/**
	 * @return object
	 * @throws \Exception
	 */
	protected function getRepository() {
		if($entityRepository = $this->getManager()->getRepository($this->getEntity())) {
			if(!$entityRepository instanceof RepositoryInterface) {
				throw new \Exception(sprintf('"%s" does not implement "Recognize\ExtraBundle\Repository\RepositoryInterface"', get_class($entityRepository)));
			} else { // Return repository
				return $entityRepository;
			}
		} throw new \Exception(sprintf('Reference for "%s" not found', $this->getEntity()));
	}

	/**
	 * @return \Doctrine\DBAL\Connection
	 * @throws \Exception
	 */
	protected function getConnection() {
		return $this->getRepository()->getConnection();
	}

	/**
	 * @param string $statement
	 * @throws \Doctrine\DBAL\DBALException
	 */
	protected function execute($statement) {
		$this->getConnection()->prepare($statement)->execute();
	}

	/**
	 * Load data fixtures with the passed EntityManager
	 *
	 * @param ObjectManager $manager
	 * @throws \Exception
	 */
	public function load(ObjectManager $manager) {
		$this->setManager($manager);
		$this->queries();
	}

	/**
	 * Implement this method instead of load()
	 */
	public function queries() {
		// TODO: implement queries()
	}

}