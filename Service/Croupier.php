<?php

namespace Alcalyn\Owls\Service;

use Eole\Core\Model\Party as EoleParty;
use Eole\Core\Service\PartyManager;
use Alcalyn\Owls\Exception\CroupierException;
use Alcalyn\Owls\Model\Card;
use Alcalyn\Owls\Model\Deck;
use Alcalyn\Owls\Model\DeckCard;
use Alcalyn\Owls\Model\Player;
use Alcalyn\Owls\Model\Party;
use Alcalyn\Owls\Model\Bet;
use Alcalyn\Owls\Model\Owl;

class Croupier
{
    /**
     * @var PartyManager
     */
    private $partyManager;

    /**
     * @param PartyManager $partyManager
     */
    public function __construct(PartyManager $partyManager)
    {
        $this->partyManager = $partyManager;
    }

    /**
     * @return Card[]
     */
    public function createCards()
    {
        $cards = array();

        for ($color = 0; $color < 6; $color++) {
            for ($number = 1; $number <= 7; $number++) {
                $card = new Card();

                $card
                    ->setType(Card::TYPE_NORMAL)
                    ->setNumber($number)
                    ->setColor($color)
                ;

                $cards []= $card;
            }
        }

        return $cards;
    }

    /**
     * @param Card[] $cards
     *
     * @return Deck
     */
    public function createShuffledDeck(array $cards)
    {
        $deck = new Deck();
        $deckCards = array();

        foreach ($cards as $card) {
            $deckCard = new DeckCard();

            $deckCard
                ->setCard($card)
                ->setDeck($deck)
            ;

            $deckCards []= $deckCard;
        }

        shuffle($deckCards);

        foreach ($deckCards as $weight => $deckCard) {
            $deckCard->setWeight(54 - $weight);
        }

        $deck->setDeckCards($deckCards);

        return $deck;
    }

    /**
     * @param Deck $deck
     * @param int $cardId
     *
     * @return DeckCard
     *
     * @throws \OutOfBoundsException
     */
    public function getDeckCardByCardId(Deck $deck, $cardId)
    {
        foreach ($deck->getDeckCards() as $deckCard) {
            if ($deckCard->getCard()->getId() === $cardId) {
                return $deckCard;
            }
        }

        throw new CroupierException('This card is not in the deck.');
    }

    /**
     * @param Deck $deck
     *
     * @return DeckCard
     *
     * @throws CroupierException When trying to pop an empty Deck.
     */
    public function popDeckCard(Deck $deck)
    {
        $deckCards = $deck->getDeckCards();

        if ($deckCards->isEmpty()) {
            throw new CroupierException('Cannot pop deck card, deck is empty.');
        }

        $deckCard = $deckCards->last();
        $deckCards->removeElement($deckCard);
        $deckCard->setDeck(null);

        return $deckCard;
    }

    /**
     * @param Deck $deck
     * @param DeckCard $deckCard
     *
     * @return self
     */
    public function pushDeckCard(Deck $deck, DeckCard $deckCard)
    {
        $deck->addDeckCard($deckCard);

        $deckCard->setDeck($deck);

        return $this;
    }

    /**
     * @param Deck $from
     * @param Deck $to
     *
     * @return DeckCard Distributed DeckCard instance.
     *
     * @throws CroupierException When trying to distribute from an empty deck.
     */
    public function distribute(Deck $from, Deck $to)
    {
        if ($from->isEmpty()) {
            throw new CroupierException('Cannot distribute from an empty deck.');
        }

        $deckCard = $this->popDeckCard($from);

        $this->pushDeckCard($to, $deckCard);

        return $deckCard;
    }

    /**
     * @param Party $party
     * @param int $number of cards to distribute to each.
     */
    public function distributeCardsToEach(Party $party, $number)
    {
        $partyDeck = $party->getDeck();
        $playersDeck = array();

        foreach ($party->getPlayers() as $player) {
            $playersDeck []= $player->getDeck();
        }

        for ($i = 0; $i < $number; $i++) {
            foreach ($playersDeck as $playerDeck) {
                $this->distribute($partyDeck, $playerDeck);
            }
        }
    }

