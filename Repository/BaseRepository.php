<?php

namespace Recognize\ExtraBundle\Repository;

use Doctrine\DBAL\Query\Expression\CompositeExpression;
use Doctrine\ORM\EntityRepository,
	Doctrine\ORM\Query\Expr,
	Doctrine\ORM\Mapping\ClassMetadata,
	Doctrine\ORM\QueryBuilder,
	Doctrine\ORM\Query\AST\HavingClause;
use Recognize\ExtraBundle\Utility\SQLLimitUtilities;

/**
 * Class BaseRepository
 * @package Recognize\ExtraBundle\Repository
 * @author Nick Obermeijer <n.obermeijer@recognize.nl>
 */
class BaseRepository extends EntityRepository implements RepositoryInterface {

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
				$value = $this->qb->expr()->literal($value);
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
	 * @deprecated Use appendFilter() instead
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
	 * @param mixed $filter
	 * @throws \Exception
	 */
	public function appendFilter(QueryBuilder &$query, $filter) {
		if(!is_null($filter)) { // When set
			$filters = (!is_array($filter)) ? array($filter) : $filter;
			foreach($filters as $sFilter) { // Loop multiple filters
				if($sFilter instanceof Expr\Andx
                    || $sFilter instanceof Expr\Orx
                    || $sFilter instanceof Expr\Comparison
                ) $query->andWhere($sFilter);
                elseif($sFilter instanceof CompositeExpression) {
                    $query->andWhere($sFilter->__toString());
                }
				elseif($sFilter instanceof HavingClause) $query->andHaving($sFilter->conditionalExpression);
				elseif(is_string($sFilter)) $query->andWhere($sFilter);
				else { // Not supported filter
					throw new \Exception(sprintf(
						'Unsupported filter of type "%s" supplied; Expr\Andx, Expr\Comparison and HavingClause are supported',
						((is_object($sFilter)) ? get_class($sFilter) : gettype($sFilter))
					));
				}
			}
		}
	}

	/**
	 * @param QueryBuilder $query
	 * @param int $offset
	 * @param int $limit
	 * @param bool $count
	 * @return string
	 */
	public function getLimitedDQL(QueryBuilder $query, $offset = null, $limit = null, $count = false) {
		if(!$count && !is_null($offset) && !is_null($limit)) { // Make sure there's a limit and offset
			list($offset, $limit) = SQLLimitUtilities::getOffsetLimit($offset, $limit);
			return sprintf('%s LIMIT %s,%s', $query->getDQL(), $offset, $limit);
		}
		return $query->getDQL();
	}

	/**
	 * @deprecated Use getLimitedDQL() instead
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

	/**
	 * @return \Doctrine\DBAL\Connection
	 */
	public function getConnection() {
		return $this->getEntityManager()->getConnection();
	}

}