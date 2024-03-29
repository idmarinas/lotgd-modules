<?php

namespace Lotgd\Local\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Structure of table "mod_inventory" in data base.
 *
 * Inventory of characters.
 *
 * @ORM\Table(indexes={
 *     @ORM\Index(name="user_id", columns={"user_id"})
 * })
 * @ORM\Entity(repositoryClass="Lotgd\Local\EntityRepository\ModInventoryRepository")
 */
class ModInventory
{
    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", options={"unsigned"=true})
     */
    private $userId = 0;

    /**
     * @var \Lotgd\Local\Entity\ModInventoryItem|null
     *
     * @ORM\ManyToOne(targetEntity="ModInventoryItem")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $item;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", options={"unsigned"=true})
     */
    private $sellValueGold = 0;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", options={"unsigned"=true})
     */
    private $sellValueGems = 0;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", length=65535)
     */
    private $specialValue = '';

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $equipped = false;

    /**
     * @var int|null
     *
     * @ORM\Column(type="smallint", options={"unsigned"=true})
     */
    private $charges = 0;

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
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of userId.
     *
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set the value of userId.
     *
     * @return self
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get the value of item.
     *
     * @return object
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * Set the value of item.
     *
     * @param object $item
     *
     * @return self
     */
    public function setItem($item)
    {
        $this->item = $item;

        return $this;
    }

    /**
     * Get the value of sellValueGold.
     *
     * @return int
     */
    public function getSellValueGold()
    {
        return $this->sellValueGold;
    }

    /**
     * Set the value of sellValueGold.
     *
     * @return self
     */
    public function setSellValueGold($sellValueGold)
    {
        $this->sellValueGold = $sellValueGold;

        return $this;
    }

    /**
     * Get the value of sellValueGems.
     *
     * @return int
     */
    public function getSellValueGems()
    {
        return $this->sellValueGems;
    }

    /**
     * Set the value of sellValueGems.
     *
     * @return self
     */
    public function setSellValueGems($sellValueGems)
    {
        $this->sellValueGems = $sellValueGems;

        return $this;
    }

    /**
     * Get the value of specialValue.
     *
     * @return string
     */
    public function getSpecialValue()
    {
        return $this->specialValue;
    }

    /**
     * Set the value of specialValue.
     *
     * @return self
     */
    public function setSpecialValue($specialValue)
    {
        $this->specialValue = $specialValue;

        return $this;
    }

    /**
     * Get the value of equipped.
     *
     * @return bool
     */
    public function getEquipped()
    {
        return $this->equipped;
    }

    /**
     * Set the value of equipped.
     *
     * @return self
     */
    public function setEquipped($equipped)
    {
        $this->equipped = $equipped;

        return $this;
    }

    /**
     * Get the value of charges.
     *
     * @return int
     */
    public function getCharges()
    {
        return $this->charges;
    }

    /**
     * Set the value of charges.
     *
     * @return self
     */
    public function setCharges($charges)
    {
        $this->charges = $charges;

        return $this;
    }
}
