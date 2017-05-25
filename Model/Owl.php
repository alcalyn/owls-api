<?php

namespace Alcalyn\Owls\Model;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

class Owl
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var Party
     */
    private $party;

    /**
     * @var int
     */
    private $color;

    /**
     * @var Collection|Bet[]
     */
    private $bets;

    /**
     * @var DeckCard|null
     */
    private $deckCard;

    /**
     * @var bool
     */
    private $alive;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->bets = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Party
     */
    public function getParty()
    {
        return $this->party;
    }

    /**
     * @param Party $party
     *
     * @return self
     */
    public function setParty(Party $party)
    {
        $this->party = $party;

        return $this;
    }

    /**
     * @return int
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * @param int $color
     *
     * @return self
     */
    public function setColor($color)
    {
        $this->color = $color;

        return $this;
    }

    /**
     * @return Collection|Bet[]
     */
    public function getBets()
    {
        return $this->bets;
    }

    /**
     * @param Bet[] $bets
     *
     * @return Owl
     */
    public function setBets(array $bets)
    {
        $this->bets = new ArrayCollection($bets);

        return $this;
    }

    /**
     * @param Bet $bet
     *
     * @return self
     */
    public function addBet(Bet $bet)
    {
        $this->bets->add($bet);

        return $this;
    }

    /**
     * @return self
     */
    public function clearBets()
    {
        $this->bets->clear();

        return $this;
    }

    /**
     * @return DeckCard|null
     */
    public function getDeckCard()
    {
        return $this->deckCard;
    }

    /**
     * @return bool
     */
    public function hasDeckCard()
    {
        return null !== $this->deckCard;
    }

    /**
     * @param DeckCard|null $deckCard
     *
     * @return self
     */
    public function setDeckCard(DeckCard $deckCard = null)
    {
        $this->deckCard = $deckCard;

        return $this;
    }

    /**
     * @return bool
     */
    public function isAlive()
    {
        return $this->alive;
    }

    /**
     * @param bool $alive
     *
     * @return self
     */
    public function setAlive($alive)
    {
        $this->alive = $alive;

        return $this;
    }
}
