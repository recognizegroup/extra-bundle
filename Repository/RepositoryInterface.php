<?php

namespace Recognize\ExtraBundle\Repository;

/**
 * Interface RepositoryInterface
 * @package Recognize\ExtraBundle\Repository
 * @author Nick Obermeijer <n.obermeijer@recognize.nl>
 */
interface RepositoryInterface {

	/**
	 * @param array $collection
	 * @param string $field
	 * @return mixed
	 */
	public function getEntitiesByArray(array $collection, $field = 'id');

	/**
	 * @return \Doctrine\DBAL\Connection
	 */
	public function getConnection();

}