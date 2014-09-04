<?php

namespace Recognize\ExtraBundle\Service;

use Doctrine\DBAL\Connection,
	Doctrine\DBAL\Query\QueryBuilder;

use Recognize\ExtraBundle\Utility\ArrayUtilities;
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
	 * @var \Doctrine\ORM\QueryBuilder
	 */
	protected $qb;

	/**
	 * @var array
	 */
	protected $orderByFields = array();

	/**
	 * @var bool
	 */
	protected $transaction = false;


	/**
	 * @param RegistryInterface $registry
	 */
	public function __construct(RegistryInterface $registry) {
		$this->registry = $registry;
		$this->settingRepository = $registry->getRepository('RecognizeExtraBundle:Setting');
		$this->qb = new QueryBuilder($this->getConnection());
	}

	/**
	 * @return Connection
	 */
	public function getConnection() {
		return $this->registry->getConnection();
	}

	/**
	 * @param $value
	 * @return int|string
	 */
	protected function getExpressionValue($value) {
		return (!is_numeric($value)) ? $this->qb->expr()->literal("%$value%") : $value;
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
	 * Creates array to determine query's orderBy, currently supports one order clause
	 * TODO: Add support for multiple order fields
	 * @param $field
	 * @param string $order
	 * @return array
	 */
	public function getOrderBy($field, $order = 'ASC') {
		return (array_key_exists($field, $this->orderByFields) && in_array(strtoupper($order), array('ASC','DESC'))) ?
			array($this->orderByFields[$field] => $order) : array();
	}

	/**
	 * Starts transaction mode
	 */
	public function transactionStart() {
		if(!$this->transaction){
			$this->getConnection()->beginTransaction();
			$this->transaction = true;
		}
	}

	/**
	 * Finish transaction
	 */
	public function transactionCommit() {
		if($this->transaction) {
			$this->getConnection()->commit();
			$this->transaction = false;
		}
	}

	/**
	 * Rollback changes
	 */
	public function transactionRollback() {
		if($this->transaction) {
			$this->getConnection()->rollback();
			$this->transaction = false;
		}
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