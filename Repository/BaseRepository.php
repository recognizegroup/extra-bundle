<?php

namespace Recognize\ExtraBundle\Repository;

use Doctrine\ORM\EntityRepository,
	Doctrine\ORM\Query\Expr,
	Doctrine\ORM\Mapping\ClassMetadata,
	Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\AST\HavingClause;

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
			if(!is_numeric($value)) { // When it's not an numeric
				$value = sprintf('%s', $value);
			}
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
	 * @param array $sorting
	 */
	protected function addOrders(QueryBuilder &$query, array $sorting) {
		foreach($sorting as $sort => $order) {
			if(in_array(strtoupper($order), array('ASC','DESC')) && !empty($sort)) {
				$query->addOrderBy($sort, $order);
			}
		}
	}

	/**
	 * @param QueryBuilder $query
	 * @param $filter
	 * @throws \Exception
	 */
	public function addFilter(QueryBuilder &$query, $filter) {
		if(!is_null($filter)) { // When set
			if($filter instanceof Expr\Andx || $filter instanceof Expr\Comparison) $query->andWhere($filter);
			elseif($filter instanceof HavingClause) $query->having($filter->conditionalExpression);
			else throw new \Exception('Unsupported filter supplied; Expr\Andx, Expr\Comparison and HavingClause are supported');
		}
	}

	/**
	 * @param QueryBuilder $query
	 * @param int $start
	 * @param bool|int $limit
	 * @return string
	 */
	public function getQuery(QueryBuilder $query, $start = 0, $limit = false) {
		return ($limit) ? sprintf('%s LIMIT %s,%s', $query->getDQL(), ($start*$limit), $limit) : $query->getDQL();
	}

	/**
	 * Simple wrapper to shorten literal usage
	 * @param $string
	 * @return Expr\Literal
	 */
	public function getLiteral($string) {
		return $this->qb->expr()->literal($string);
	}

	/**
	 * @param array $collection
	 * @param string $field
	 * @return array
	 */
	public function getEntitiesByArray(Array $collection, $field = 'id') {
		return $this->createQueryBuilder('entity')
			->where($this->qb->expr()->in(sprintf('entity.%s', $field), $this->getEscapedCollection($collection)))
			->getQuery()->getResult();
	}

}