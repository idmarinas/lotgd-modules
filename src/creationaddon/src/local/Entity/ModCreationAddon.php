<?php

namespace Lotgd\Local\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Structure of table "module_creation_addon" in data base.
 *
 * This table store the names forbidden in the creation of a character.
 *
 * @ORM\Table
 * @ORM\Entity
 */
class ModCreationAddon
{
    /**
     * @var int|null
     *
     * @ORM\Column(type="smallint", options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=50)
     */
    private $badName;

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
     * Get the value of badName.
     *
     * @return string
     */
    public function getBadName()
    {
        return $this->badName;
    }

    /**
     * Set the value of badName.
     *
     * @return self
     */
    public function setBadName(string $badName)
    {
        $this->badName = $badName;

        return $this;
    }
}
