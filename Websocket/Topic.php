<?php

namespace Alcalyn\Owls\Websocket;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Eole\Sandstone\Websocket\Topic as BaseTopic;
use Alcalyn\Owls\Event\Event;
use Alcalyn\Owls\Event\PlayBetEvent;
use Alcalyn\Owls\Event\PlayCardEvent;
use Alcalyn\Owls\Event\OwlEliminatedEvent;

class Topic extends BaseTopic implements EventSubscriberInterface
{
    /**
     * Constructor.
     *
     * @param string $topicPath
     * @param int $partyId
     */
    public function __construct($topicPath, $partyId)
    {
        parent::__construct($topicPath);
    }

    /**
     * @param PlayBetEvent $event
     */
    public function onPlayBet(PlayBetEvent $event)
    {
        $this->broadcast(array(
            'type' => 'play_bet',
            'party' => $event->getParty(),
            'bet' => $event->getBet(),
        ));
    }

    /**
     * @param PlayCardEvent $event
     */
    public function onPlayCard(PlayCardEvent $event)
    {
        $this->broadcast(array(
            'type' => 'play_card',
            'party' => $event->getParty(),
            'owl' => $event->getOwl(),
            'deck_card' => $event->getDeckCard(),
            'has_elimination' => $event->getHasElimination(),
        ));
    }

    /**
     * @param OwlEliminatedEvent $event
     */
    public function onOwlEliminated(OwlEliminatedEvent $event)
    {
        $this->broadcast(array(
            'type' => 'owl_eliminated',
            'party' => $event->getParty(),
            'owl' => $event->getOwl(),
        ));
    }

    /**
     * {@InheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            Event::PLAY_BET => array(
                array('onPlayBet'),
            ),
            Event::PLAY_CARD => array(
                array('onPlayCard'),
            ),
            Event::MONKEY_ELIMINATED => array(
                array('onOwlEliminated'),
            ),
        );
    }
}
