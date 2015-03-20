<?php

namespace Recognize\ExtraBundle\Entity;

use Doctrine\ORM\Mapping as ORM,
	Doctrine\Common\Collections\ArrayCollection;

/**
 * Class Setting
 * @package Recognize\ExtraBundle\Entity
 * @author Nick Obermeijer <n.obermeijer@recognize.nl>
 *
 * @ORM\Entity(repositoryClass="Recognize\ExtraBundle\Repository\SettingRepository")
 * @ORM\Table(name="recognize_extra_setting", uniqueConstraints={
 *	@ORM\UniqueConstraint(name="UNIQUE_SETTING_KEY", columns={"fk_setting_id","`key`"})
 * })
 */
class Setting {

	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @ORM\Column(type="integer", length=11, nullable=true)
	 */
	protected $fk_setting_id;

	/**
	 * @ORM\Column(name="`key`", type="string", length=75)
	 */
	protected $key;

	/**
	 * @ORM\Column(type="string", length=100, nullable=true)
	 */
	protected $value;

	/**
	 * @ORM\ManyToOne(targetEntity="Recognize\ExtraBundle\Entity\Setting", inversedBy="settings")
	 * @ORM\JoinColumn(name="fk_setting_id", referencedColumnName="id", onDelete="CASCADE")
	 */
	private $setting;

	/**
	 * @ORM\OneToMany(targetEntity="Recognize\ExtraBundle\Entity\Setting", mappedBy="setting")
	 */
	private $settings;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->settings = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set fk_setting_id
     *
     * @param integer $fkSettingId
     * @return Setting
     */
    public function setFkSettingId($fkSettingId)
    {
        $this->fk_setting_id = $fkSettingId;

        return $this;
    }

    /**
     * Get fk_setting_id
     *
     * @return integer 
     */
    public function getFkSettingId()
    {
        return $this->fk_setting_id;
    }

    /**
     * Set key
     *
     * @param string $key
     * @return Setting
     */
    public function setKey($key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * Get key
     *
     * @return string 
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Set value
     *
     * @param string $value
     * @return Setting
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return string 
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set setting
     *
     * @param \Recognize\ExtraBundle\Entity\Setting $setting
     * @return Setting
     */
    public function setSetting(Setting $setting = null)
    {
        $this->setting = $setting;

        return $this;
    }

    /**
     * Get setting
     *
     * @return \Recognize\ExtraBundle\Entity\Setting 
     */
    public function getSetting()
    {
        return $this->setting;
    }

    /**
     * Add settings
     *
     * @param \Recognize\ExtraBundle\Entity\Setting $settings
     * @return Setting
     */
    public function addSetting(Setting $settings)
    {
        $this->settings[] = $settings;

        return $this;
    }

    /**
     * Remove settings
     *
     * @param \Recognize\ExtraBundle\Entity\Setting $settings
     */
    public function removeSetting(Setting $settings)
    {
        $this->settings->removeElement($settings);
    }

    /**
     * Get settings
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getSettings()
    {
        return $this->settings;
	}

}
