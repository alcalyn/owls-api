<?php

namespace Alcalyn\Owls\Event;

use Symfony\Component\EventDispatcher\Event as BaseEvent;
use Alcalyn\Owls\Model\Party;

class Event extends BaseEvent
{
    /**
     * This event is triggered when a player has bet on a owl.
     *
     * @var string
     */
    const PLAY_BET = 'owls.event.play_bet';

    /**
     * This event is triggered when a player has played a card on a owl.
     *
     * @var string
     */
    const PLAY_CARD = 'owls.event.play_card';

    /**
     * This event is triggered when a owl has been eliminated.
     *
     * @var string
     */
    const MONKEY_ELIMINATED = 'owls.event.owl_eliminated';

    /**
     * @var Party
     */
    private $party;

    /**
     * @param Party $party
     */
    public function __construct(Party $party)
    {
        $this->party = $party;
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
}
