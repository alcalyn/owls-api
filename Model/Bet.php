<?php

namespace Alcalyn\Owls\Model;

class Bet
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var Player
     */
    private $player;

    /**
     * @var Owl
     */
    private $owl;

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
     * @return Player
     */
    public function getPlayer()
    {
        return $this->player;
    }

    /**
     * @param Player $player
     *
     * @return self
     */
    public function setPlayer(Player $player)
    {
        $this->player = $player;

        return $this;
    }

    /**
     * @return Owl
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
