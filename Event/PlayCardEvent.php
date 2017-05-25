<?php

namespace Alcalyn\Owls\Event;

use Alcalyn\Owls\Model\DeckCard;
use Alcalyn\Owls\Model\Party;
use Alcalyn\Owls\Model\Owl;

class PlayCardEvent extends Event
{
    /**
     * @var Owl
     */
    private $owl;

    /**
     * @var DeckCard
     */
    private $deckCard;

    /**
     * @var bool
     */
    private $hasElimination;

    /**
     * @param Party $party
     * @param Owl $owl
     * @param DeckCard $deckCard
     * @param bool $hasElimination
     */
    public function __construct(Party $party, Owl $owl, DeckCard $deckCard, $hasElimination = false)
    {
        parent::__construct($party);

        $this->owl = $owl;
        $this->deckCard = $deckCard;
        $this->hasElimination = $hasElimination;
    }

    /**
     * @return Party
     */
    public function getOwl()
    {
        return $this->owl;
    }

    /**
     * @param Owl $owl
     *
     * @return self
     */
    public function setOwl(Owl $owl)
    {
        $this->owl = $owl;

        return $this;
    }

    /**
     * @return Party
     */
    public function getDeckCard()
    {
        return $this->deckCard;
    }

    /**
     * @param DeckCard $deckCard
     *
     * @return self
     */
    public function setDeckCard(DeckCard $deckCard)
    {
        $this->deckCard = $deckCard;

        return $this;
    }

    /**
     * @return Party
     */
    public function getHasElimination()
    {
        return $this->hasElimination;
    }

    /**
     * @param bool $hasElimination
     *
     * @return self
     */
    public function setHasElimination($hasElimination)
    {
        $this->hasElimination = $hasElimination;

        return $this;
    }
}