    /**
     * Distribute to all players until players reached their $max amount of cards.
     *
     * @param Party $party
     * @param int $max Maximum amount of cards a player should have.
     */
    public function distributeCardsToEachMax(Party $party, $max)
    {
        $partyDeck = $party->getDeck();

        do {
            $stillDistributing = false;

            foreach ($party->getPlayers() as $player) {
                if ($party->getDeck()->hasCards() && ($player->getDeck()->getSize() < $max)) {
                    $this->distribute($partyDeck, $player->getDeck());
                    $stillDistributing = true;
                }
            }
        } while ($stillDistributing);
    }

    /**
     * Create 6 new owls for $party.
     *
     * @param Party $party
     *
     * @return Owl[]
     */
    public function createOwls(Party $party)
    {
        $owls = array();

        for ($i = 0; $i < 6; $i++) {
            $owl = new Owl();

            $owl
                ->setParty($party)
                ->setColor($i)
                ->setAlive(true)
                ->setDeckCard(null)
            ;

            $owls []= $owl;
        }

        return $owls;
    }

    /**
     * @param Party $party
     * @param int $owlColor
     *
     * @return Owl
     *
     * @throws CroupierException If Owl does not exists.
     */
    public function getOwlByColor(Party $party, $owlColor)
    {
        if (!is_int($owlColor)) {
            throw new \UnexpectedValueException('Expected $owlColor integer, '.gettype($owlColor).' given.');
        }

        if (!isset($party->getOwls()[$owlColor])) {
            throw new CroupierException('This owl does not exists.');
        }

        $owl = $party->getOwls()[$owlColor];

        if ($owl->getColor() === $owlColor) {
            return $owl;
        }

        foreach ($party->getOwls() as $owl) {
            if ($owl->getColor() === $owlColor) {
                return $owl;
            }
        }

        throw new CroupierException('This owl does not exists.');
    }

    /**
     * @param Player $player
     * @param Owl $owl
     *
     * @return Bet
     *
     * @throws CroupierException when impossible to bet on this owl.
     */
    public function placeBet(Player $player, Owl $owl)
    {
        if (!$owl->isAlive()) {
            throw new CroupierException('This owl has been eliminated.');
        }

        $bets = $owl->getBets();
        $betsCount = count($bets);

        if ($betsCount >= 4) {
            throw new CroupierException('There is already 4 bets.');
        }

        $bet = new Bet();

        $bet
            ->setPlayer($player)
            ->setOwl($owl)
            ->setOrder($betsCount)
        ;

        $owl->addBet($bet);

        return $bet;
    }

    /**
     * Place a DeckCard on a Owl.
     *
     * @param DeckCard $deckCard
     *
     * @param Owl $owl
     */
    public function placeCard(DeckCard $deckCard, Owl $owl)
    {
        if (!$owl->isAlive()) {
            throw new CroupierException('This owl has been eliminated.');
        }

        if (null !== $deckCard->getDeck()) {
            $deckCard->getDeck()->removeDeckCard($deckCard);
            $deckCard->setDeck(null);
        }

        $owl->setDeckCard($deckCard);
    }

    /**
     * Update players score on Eole by counting theirs bets on owls.
     *
     * @param Party $party
     */
    public function updateScores(Party $party)
    {
        $slots = new \SplObjectStorage();

        foreach ($party->getEoleParty()->getSlots() as $slot) {
            $slot->setScore(0);

            $slots[$slot->getPlayer()] = $slot;
        }

        foreach ($party->getOwls() as $owl) {
            foreach ($owl->getBets() as $bet) {
                $slots[$bet->getPlayer()->getEolePlayer()]->incrementScore();
            }
        }
    }

    /**
     * @param Party $party
     *
     * @return Owl|null Eliminated owl, or null.
     */
    public function checkOwlElimination(Party $party)
    {
        $owls = [];

        foreach ($party->getOwls() as $owl) {
            if (!$owl->isAlive()) {
                continue;
            }

            if (!$owl->hasDeckCard()) {
                return null;
            }

            if (0 === count($owls)) {
                $owls []= $owl;
                continue;
            }

            $card = $owl->getDeckCard()->getCard();
            $minCard = $owls[0]->getDeckCard()->getCard();

            if ($minCard->getNumber() === $card->getNumber()) {
                $owls []= $owl;
            } elseif ($minCard->getNumber() > $card->getNumber()) {
                $owls = [$owl];
            }
        }

        if (1 === count($owls)) {
            return $owls[0];
        }

        return null;
    }

