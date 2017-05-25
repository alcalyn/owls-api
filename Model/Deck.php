<?php

namespace Alcalyn\Owls\Model;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

class Deck
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var Collection|DeckCard[]
     */
    private $deckCards;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->deckCards = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Collection|DeckCard[]
     */
    public function getDeckCards()
    {
        if (null === $this->deckCards) {
            $this->deckCards = new ArrayCollection();
        }

        return $this->deckCards;
    }

    /**
     * @param DeckCard[] $deckCards
     *
     * @return self
     */
    public function setDeckCards(array $deckCards)
    {
        $this->deckCards = new ArrayCollection($deckCards);

        return $this;
    }

    /**
     * @param DeckCard $deckCard
     *
     * @return self
     */
    public function addDeckCard(DeckCard $deckCard)
    {
        $this->deckCards->add($deckCard);

        return $this;
    }

    /**
     * @param DeckCard $deckCard
     *
     * @return self
     *
     * @throws \OutOfBoundsException when DeckCard to remove is not in this Deck.
     */
    public function removeDeckCard(DeckCard $deckCard)
    {
        if (!$this->deckCards->contains($deckCard)) {
            throw new \OutOfBoundsException('The DeckCard is not in this Deck.');
        }

        $this->deckCards->removeElement($deckCard);

        return $this;
    }

    /**
     * @return Collection|Card[]
     */
    public function getCards()
    {
        return $this->getDeckCards()->map(function (DeckCard $deckCard) {
            return $deckCard->getCard();
        });
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return $this->getDeckCards()->count();
    }

    /**
     * @return bool
     */
    public function hasCards()
    {
        return !$this->getDeckCards()->isEmpty();
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return $this->getDeckCards()->isEmpty();
    }
}
