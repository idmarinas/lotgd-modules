<?php

namespace Lotgd\Local\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Drinks.
 *
 * @ORM\Table
 * @ORM\Entity
 */
class ModuleDrinks
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
     * @ORM\Column(name="name", type="string", length=25, nullable=false)
     */
    private $name = '';

    /**
     * @var bool
     *
     * @ORM\Column(name="active", type="boolean", nullable=false)
     */
    private $active = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="costperlevel", type="integer", nullable=false, options={"unsigned": true})
     */
    private $costperlevel = 0;

    /**
     * @var bool
     *
     * @ORM\Column(name="hpchance", type="boolean", nullable=false)
     */
    private $hpchance = 0;

    /**
     * @var bool
     *
     * @ORM\Column(name="turnchance", type="boolean", nullable=false)
     */
    private $turnchance = 0;

    /**
     * @var bool
     *
     * @ORM\Column(name="alwayshp", type="boolean", nullable=false)
     */
    private $alwayshp = 0;

    /**
     * @var bool
     *
     * @ORM\Column(name="alwaysturn", type="boolean", nullable=false)
     */
    private $alwaysturn = 0;

    /**
     * @var bool
     *
     * @ORM\Column(name="drunkeness", type="boolean", nullable=false)
     */
    private $drunkeness = 0;

    /**
     * @var bool
     *
     * @ORM\Column(name="harddrink", type="boolean", nullable=false)
     */
    private $harddrink = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="hpmin", type="integer", nullable=false)
     */
    private $hpmin = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="hpmax", type="integer", nullable=false)
     */
    private $hpmax = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="hppercent", type="integer", nullable=false)
     */
    private $hppercent = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="turnmin", type="integer", nullable=false)
     */
    private $turnmin = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="turnmax", type="integer", nullable=false)
     */
    private $turnmax = 0;

    /**
     * @var string
     *
     * @ORM\Column(name="buffname", type="string", length=50, nullable=false)
     */
    private $buffname = '';

    /**
     * @var bool
     *
     * @ORM\Column(name="buffrounds", type="smallint", nullable=false)
     */
    private $buffrounds = 0;

    /**
     * @var string
     *
     * @ORM\Column(name="buffroundmsg", type="string", length=75, nullable=false)
     */
    private $buffroundmsg = '';

    /**
     * @var string
     *
     * @ORM\Column(name="buffwearoff", type="string", length=75, nullable=false)
     */
    private $buffwearoff = '';

    /**
     * @var string
     *
     * @ORM\Column(name="buffatkmod",type="decimal", precision=4, scale=2, nullable=true, options={"unsigned": true})
     */
    private $buffatkmod;

    /**
     * @var string
     *
     * @ORM\Column(name="buffdefmod",type="decimal", precision=4, scale=2, nullable=true, options={"unsigned": true})
     */
    private $buffdefmod;

    /**
     * @var string
     *
     * @ORM\Column(name="buffdmgmod",type="decimal", precision=4, scale=2, nullable=true, options={"unsigned": true})
     */
    private $buffdmgmod;

    /**
     * @var string
     *
     * @ORM\Column(name="buffdmgshield",type="decimal", precision=4, scale=2, nullable=true, options={"unsigned": true})
     */
    private $buffdmgshield;

    /**
     * @var string
     *
     * @ORM\Column(name="buffeffectfailmsg", type="string", length=255, nullable=false)
     */
    private $buffeffectfailmsg = '';

    /**
     * @var string
     *
     * @ORM\Column(name="buffeffectnodmgmsg", type="string", length=255, nullable=false)
     */
    private $buffeffectnodmgmsg = '';

    /**
     * @var string
     *
     * @ORM\Column(name="buffeffectmsg", type="string", length=255, nullable=false)
     */
    private $buffeffectmsg = '';

    /**
     * Set the value of id.
     *
     * @param int id
     *
     * @return self
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of id.
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Set the value of Name.
     *
     * @param string name
     *
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the value of Name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set the value of Active.
     *
     * @param bool active
     *
     * @return self
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get the value of Active.
     *
     * @return bool
     */
    public function getActive(): bool
    {
        return $this->active;
    }

    /**
     * Set the value of Costperlevel.
     *
     * @param int costperlevel
     *
     * @return self
     */
    public function setCostperlevel($costperlevel)
    {
        $this->costperlevel = $costperlevel;

        return $this;
    }

    /**
     * Get the value of Costperlevel.
     *
     * @return int
     */
    public function getCostperlevel(): int
    {
        return $this->costperlevel;
    }

    /**
     * Set the value of Hpchance.
     *
     * @param bool hpchance
     *
     * @return self
     */
    public function setHpchance($hpchance)
    {
        $this->hpchance = $hpchance;

        return $this;
    }

    /**
     * Get the value of Hpchance.
     *
     * @return bool
     */
    public function getHpchance(): bool
    {
        return $this->hpchance;
    }

    /**
     * Set the value of Turnchance.
     *
     * @param bool turnchance
     *
     * @return self
     */
    public function setTurnchance($turnchance)
    {
        $this->turnchance = $turnchance;

        return $this;
    }

    /**
     * Get the value of Turnchance.
     *
     * @return bool
     */
    public function getTurnchance(): bool
    {
        return $this->turnchance;
    }

    /**
     * Set the value of Alwayshp.
     *
     * @param bool alwayshp
     *
     * @return self
     */
    public function setAlwayshp($alwayshp)
    {
        $this->alwayshp = $alwayshp;

        return $this;
    }

    /**
     * Get the value of Alwayshp.
     *
     * @return bool
     */
    public function getAlwayshp(): bool
    {
        return $this->alwayshp;
    }

    /**
     * Set the value of Alwaysturn.
     *
     * @param bool alwaysturn
     *
     * @return self
     */
    public function setAlwaysturn($alwaysturn)
    {
        $this->alwaysturn = $alwaysturn;

        return $this;
    }

    /**
     * Get the value of Alwaysturn.
     *
     * @return bool
     */
    public function getAlwaysturn(): bool
    {
        return $this->alwaysturn;
    }

    /**
     * Set the value of Drunkeness.
     *
     * @param bool drunkeness
     *
     * @return self
     */
    public function setDrunkeness($drunkeness)
    {
        $this->drunkeness = $drunkeness;

        return $this;
    }

    /**
     * Get the value of Drunkeness.
     *
     * @return bool
     */
    public function getDrunkeness(): bool
    {
        return $this->drunkeness;
    }

    /**
     * Set the value of Harddrink.
     *
     * @param bool harddrink
     *
     * @return self
     */
    public function setHarddrink($harddrink)
    {
        $this->harddrink = $harddrink;

        return $this;
    }

    /**
     * Get the value of Harddrink.
     *
     * @return bool
     */
    public function getHarddrink(): bool
    {
        return $this->harddrink;
    }

    /**
     * Set the value of Hpmin.
     *
     * @param int hpmin
     *
     * @return self
     */
    public function setHpmin($hpmin)
    {
        $this->hpmin = $hpmin;

        return $this;
    }

    /**
     * Get the value of Hpmin.
     *
     * @return int
     */
    public function getHpmin(): int
    {
        return $this->hpmin;
    }

    /**
     * Set the value of Hpmax.
     *
     * @param int hpmax
     *
     * @return self
     */
    public function setHpmax($hpmax)
    {
        $this->hpmax = $hpmax;

        return $this;
    }

    /**
     * Get the value of Hpmax.
     *
     * @return int
     */
    public function getHpmax(): int
    {
        return $this->hpmax;
    }

    /**
     * Set the value of Hppercent.
     *
     * @param int hppercent
     *
     * @return self
     */
    public function setHppercent($hppercent)
    {
        $this->hppercent = $hppercent;

        return $this;
    }

    /**
     * Get the value of Hppercent.
     *
     * @return int
     */
    public function getHppercent(): int
    {
        return $this->hppercent;
    }

    /**
     * Set the value of Turnmin.
     *
     * @param int turnmin
     *
     * @return self
     */
    public function setTurnmin($turnmin)
    {
        $this->turnmin = $turnmin;

        return $this;
    }

    /**
     * Get the value of Turnmin.
     *
     * @return int
     */
    public function getTurnmin(): int
    {
        return $this->turnmin;
    }

    /**
     * Set the value of Turnmax.
     *
     * @param int turnmax
     *
     * @return self
     */
    public function setTurnmax($turnmax)
    {
        $this->turnmax = $turnmax;

        return $this;
    }

    /**
     * Get the value of Turnmax.
     *
     * @return int
     */
    public function getTurnmax(): int
    {
        return $this->turnmax;
    }

    /**
     * Set the value of Buffname.
     *
     * @param string buffname
     *
     * @return self
     */
    public function setBuffname($buffname)
    {
        $this->buffname = $buffname;

        return $this;
    }

    /**
     * Get the value of Buffname.
     *
     * @return string
     */
    public function getBuffname(): string
    {
        return $this->buffname;
    }

    /**
     * Set the value of Buffrounds.
     *
     * @param int buffrounds
     *
     * @return self
     */
    public function setBuffrounds($buffrounds)
    {
        $this->buffrounds = $buffrounds;

        return $this;
    }

    /**
     * Get the value of Buffrounds.
     *
     * @return int
     */
    public function getBuffrounds(): int
    {
        return $this->buffrounds;
    }

    /**
     * Set the value of Buffroundmsg.
     *
     * @param string buffroundmsg
     *
     * @return self
     */
    public function setBuffroundmsg($buffroundmsg)
    {
        $this->buffroundmsg = $buffroundmsg;

        return $this;
    }

    /**
     * Get the value of Buffroundmsg.
     *
     * @return string
     */
    public function getBuffroundmsg(): string
    {
        return $this->buffroundmsg;
    }

    /**
     * Set the value of Buffwearoff.
     *
     * @param string buffwearoff
     *
     * @return self
     */
    public function setBuffwearoff($buffwearoff)
    {
        $this->buffwearoff = $buffwearoff;

        return $this;
    }

    /**
     * Get the value of Buffwearoff.
     *
     * @return string
     */
    public function getBuffwearoff(): string
    {
        return $this->buffwearoff;
    }

    /**
     * Set the value of Buffatkmod.
     *
     * @param string buffatkmod
     *
     * @return self
     */
    public function setBuffatkmod($buffatkmod)
    {
        $this->buffatkmod = $buffatkmod;

        return $this;
    }

    /**
     * Get the value of Buffatkmod.
     *
     * @return string
     */
    public function getBuffatkmod(): ?string
    {
        return $this->buffatkmod;
    }

    /**
     * Set the value of Buffdefmod.
     *
     * @param string buffdefmod
     *
     * @return self
     */
    public function setBuffdefmod($buffdefmod)
    {
        $this->buffdefmod = $buffdefmod;

        return $this;
    }

    /**
     * Get the value of Buffdefmod.
     *
     * @return string
     */
    public function getBuffdefmod(): ?string
    {
        return $this->buffdefmod;
    }

    /**
     * Set the value of Buffdmgmod.
     *
     * @param string buffdmgmod
     *
     * @return self
     */
    public function setBuffdmgmod($buffdmgmod)
    {
        $this->buffdmgmod = $buffdmgmod;

        return $this;
    }

    /**
     * Get the value of Buffdmgmod.
     *
     * @return string
     */
    public function getBuffdmgmod(): ?string
    {
        return $this->buffdmgmod;
    }

    /**
     * Set the value of Buffdmgshield.
     *
     * @param string buffdmgshield
     *
     * @return self
     */
    public function setBuffdmgshield($buffdmgshield)
    {
        $this->buffdmgshield = $buffdmgshield;

        return $this;
    }

    /**
     * Get the value of Buffdmgshield.
     *
     * @return string
     */
    public function getBuffdmgshield(): ?string
    {
        return $this->buffdmgshield;
    }

    /**
     * Set the value of Buffeffectfailmsg.
     *
     * @param string buffeffectfailmsg
     *
     * @return self
     */
    public function setBuffeffectfailmsg($buffeffectfailmsg)
    {
        $this->buffeffectfailmsg = $buffeffectfailmsg;

        return $this;
    }

    /**
     * Get the value of Buffeffectfailmsg.
     *
     * @return string
     */
    public function getBuffeffectfailmsg(): string
    {
        return $this->buffeffectfailmsg;
    }

    /**
     * Set the value of Buffeffectnodmgmsg.
     *
     * @param string buffeffectnodmgmsg
     *
     * @return self
     */
    public function setBuffeffectnodmgmsg($buffeffectnodmgmsg)
    {
        $this->buffeffectnodmgmsg = $buffeffectnodmgmsg;

        return $this;
    }

    /**
     * Get the value of Buffeffectnodmgmsg.
     *
     * @return string
     */
    public function getBuffeffectnodmgmsg(): string
    {
        return $this->buffeffectnodmgmsg;
    }

    /**
     * Set the value of Buffeffectmsg.
     *
     * @param string buffeffectmsg
     *
     * @return self
     */
    public function setBuffeffectmsg($buffeffectmsg)
    {
        $this->buffeffectmsg = $buffeffectmsg;

        return $this;
    }

    /**
     * Get the value of Buffeffectmsg.
     *
     * @return string
     */
    public function getBuffeffectmsg(): string
    {
        return $this->buffeffectmsg;
    }
}
