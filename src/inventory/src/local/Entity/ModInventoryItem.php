<?php

namespace Lotgd\Local\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Structure of table "module_inventory_item" in data base.
 *
 * Items available in game.
 *
 * @ORM\Table(indexes={
 *     @ORM\Index(name="find_rarity", columns={"find_rarity"}),
 *     @ORM\Index(name="find_chance", columns={"find_chance"})
 * })
 * @ORM\Entity
 */
class ModInventoryItem
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
     * @var string|null
     *
     * @ORM\Column(type="string", length=100)
     */
    private $class = '';

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=150)
     */
    private $name = '';

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=150)
     */
    private $image = '';

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", length=65535)
     */
    private $description = '';

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", options={"unsigned"=true})
     */
    private $gold = 0;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", options={"unsigned"=true})
     */
    private $gems = 0;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", options={"unsigned"=true})
     */
    private $weight = 0;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default"="1"})
     */
    private $droppable = false;

    /**
     * @var int|null
     *
     * @ORM\Column(type="smallint", options={"default"="1"})
     */
    private $level = 1;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", options={"unsigned"=true})
     */
    private $dragonkills = 0;

    /**
     * @var \Lotgd\Local\Entity\ModInventoryBuff|null
     *
     * @ORM\ManyToOne(targetEntity="ModInventoryBuff")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $buff;

    /**
     * @var int|null
     *
     * @ORM\Column(type="smallint")
     */
    private $charges = 0;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $hide = false;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", length=65535)
     */
    private $customValue = '';

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", length=65535)
     */
    private $execValue = '';

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=70)
     */
    private $execText = '';

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", length=65535)
     */
    private $execRequisites = '';

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", length=65535)
     */
    private $execCustomValue = '';

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=70)
     */
    private $noEffectText = '';

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer")
     */
    private $activationHook = 0;

    /**
     * @var int|null
     *
     * @ORM\Column(type="smallint")
     */
    private $findChance = 0;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=50, options={"default"="common"})
     */
    private $findRarity = 'common';

    /**
     * @var int|null
     *
     * @ORM\Column(type="smallint")
     */
    private $looseChance = 0;

    /**
     * @var int|null
     *
     * @ORM\Column(type="smallint")
     */
    private $dkLooseChance = 0;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default"="1"})
     */
    private $sellable = false;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default"="1"})
     */
    private $buyable = false;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $uniqueForServer = false;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $uniqueForPlayer = false;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $equippable = false;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=20, options={"default"="none"})
     */
    private $equipWhere = 'none';

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
     * Get the value of class.
     *
     * @return string|null
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Set the value of class.
     *
     * @param string|null $class
     *
     * @return self
     */
    public function setClass($class)
    {
        $this->class = $class;

        return $this;
    }

    /**
     * Get the value of name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the value of name.
     *
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the value of image.
     *
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Set the value of image.
     *
     * @return self
     */
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get the value of description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set the value of description.
     *
     * @return self
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get the value of gold.
     *
     * @return int
     */
    public function getGold()
    {
        return $this->gold;
    }

    /**
     * Set the value of gold.
     *
     * @return self
     */
    public function setGold($gold)
    {
        $this->gold = $gold;

        return $this;
    }

    /**
     * Get the value of gems.
     *
     * @return int
     */
    public function getGems()
    {
        return $this->gems;
    }

    /**
     * Set the value of gems.
     *
     * @return self
     */
    public function setGems($gems)
    {
        $this->gems = $gems;

        return $this;
    }

    /**
     * Get the value of weight.
     *
     * @return int
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * Set the value of weight.
     *
     * @return self
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;

        return $this;
    }

    /**
     * Get the value of droppable.
     *
     * @return bool
     */
    public function getDroppable()
    {
        return $this->droppable;
    }

    /**
     * Set the value of droppable.
     *
     * @return self
     */
    public function setDroppable($droppable)
    {
        $this->droppable = $droppable;

        return $this;
    }

    /**
     * Get the value of level.
     *
     * @return int
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * Set the value of level.
     *
     * @param int $level
     *
     * @return self
     */
    public function setLevel($level)
    {
        $this->level = $level;

        return $this;
    }

    /**
     * Get the value of dragonkills.
     *
     * @return int
     */
    public function getDragonkills()
    {
        return $this->dragonkills;
    }

    /**
     * Set the value of dragonkills.
     *
     * @return self
     */
    public function setDragonkills($dragonkills)
    {
        $this->dragonkills = $dragonkills;

        return $this;
    }

    /**
     * Get the value of buff.
     *
     * @return object
     */
    public function getBuff()
    {
        return $this->buff;
    }

    /**
     * Set the value of buff.
     *
     * @param object $buff
     *
     * @return self
     */
    public function setBuff($buff)
    {
        $this->buff = $buff;

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

    /**
     * Get the value of hide.
     *
     * @return bool
     */
    public function getHide()
    {
        return $this->hide;
    }

    /**
     * Set the value of hide.
     *
     * @return self
     */
    public function setHide($hide)
    {
        $this->hide = $hide;

        return $this;
    }

    /**
     * Get the value of customValue.
     *
     * @return string
     */
    public function getCustomValue()
    {
        return $this->customValue;
    }

    /**
     * Set the value of customValue.
     *
     * @return self
     */
    public function setCustomValue($customValue)
    {
        $this->customValue = $customValue;

        return $this;
    }

    /**
     * Get the value of execValue.
     *
     * @return string
     */
    public function getExecValue()
    {
        return $this->execValue;
    }

    /**
     * Set the value of execValue.
     *
     * @return self
     */
    public function setExecValue($execValue)
    {
        $this->execValue = $execValue;

        return $this;
    }

    /**
     * Get the value of execText.
     *
     * @return string
     */
    public function getExecText()
    {
        return $this->execText;
    }

    /**
     * Set the value of execText.
     *
     * @return self
     */
    public function setExecText($execText)
    {
        $this->execText = $execText;

        return $this;
    }

    /**
     * Get the value of execRequisites.
     *
     * @return string
     */
    public function getExecRequisites()
    {
        return $this->execRequisites;
    }

    /**
     * Set the value of execRequisites.
     *
     * @return self
     */
    public function setExecRequisites($execRequisites)
    {
        $this->execRequisites = $execRequisites;

        return $this;
    }

    /**
     * Get the value of execCustomValue.
     *
     * @return string
     */
    public function getExecCustomValue()
    {
        return $this->execCustomValue;
    }

    /**
     * Set the value of execCustomValue.
     *
     * @return self
     */
    public function setExecCustomValue($execCustomValue)
    {
        $this->execCustomValue = $execCustomValue;

        return $this;
    }

    /**
     * Get the value of noEffectText.
     *
     * @return string
     */
    public function getNoEffectText()
    {
        return $this->noEffectText;
    }

    /**
     * Set the value of noEffectText.
     *
     * @return self
     */
    public function setNoEffectText($noEffectText)
    {
        $this->noEffectText = $noEffectText;

        return $this;
    }

    /**
     * Get the value of activationHook.
     *
     * @return int
     */
    public function getActivationHook()
    {
        return $this->activationHook;
    }

    /**
     * Set the value of activationHook.
     *
     * @return self
     */
    public function setActivationHook($activationHook)
    {
        $this->activationHook = $activationHook;

        return $this;
    }

    /**
     * Get the value of findChance.
     *
     * @return int
     */
    public function getFindChance()
    {
        return $this->findChance;
    }

    /**
     * Set the value of findChance.
     *
     * @return self
     */
    public function setFindChance($findChance)
    {
        $this->findChance = $findChance;

        return $this;
    }

    /**
     * Get the value of findRarity.
     *
     * @return string
     */
    public function getFindRarity()
    {
        return $this->findRarity;
    }

    /**
     * Set the value of findRarity.
     *
     * @return self
     */
    public function setFindRarity($findRarity)
    {
        $this->findRarity = $findRarity;

        return $this;
    }

    /**
     * Get the value of looseChance.
     *
     * @return int
     */
    public function getLooseChance()
    {
        return $this->looseChance;
    }

    /**
     * Set the value of looseChance.
     *
     * @return self
     */
    public function setLooseChance($looseChance)
    {
        $this->looseChance = $looseChance;

        return $this;
    }

    /**
     * Get the value of dkLooseChance.
     *
     * @return int
     */
    public function getDkLooseChance()
    {
        return $this->dkLooseChance;
    }

    /**
     * Set the value of dkLooseChance.
     *
     * @return self
     */
    public function setDkLooseChance($dkLooseChance)
    {
        $this->dkLooseChance = $dkLooseChance;

        return $this;
    }

    /**
     * Get the value of sellable.
     *
     * @return bool
     */
    public function getSellable()
    {
        return $this->sellable;
    }

    /**
     * Set the value of sellable.
     *
     * @return self
     */
    public function setSellable($sellable)
    {
        $this->sellable = $sellable;

        return $this;
    }

    /**
     * Get the value of buyable.
     *
     * @return bool
     */
    public function getBuyable()
    {
        return $this->buyable;
    }

    /**
     * Set the value of buyable.
     *
     * @return self
     */
    public function setBuyable($buyable)
    {
        $this->buyable = $buyable;

        return $this;
    }

    /**
     * Get the value of uniqueForServer.
     *
     * @return bool
     */
    public function getUniqueForServer()
    {
        return $this->uniqueForServer;
    }

    /**
     * Set the value of uniqueForServer.
     *
     * @return self
     */
    public function setUniqueForServer($uniqueForServer)
    {
        $this->uniqueForServer = $uniqueForServer;

        return $this;
    }

    /**
     * Get the value of uniqueForPlayer.
     *
     * @return bool
     */
    public function getUniqueForPlayer()
    {
        return $this->uniqueForPlayer;
    }

    /**
     * Set the value of uniqueForPlayer.
     *
     * @return self
     */
    public function setUniqueForPlayer($uniqueForPlayer)
    {
        $this->uniqueForPlayer = $uniqueForPlayer;

        return $this;
    }

    /**
     * Get the value of equippable.
     *
     * @return bool
     */
    public function getEquippable()
    {
        return $this->equippable;
    }

    /**
     * Set the value of equippable.
     *
     * @return self
     */
    public function setEquippable($equippable)
    {
        $this->equippable = $equippable;

        return $this;
    }

    /**
     * Get the value of equipWhere.
     *
     * @return string
     */
    public function getEquipWhere()
    {
        return $this->equipWhere;
    }

    /**
     * Set the value of equipWhere.
     *
     * @return self
     */
    public function setEquipWhere($equipWhere)
    {
        $this->equipWhere = $equipWhere;

        return $this;
    }
}
