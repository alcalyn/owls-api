<?php

namespace Alcalyn\Owls\EventListener;

use Doctrine\Common\Persistence\ObjectManager;
use Eole\Core\Event\SlotEvent;
use Eole\Core\Service\PartyManager;
use Alcalyn\Owls\Model\Deck;
use Alcalyn\Owls\Model\Player;
use Alcalyn\Owls\Repository\PartyRepository;
use Alcalyn\Owls\Owls;

class SlotListener
{
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @var PartyRepository
     */
    private $partyRepository;

    /**
     * @var PartyManager
     */
    private $partyManager;

    /**
     * $param ObjectManager $om
     * @param PartyManager $partyManager
     */
    public function __construct(ObjectManager $om, PartyRepository $partyRepository, PartyManager $partyManager)
    {
        $this->om = $om;
        $this->partyRepository = $partyRepository;
        $this->partyManager = $partyManager;
    }

    /**
     * Init Players and Deck, and link to Eole player.
     * Starts the party once full.
     *
     * @param SlotEvent $event
     */
    public function onSlotJoinAfter(SlotEvent $event)
    {
        $eoleParty = $event->getParty();

        if (Owls::GAME_NAME !== $eoleParty->getGame()->getName()) {
            return;
        }

        $eolePlayer = $event->getPlayer();
        $party = $this->partyRepository->findOneByEoleParty($eoleParty);
        $player = new Player();
        $deck = new Deck();

        $player
            ->setParty($party)
            ->setEolePlayer($eolePlayer)
            ->setDeck($deck)
        ;

        $this->om->persist($player);

        $party
            ->addPlayer($player)
        ;

        $this->om->flush();

        $this->startIfPartyFull($event);
    }

    /**
     * @param SlotEvent $event
     */
    private function startIfPartyFull(SlotEvent $event)
    {
        $eoleParty = $event->getParty();

        if ($this->partyManager->isFull($eoleParty)) {
            $this->partyManager->startParty($eoleParty);

            $this->om->flush();
        }
    }
}
