<?php

namespace Alcalyn\Owls\Model;

use Eole\Core\Model\Player as EolePlayer;

class Player
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
     * @var EolePlayer
     */
    private $eolePlayer;

    /**
     * @var Deck
     */
    private $deck;

    /**
     * @var int
     */
    private $order;

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
     * @return EolePlayer
     */
    public function getEolePlayer()
    {
        return $this->eolePlayer;
    }

    /**
     * @param EolePlayer $eolePlayer
     *
     * @return self
     */
    public function setEolePlayer(EolePlayer $eolePlayer)
    {
        $this->eolePlayer = $eolePlayer;

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
     * @return int
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param int $order
     *
     * @return self
     */
    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
    }
}
