<?php

namespace Alcalyn\Owls\Model;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Eole\Core\Model\Party as EoleParty;

class Party
{
    /**
     * @var bool
     */
    const PHASE_BET = false;

    /**
     * @var bool
     */
    const PHASE_CARD = true;

    /**
     * @var int
     */
    private $id;

    /**
     * @var EoleParty
     */
    private $eoleParty;

    /**
     * @var Deck
     */
    private $deck;

    /**
     * @var Collection|Owl[]
     */
    private $owls;

    /**
     * @var Collection|Player[]
     */
    private $players;

    /**
     * @var int
     */
    private $playerTurn;

    /**
     * @var bool
     */
    private $turnPhase;

    /**
     * @var int
     */
    private $version;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this
            ->setOwls(array())
            ->setPlayers(array())
            ->setPlayerTurn(rand(0, 2))
            ->setTurnPhase(self::PHASE_BET)
        ;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return EoleParty
     */
    public function getEoleParty()
    {
        return $this->eoleParty;
    }

    /**
     * @param EoleParty $eoleParty
     *
     * @return self
     */
    public function setEoleParty(EoleParty $eoleParty)
    {
        $this->eoleParty = $eoleParty;

        return $this;
    }

    /**
     * @return Deck
     */
    public function getDeck()
    {
        return $this->deck;
    }

    /**
     * @param Deck $deck
     *
     * @return self
     */
    public function setDeck(Deck $deck)
    {
        $this->deck = $deck;

        return $this;
    }

    /**
     * @return Collection|Owl[]
     */
    public function getOwls()
    {
        return $this->owls;
    }

    /**
     * @param Collection|Owl[] $owls
     *
     * @return self
     */
    public function setOwls(array $owls)
    {
        $this->owls = new ArrayCollection($owls);

        return $this;
    }

    /**
     * @return Collection|Player[]
     */
    public function getPlayers()
    {
        return $this->players;
    }

    /**
     * @param Player[] $players
     *
     * @return self
     */
    public function setPlayers(array $players)
    {
        $this->players = new ArrayCollection($players);

        return $this;
    }

    /**
     * @param Player $player
     *
     * @return self
     */
    public function addPlayer(Player $player)
    {
        $this->players []= $player;

        return $this;
    }

    /**
     * @return int
     */
    public function getPlayerTurn()
    {
        return $this->playerTurn;
    }

    /**
     * @param int $playerTurn
     *
     * @return self
     */
    public function setPlayerTurn($playerTurn)
    {
        $this->playerTurn = $playerTurn;

        return $this;
    }

    /**
     * @return bool
     */
    public function getTurnPhase()
    {
        return $this->turnPhase;
    }

    /**
     * @param bool $turnPhase
     *
     * @return self
     */
    public function setTurnPhase($turnPhase)
    {
        $this->turnPhase = $turnPhase;

        return $this;
    }

    /**
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param int $version
     *
     * @return self
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }
}
