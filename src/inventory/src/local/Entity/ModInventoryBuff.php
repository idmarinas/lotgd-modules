<?php

namespace Lotgd\Local\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Structure of table "module_inventory_item_buff" in data base.
 *
 * Buffs available for items.
 *
 * @ORM\Table(uniqueConstraints={
 *     @ORM\UniqueConstraint(name="buff_key", columns={"key"})
 * })
 * @ORM\Entity
 */
class ModInventoryBuff
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=false, options={"unsigned": true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=false, options={"collation": "utf8_general_ci"})
     */
    private $key;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private $name = '';

    /**
     * @var int
     *
     * @ORM\Column(type="smallint", nullable=false, options={"default": "1"})
     */
    private $rounds = 1;

    /**
     * @var float|null
     *
     * @ORM\Column(type="float", precision=10, scale=0, nullable=true)
     */
    private $dmgMod;

    /**
     * @var float|null
     *
     * @ORM\Column(type="float", precision=10, scale=0, nullable=true)
     */
    private $atkMod;

    /**
     * @var float|null
     *
     * @ORM\Column(type="float", precision=10, scale=0, nullable=true)
     */
    private $defMod;

    /**
     * @var float|null
     *
     * @ORM\Column(type="float", precision=10, scale=0, nullable=true)
     */
    private $badGuyDmgMod;

    /**
     * @var float|null
     *
     * @ORM\Column(type="float", precision=10, scale=0, nullable=true)
     */
    private $badGuyAtkMod;

    /**
     * @var float|null
     *
     * @ORM\Column(type="float", precision=10, scale=0, nullable=true)
     */
    private $badGuyDefMod;

    /**
     * @var int
     *
     * @ORM\Column(type="smallint", nullable=false, options={"unsigned": true})
     */
    private $lifeTap = 0;

    /**
     * @var int
     *
     * @ORM\Column(type="smallint", nullable=false, options={"unsigned": true})
     */
    private $damageShield = 0;

    /**
     * @var int
     *
     * @ORM\Column(type="smallint", nullable=false, options={"unsigned": true})
     */
    private $regen = 0;

    /**
     * @var int
     *
     * @ORM\Column(type="smallint", nullable=false, options={"unsigned": true})
     */
    private $minionCount = 0;

    /**
     * @var int
     *
     * @ORM\Column(type="smallint", nullable=false)
     */
    private $maxBadGuyDamage = 0;

    /**
     * @var int
     *
     * @ORM\Column(type="smallint", nullable=false)
     */
    private $minBadGuyDamage = 0;

    /**
     * @var int
     *
     * @ORM\Column(type="smallint", nullable=false)
     */
    private $maxGoodGuyDamage = 0;

    /**
     * @var int
     *
     * @ORM\Column(type="smallint", nullable=false)
     */
    private $minGoodGuyDamage = 0;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=350, nullable=false)
     */
    private $startMsg = '';

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=350, nullable=false)
     */
    private $roundMsg = '';

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=350, nullable=false)
     */
    private $wearOff = '';

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=350, nullable=false)
     */
    private $effectFailMsg = '';

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=350, nullable=false)
     */
    private $effectNoDmgMsg = '';

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=350, nullable=false)
     */
    private $effectMsg = '';

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $allowInPvp = false;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $allowInTrain = false;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $surviveNewDay = false;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $expireAfterFight = false;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $invulnerable = false;

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
     * Get the value of key.
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Set the value of key.
     *
     * @return self
     */
    public function setKey(string $key)
    {
        $this->key = $key;

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
    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the value of rounds.
     *
     * @return int
     */
    public function getRounds()
    {
        return $this->rounds;
    }

    /**
     * Set the value of rounds.
     *
     * @return self
     */
    public function setRounds($rounds)
    {
        $this->rounds = $rounds;

        return $this;
    }

    /**
     * Get the value of dmgMod.
     *
     * @return float|null
     */
    public function getDmgMod()
    {
        return $this->dmgMod;
    }

    /**
     * Set the value of dmgMod.
     *
     * @param float|null $dmgMod
     *
     * @return self
     */
    public function setDmgMod($dmgMod)
    {
        $this->dmgMod = '' == $dmgMod ? null : $dmgMod;

        return $this;
    }

    /**
     * Get the value of atkMod.
     *
     * @return float|null
     */
    public function getAtkMod()
    {
        return $this->atkMod;
    }

    /**
     * Set the value of atkMod.
     *
     * @param float|null $atkMod
     *
     * @return self
     */
    public function setAtkMod($atkMod)
    {
        $this->atkMod = '' == $atkMod ? null : $atkMod;

        return $this;
    }

    /**
     * Get the value of defMod.
     *
     * @return float|null
     */
    public function getDefMod()
    {
        return $this->defMod;
    }

    /**
     * Set the value of defMod.
     *
     * @param float|null $defMod
     *
     * @return self
     */
    public function setDefMod($defMod)
    {
        $this->defMod = '' == $defMod ? null : $defMod;

        return $this;
    }

    /**
     * Get the value of badGuyDmgMod.
     *
     * @return float|null
     */
    public function getBadGuyDmgMod()
    {
        return $this->badGuyDmgMod;
    }

    /**
     * Set the value of badGuyDmgMod.
     *
     * @param float|null $badGuyDmgMod
     *
     * @return self
     */
    public function setBadGuyDmgMod($badGuyDmgMod)
    {
        $this->badGuyDmgMod = '' == $badGuyDmgMod ? null : $badGuyDmgMod;

        return $this;
    }

    /**
     * Get the value of badGuyAtkMod.
     *
     * @return float|null
     */
    public function getBadGuyAtkMod()
    {
        return $this->badGuyAtkMod;
    }

    /**
     * Set the value of badGuyAtkMod.
     *
     * @param float|null $badGuyAtkMod
     *
     * @return self
     */
    public function setBadGuyAtkMod($badGuyAtkMod)
    {
        $this->badGuyAtkMod = '' == $badGuyAtkMod ? null : $badGuyAtkMod;

        return $this;
    }

    /**
     * Get the value of badGuyDefMod.
     *
     * @return float|null
     */
    public function getBadGuyDefMod()
    {
        return $this->badGuyDefMod;
    }

    /**
     * Set the value of badGuyDefMod.
     *
     * @param float|null $badGuyDefMod
     *
     * @return self
     */
    public function setBadGuyDefMod($badGuyDefMod)
    {
        $this->badGuyDefMod = '' == $badGuyDefMod ? null : $badGuyDefMod;

        return $this;
    }

    /**
     * Get the value of lifeTap.
     *
     * @return int
     */
    public function getLifeTap()
    {
        return $this->lifeTap;
    }

    /**
     * Set the value of lifeTap.
     *
     * @return self
     */
    public function setLifeTap($lifeTap)
    {
        $this->lifeTap = $lifeTap;

        return $this;
    }

    /**
     * Get the value of damageShield.
     *
     * @return int
     */
    public function getDamageShield()
    {
        return $this->damageShield;
    }

    /**
     * Set the value of damageShield.
     *
     * @return self
     */
    public function setDamageShield($damageShield)
    {
        $this->damageShield = $damageShield;

        return $this;
    }

    /**
     * Get the value of regen.
     *
     * @return int
     */
    public function getRegen()
    {
        return $this->regen;
    }

    /**
     * Set the value of regen.
     *
     * @return self
     */
    public function setRegen($regen)
    {
        $this->regen = $regen;

        return $this;
    }

    /**
     * Get the value of minionCount.
     *
     * @return int
     */
    public function getMinionCount()
    {
        return $this->minionCount;
    }

    /**
     * Set the value of minionCount.
     *
     * @return self
     */
    public function setMinionCount($minionCount)
    {
        $this->minionCount = $minionCount;

        return $this;
    }

    /**
     * Get the value of maxBadGuyDamage.
     *
     * @return int
     */
    public function getMaxBadGuyDamage()
    {
        return $this->maxBadGuyDamage;
    }

    /**
     * Set the value of maxBadGuyDamage.
     *
     * @return self
     */
    public function setMaxBadGuyDamage($maxBadGuyDamage)
    {
        $this->maxBadGuyDamage = $maxBadGuyDamage;

        return $this;
    }

    /**
     * Get the value of minBadGuyDamage.
     *
     * @return int
     */
    public function getMinBadGuyDamage()
    {
        return $this->minBadGuyDamage;
    }

    /**
     * Set the value of minBadGuyDamage.
     *
     * @return self
     */
    public function setMinBadGuyDamage($minBadGuyDamage)
    {
        $this->minBadGuyDamage = $minBadGuyDamage;

        return $this;
    }

    /**
     * Get the value of maxGoodGuyDamage.
     *
     * @return int
     */
    public function getMaxGoodGuyDamage()
    {
        return $this->maxGoodGuyDamage;
    }

    /**
     * Set the value of maxGoodGuyDamage.
     *
     * @return self
     */
    public function setMaxGoodGuyDamage($maxGoodGuyDamage)
    {
        $this->maxGoodGuyDamage = $maxGoodGuyDamage;

        return $this;
    }

    /**
     * Get the value of minGoodGuyDamage.
     *
     * @return int
     */
    public function getMinGoodGuyDamage()
    {
        return $this->minGoodGuyDamage;
    }

    /**
     * Set the value of minGoodGuyDamage.
     *
     * @return self
     */
    public function setMinGoodGuyDamage($minGoodGuyDamage)
    {
        $this->minGoodGuyDamage = $minGoodGuyDamage;

        return $this;
    }

    /**
     * Get the value of startMsg.
     *
     * @return string
     */
    public function getStartMsg()
    {
        return $this->startMsg;
    }

    /**
     * Set the value of startMsg.
     *
     * @return self
     */
    public function setStartMsg($startMsg)
    {
        $this->startMsg = $startMsg;

        return $this;
    }

    /**
     * Get the value of roundMsg.
     *
     * @return string
     */
    public function getRoundMsg()
    {
        return $this->roundMsg;
    }

    /**
     * Set the value of roundMsg.
     *
     * @return self
     */
    public function setRoundMsg($roundMsg)
    {
        $this->roundMsg = $roundMsg;

        return $this;
    }

    /**
     * Get the value of wearOff.
     *
     * @return string
     */
    public function getWearOff()
    {
        return $this->wearOff;
    }

    /**
     * Set the value of wearOff.
     *
     * @return self
     */
    public function setWearOff($wearOff)
    {
        $this->wearOff = $wearOff;

        return $this;
    }

    /**
     * Get the value of effectFailMsg.
     *
     * @return string
     */
    public function getEffectFailMsg()
    {
        return $this->effectFailMsg;
    }

    /**
     * Set the value of effectFailMsg.
     *
     * @return self
     */
    public function setEffectFailMsg($effectFailMsg)
    {
        $this->effectFailMsg = $effectFailMsg;

        return $this;
    }

    /**
     * Get the value of effectNoDmgMsg.
     *
     * @return string
     */
    public function getEffectNoDmgMsg()
    {
        return $this->effectNoDmgMsg;
    }

    /**
     * Set the value of effectNoDmgMsg.
     *
     * @return self
     */
    public function setEffectNoDmgMsg($effectNoDmgMsg)
    {
        $this->effectNoDmgMsg = $effectNoDmgMsg;

        return $this;
    }

    /**
     * Get the value of effectMsg.
     *
     * @return string
     */
    public function getEffectMsg()
    {
        return $this->effectMsg;
    }

    /**
     * Set the value of effectMsg.
     *
     * @return self
     */
    public function setEffectMsg($effectMsg)
    {
        $this->effectMsg = $effectMsg;

        return $this;
    }

    /**
     * Get the value of allowInPvp.
     *
     * @return bool
     */
    public function getAllowInPvp()
    {
        return $this->allowInPvp;
    }

    /**
     * Set the value of allowInPvp.
     *
     * @return self
     */
    public function setAllowInPvp($allowInPvp)
    {
        $this->allowInPvp = $allowInPvp;

        return $this;
    }

    /**
     * Get the value of allowInTrain.
     *
     * @return bool
     */
    public function getAllowInTrain()
    {
        return $this->allowInTrain;
    }

    /**
     * Set the value of allowInTrain.
     *
     * @return self
     */
    public function setAllowInTrain($allowInTrain)
    {
        $this->allowInTrain = $allowInTrain;

        return $this;
    }

    /**
     * Get the value of surviveNewDay.
     *
     * @return bool
     */
    public function getSurviveNewDay()
    {
        return $this->surviveNewDay;
    }

    /**
     * Set the value of surviveNewDay.
     *
     * @return self
     */
    public function setSurviveNewDay($surviveNewDay)
    {
        $this->surviveNewDay = $surviveNewDay;

        return $this;
    }

    /**
     * Get the value of expireAfterFight.
     *
     * @return bool
     */
    public function getExpireAfterFight()
    {
        return $this->expireAfterFight;
    }

    /**
     * Set the value of expireAfterFight.
     *
     * @return self
     */
    public function setExpireAfterFight($expireAfterFight)
    {
        $this->expireAfterFight = $expireAfterFight;

        return $this;
    }

    /**
     * Get the value of invulnerable.
     *
     * @return bool
     */
    public function getInvulnerable()
    {
        return $this->invulnerable;
    }

    /**
     * Set the value of invulnerable.
     *
     * @return self
     */
    public function setInvulnerable($invulnerable)
    {
        $this->invulnerable = $invulnerable;

        return $this;
    }
}
