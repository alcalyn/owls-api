<?php

namespace Alcalyn\Owls\Event;

use Alcalyn\Owls\Model\Party;
use Alcalyn\Owls\Model\Bet;

class PlayBetEvent extends Event
{
    /**
     * @var Bet
     */
    private $bet;

    /**
     * @param Party $party
     * @param Bet $bet
     */
    public function __construct(Party $party, Bet $bet)
    {
        parent::__construct($party);

        $this->bet = $bet;
    }
    /**
     * @return Party
     */
    public function getBet()
    {
        return $this->bet;
    }

    /**
     * @param Bet $bet
     *
     * @return self
     */
    public function setBet(Bet $bet)
    {
        $this->bet = $bet;

        return $this;
    }
}
