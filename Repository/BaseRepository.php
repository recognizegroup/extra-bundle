<?php

namespace Recognize\ExtraBundle\Repository;

use Doctrine\ORM\EntityRepository,
	Doctrine\ORM\Query\Expr,
	Doctrine\ORM\Mapping\ClassMetadata,
	Doctrine\ORM\QueryBuilder;

/**
 * Class BaseRepository
 * @package Recognize\ExtraBundle\Repository
 * @author Nick Obermeijer <n.obermeijer@recognize.nl>
 */
class BaseRepository extends EntityRepository {

	/**
	 * @var \Doctrine\ORM\QueryBuilder
	 */
	protected $qb;

	/**
	 * @param \Doctrine\ORM\EntityManager $em
	 * @param \Doctrine\ORM\Mapping\ClassMetadata $class
	 */
	public function __construct($em, ClassMetadata $class) {
		parent::__construct($em, $class);
		$this->qb = $this->getEntityManager()->createQueryBuilder();
	}

	/**
	 * Helper to simply escape an collection of strings
	 * @param array $collection
	 * @return array
	 */
	protected function getEscapedCollection(Array $collection) {
		foreach($collection as &$value) {
			$value = sprintf('%s',$value);
		}
		return $collection;
	}

	/**
	 * @return mixed
	 */
	protected function getEscapedEntityName() {
		return str_replace('\\','\\\\', $this->getEntityName());
	}

	/**
	 * @param QueryBuilder $query
	 * @param int $start
	 * @param bool|int $limit
	 * @return string
	 */
	public function getQuery(QueryBuilder $query, $start = 0, $limit = false) {
		return ($limit) ? sprintf('%s LIMIT %s,%s', $query->getDQL(), $start, $limit) : $query->getDQL();
	}

}