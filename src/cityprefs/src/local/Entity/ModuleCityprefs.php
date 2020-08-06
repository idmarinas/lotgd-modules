<?php

namespace Lotgd\Local\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Structure of table "module_cityprefs" in data base.
 *
 * This table store the cities in game.
 *
 * @ORM\Table(
 *     indexes={
 *         @ORM\Index(name="module", columns={"module"}),
 *         @ORM\Index(name="city_name", columns={"city_name"})
 *     }
 * )
 * @ORM\Entity(repositoryClass="Lotgd\Local\EntityRepository\ModuleCityprefsRepository")
 */
class ModuleCityprefs
{
    /**
     * @var int
     *
     * @ORM\Column(type="smallint", nullable=false, options={"unsigned": true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private $module;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private $cityName;

    /**
     * Get the value of id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the value of id.
     *
     * @return self
     */
    public function setId(int $id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of module.
     *
     * @return string
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * Set the value of module.
     *
     * @return self
     */
    public function setModule(string $module)
    {
        $this->module = $module;

        return $this;
    }

    /**
     * Get the value of cityName.
     *
     * @return string
     */
    public function getCityName()
    {
        return $this->cityName;
    }

    /**
     * Set the value of cityName.
     *
     * @return self
     */
    public function setCityName(string $cityName)
    {
        $this->cityName = $cityName;

        return $this;
    }
}
