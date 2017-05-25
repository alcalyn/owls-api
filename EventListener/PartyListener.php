<?php

namespace Alcalyn\Owls\EventListener;

use Doctrine\Common\Persistence\ObjectManager;
use Eole\Core\Event\PartyEvent;
use Eole\Core\Service\PartyManager;
use Alcalyn\Owls\Owls;
use Alcalyn\Owls\Model\Party;
use Alcalyn\Owls\Service\Croupier;
use Alcalyn\Owls\Repository\CardRepository;
use Alcalyn\Owls\Repository\PartyRepository;

class PartyListener
{
    /**
     * @var Croupier
     */
    private $croupier;

    /**
     * @var CardRepository
     */
    private $cardRepository;

    /**
     * @var PartyRepository
     */
    private $partyRepository;

    /**
     * @var PartyManager
     */
    private $partyManager;

    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @param Croupier $croupier
     * @param CardRepository $cardRepository
     * @param PartyRepository $partyRepository
     * @param PartyManager $partyManager
     * $param ObjectManager $om
     */
    public function __construct(
        Croupier $croupier,
        CardRepository $cardRepository,
        PartyRepository $partyRepository,
        PartyManager $partyManager,
        ObjectManager $om
    ) {
        $this->croupier = $croupier;
        $this->cardRepository = $cardRepository;
        $this->partyRepository = $partyRepository;
        $this->partyManager = $partyManager;
        $this->om = $om;
    }

    /**
     * @param PartyEvent $event
     */
    public function onPartyCreateBefore(PartyEvent $event)
    {
        $eoleParty = $event->getParty();

        if (Owls::GAME_NAME !== $eoleParty->getGame()->getName()) {
            return;
        }

        $eoleParty
            ->addEmptySlot()
        ;

        $this->partyManager->reorderSlots($eoleParty);

        $cards = $this->cardRepository->findAll();
        $deck = $this->croupier->createShuffledDeck($cards);

        $party = new Party();

        $party
            ->setEoleParty($eoleParty)
            ->setDeck($deck)
        ;

        $this->om->persist($party);

        $owls = $this->croupier->createOwls($party);

        foreach ($owls as $owl) {
            $this->om->persist($owl);
        }
    }

    /**
     * @param PartyEvent $event
     */
    public function onPartyStarted(PartyEvent $event)
    {
        $eoleParty = $event->getParty();

        if (Owls::GAME_NAME !== $eoleParty->getGame()->getName()) {
            return;
        }

        $party = $this->partyRepository->findFullByEolePartyId($eoleParty->getId(), array('party_deck' => true));
        $this->affectPlayerOrder($party);
        $this->croupier->distributeCardsToEach($party, 5);
    }

    /**
     * Set player order from Eole slots orders to keep order synchronized.
     *
     * @param Party $party
     */
    private function affectPlayerOrder(Party $party)
    {
        foreach ($party->getEoleParty()->getSlots() as $slot) {
            foreach ($party->getPlayers() as $player) {
                if ($slot->getPlayer() === $player->getEolePlayer()) {
                    var_dump('affect', $slot->getOrder());
                    $player->setOrder($slot->getOrder());

                    break;
                }
            }
        }
    }
}
