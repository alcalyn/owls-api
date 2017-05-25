<?php

namespace Alcalyn\Owls\Model;

class DeckCard
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var Deck|null
     */
    private $deck;

    /**
     * @var Card
     */
    private $card;

    /**
     * @var int
     */
    private $weight;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Deck|null
     */
    public function getDeck()
    {
        return $this->deck;
    }

    /**
     * @param Deck|null $deck
     *
     * @return self
     */
    public function setDeck(Deck $deck = null)
    {
        $this->deck = $deck;

        return $this;
    }

    /**
     * @return Card
     */
    public function getCard()
    {
        return $this->card;
    }

    /**
     * @param Card $card
     *
     * @return self
     */
    public function setCard(Card $card)
    {
        $this->card = $card;

        return $this;
    }

    /**
     * @return int
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @param int $weight
     *
     * @return self
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;

        return $this;
    }
}
