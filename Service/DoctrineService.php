<?php

namespace Recognize\ExtraBundle\Service;

use Doctrine\DBAL\Connection;

use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Class DoctrineService
 * @package Recognize\ExtraBundle\Service
 * @author Nick Obermeijer <n.obermeijer@recognize.nl>
 */
class DoctrineService {

	/**
	 * @var \Symfony\Bridge\Doctrine\RegistryInterface
	 */
	protected $registry;

	/**
	 * @var \Recognize\ExtraBundle\Repository\SettingRepository
	 */
	protected $settingRepository;


	/**
	 * @param RegistryInterface $registry
	 */
	public function __construct(RegistryInterface $registry) {
		$this->registry = $registry;
		$this->settingRepository = $registry->getRepository('RecognizeExtraBundle:Setting');
	}

	/**
	 * @return Connection
	 */
	public function getConnection() {
		return $this->registry->getConnection();
	}

	/**
	 * Simple entity check
	 * @param $entity
	 * @return bool
	 */
	public function isEntity($entity) {
		if($entityManager = $this->registry->getEntityManagerForClass(get_class($entity))) {
			return true;
		}
		return false;
	}

	/**
	 * Starts transaction mode
	 */
	public function transactionStart() {
		$this->getConnection()->beginTransaction();
	}

	/**
	 * Finish transaction
	 */
	public function transactionCommit() {
		$this->getConnection()->commit();
	}

	/**
	 * Rollback changes
	 */
	public function transactionRollback() {
		$this->getConnection()->rollback();
	}

	/**
	 * @param $entity
	 * @throws \Exception
	 */
	public function markEntityForRemoval($entity) {
		if(!$this->isEntity($entity)) throw new \Exception('Unable to mark entity for removal, object is not an valid entity');
		$this->removeEntity($entity, false);
	}

	/**
	 * @param array $entities
	 */
	public function doRemoveEntities(array $entities) {
		foreach($entities as $entity) { // loop over entities
			$this->markEntityForRemoval($entity);
		}
		$this->doFlush();
	}

	/**
	 * @param $entity
	 * @param bool $flush
	 * @throws \Exception
	 */
	public function doRemove($entity, $flush = true) {
		if(!$this->isEntity($entity)) throw new \Exception('Unable to remove entity, object is not an valid entity');
		$this->removeEntity($entity, $flush);
	}

	/**
	 * @param $entity
	 * @param bool $flush
	 */
	private function removeEntity($entity, $flush = true) {
		$this->registry->getManager()->remove($entity);
		if($flush) $this->doFlush();
	}

	/**
	 * @param $entity
	 * @param bool $flush
	 */
	public function persistEntity($entity, $flush = true) {
		$this->registry->getManager()->persist($entity);
		if($flush) $this->doFlush();
	}

	/**
	 * Triggers doctrine flush
	 */
	public function doFlush() {
		$this->registry->getManager()->flush();
	}

}