    /**
     * Eliminate a owl and its bets, remove all cards of the owl color
     * from party deck and players hands, and redistribute cards to players.
     *
     * @param Party $party
     *
     * @return Owl|null
     */
    public function eliminateOwl(Party $party)
    {
        $eliminatedOwl = $this->checkOwlElimination($party);

        if (null === $eliminatedOwl) {
            return null;
        }

        $eliminatedOwl
            ->setAlive(false)
            ->clearBets()
        ;

        foreach ($party->getOwls() as $owl) {
            $owl->setDeckCard(null);
        }

        $this->removeCardsWithColor($party->getDeck(), $eliminatedOwl->getColor());

        foreach ($party->getPlayers() as $player) {
            $this->removeCardsWithColor($player->getDeck(), $eliminatedOwl->getColor());
            $this->distributeCardsToEachMax($party, 5);
        }

        return $eliminatedOwl;
    }

    /**
     * @param Deck $deck
     * @param int $color
     *
     * @return DeckCard[] Removed deck cards.
     */
    public function removeCardsWithColor(Deck $deck, $color)
    {
        $removedDeckCards = array();

        foreach ($deck->getDeckCards() as $deckCard) {
            if ($color === $deckCard->getCard()->getColor()) {
                $deck->removeDeckCard($deckCard);
                $deckCard->setDeck(null);

                $removedDeckCards []= $deckCard;
            }
        }

        return $removedDeckCards;
    }

    /**
     * @param Party $party
     *
     * @throws CroupierException
     */
    public function checkPartyIsActive(Party $party)
    {
        if ($party->getEoleParty()->getState() !== EoleParty::ACTIVE) {
            throw new CroupierException('Party is not active.');
        }
    }

    /**
     * @param Party $party
     * @param Player $player
     * @param bool $phase
     *
     * @throws CroupierException When turn to play or turn phase not respected.
     */
    public function checkTurnPhase(Party $party, Player $player, $phase)
    {
        $partyPlayerTurn = $party->getPlayers()[$party->getPlayerTurn()];
        $partyTurnPhase = $party->getTurnPhase();

        if ($player !== $partyPlayerTurn) {
            throw new CroupierException('Not your turn to play.');
        }

        if ($phase !== $partyTurnPhase) {
            if (Party::PHASE_BET === $partyTurnPhase) {
                throw new CroupierException('Please bet before play.');
            } else {
                throw new CroupierException('You have already bet.');
            }
        }
    }

    /**
     * Returns whether there is free slot to bet on owls.
     *
     * @param Party $party
     *
     * @return bool
     */
    public function canStillBet(Party $party)
    {
        foreach ($party->getOwls() as $owl) {
            if (!$owl->isAlive()) {
                continue;
            }

            if ($owl->getBets()->count() < 4) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Party $party
     *
     * @throws CroupierException When trying to change turn when current player still needs to bet.
     */
    public function nextTurn(Party $party)
    {
        if ($party->getTurnPhase() !== Party::PHASE_CARD) {
            throw new CroupierException('Impossible to change turn when current player has not yet bet.');
        }

        $party->setPlayerTurn(($party->getPlayerTurn() + 1) % 3);

        if ($this->canStillBet($party)) {
            $party->setTurnPhase(Party::PHASE_BET);
        }
    }

    /**
     * @param Party $party
     *
     * @throws CroupierException When trying to change phase when current player still needs to play card.
     */
    public function nextPhase(Party $party)
    {
        if ($party->getTurnPhase() !== Party::PHASE_BET) {
            throw new CroupierException('Impossible to change phase when current player has already bet.');
        }

        $party
            ->setTurnPhase(Party::PHASE_CARD)
        ;
    }

    /**
     * Returns number of still remaining owls in the party.
     *
     * @param Party $party
     *
     * @return int
     */
    public function remainingOwls(Party $party)
    {
        return $party->getOwls()->filter(function (Owl $owl) {
            return $owl->isAlive();
        })->count();
    }

    /**
     * @param Party $party
     *
     * @return bool
     */
    public function isPartyOver(Party $party)
    {
        return $this->remainingOwls($party) <= 3;
    }

    /**
     * @param Party $party
     */
    public function stopIfPartyOver(Party $party)
    {
        if ($this->isPartyOver($party)) {
            $this->partyManager->endParty($party->getEoleParty());
        }
    }
}
