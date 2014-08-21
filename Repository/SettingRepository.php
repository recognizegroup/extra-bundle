<?php

namespace Recognize\ExtraBundle\Repository;

/**
 * Class SettingRepository
 * @package Recognize\ExtraBundle\Repository
 * @author Nick Obermeijer <n.obermeijer@recognize.nl>
 */
class SettingRepository extends BaseRepository {

	/**
	 * @param string $key
	 * @param null $parent
	 * @return string
	 */
	public function getValueForKey($key, $parent = null) {
		$parent = (!is_null($parent)) ? $this->qb->expr()->eq('res.fk_setting_id', $parent) : $this->qb->expr()->isNull('res.fk_setting_id');
		$query = $this->getEntityManager()->createQueryBuilder()->select(
				'res.value'
			)->from('recognize_extra_setting', 'res')
			->where($this->qb->expr()->eq('res.key', $this->qb->expr()->literal($key)))
				->andWhere($parent);

		$stmt = $this->getEntityManager()->getConnection()->prepare($this->getQuery($query));
		$stmt->execute();

		return $stmt->fetch(\PDO::FETCH_COLUMN);
	}

}