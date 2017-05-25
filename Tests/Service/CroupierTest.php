<?php

namespace Alcalyn\Owls\Tests\Service;

use Alcalyn\Owls\Model\Card;
use Alcalyn\Owls\Model\Deck;
use Alcalyn\Owls\Model\DeckCard;
use Alcalyn\Owls\Model\Player;
use Alcalyn\Owls\Model\Owl;
use Alcalyn\Owls\Model\Party;
use Alcalyn\Owls\Service\Croupier;

class CroupierTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Croupier
     */
    private $croupier;

    /**
     * @var Card[]
     */
    private $sampleCards;

    public function __construct()
    {
        parent::__construct();

        $this->croupier = new Croupier();
        $this->sampleCards = array();

        for ($i = 0; $i < 7; $i++) {
            $this->sampleCards []= (new Card())->setNumber($i + 1);
        }
    }

    /**
     * @return DeckCard[]
     */
    private function generateFreshDeckCards()
    {
        $deckCards = array();
        $i = 0;

        foreach ($this->sampleCards as $card) {
            $deckCard = new DeckCard();

            $deckCard
                ->setCard($card)
                ->setWeight($i++)
            ;

            $deckCards []= $deckCard;
        }

        return $deckCards;
    }

    /**
     * @return Party
     */
    private static function generatePartyWithOwls()
    {
        $party = new Party();
        $owls = array();

        for ($i = 0; $i < 6; $i++) {
            $owl = new Owl();

            $owl
                ->setAlive(true)
                ->setColor($i)
                ->setParty($party)
            ;

            $owls []= $owl;
        }

        $party->setOwls($owls);

        return $party;
    }

    public function testCreateShuffledDeckReturnsExpectedStructure()
    {
        $deck = $this->croupier->createShuffledDeck($this->sampleCards);

        $this->assertInstanceOf(Deck::class, $deck);
        $this->assertCount(7, $deck->getDeckCards());
        $this->assertContainsOnlyInstancesOf(DeckCard::class, $deck->getDeckCards());
        $this->assertContainsOnlyInstancesOf(Card::class, $deck->getCards());
    }

    public function testCreateShuffledDeckIsSortedByWeight()
    {
        $deck = $this->croupier->createShuffledDeck($this->sampleCards);

        for ($i = 1; $i < 7; $i++) {
            $weight0 = $deck->getDeckCards()[$i - 1]->getWeight();
            $weight1 = $deck->getDeckCards()[$i]->getWeight();

            $this->assertTrue($weight0 > $weight1, 'The lightest cards are at the end.');
        }
    }

    public function testDistribute()
    {
        $deck0 = new Deck();
        $deck1 = new Deck();

        $deckCards = $this->generateFreshDeckCards();

        $this->croupier->pushDeckCard($deck0, $deckCards[0]);
        $this->croupier->pushDeckCard($deck0, $deckCards[1]);
        $this->croupier->pushDeckCard($deck0, $deckCards[2]);

        $this->croupier->pushDeckCard($deck1, $deckCards[3]);
        $this->croupier->pushDeckCard($deck1, $deckCards[4]);

        $this->assertEquals(3, $deck0->getSize());
        $this->assertEquals(2, $deck1->getSize());
        $this->assertSame($this->sampleCards[2], $deck0->getCards()[2]);

        $this->croupier->distribute($deck0, $deck1);

        $this->assertEquals(2, $deck0->getSize());
        $this->assertEquals(3, $deck1->getSize());
        $this->assertSame($this->sampleCards[2], $deck1->getCards()[2]);
    }

    public function testDistributeFiveCardsToEach()
    {
        $party = new Party();
        $player0 = new Player();
        $player1 = new Player();
        $player2 = new Player();

        $party
            ->addPlayer($player0)
            ->addPlayer($player1)
            ->addPlayer($player2)
        ;

        $party->setDeck(new Deck());
        $deckCards = $this->generateFreshDeckCards();

        foreach ($deckCards as $deckCard) {
            $this->croupier->pushDeckCard($party->getDeck(), $deckCard);
        }

        $player0->setParty($party)->setDeck(new Deck());
        $player1->setParty($party)->setDeck(new Deck());
        $player2->setParty($party)->setDeck(new Deck());

        $this->assertEquals(7, $party->getDeck()->getSize());
        $this->assertEquals(0, $player0->getDeck()->getSize());
        $this->assertEquals(0, $player1->getDeck()->getSize());
        $this->assertEquals(0, $player2->getDeck()->getSize());

        $this->croupier->distributeCardsToEach($party, 2);

        $this->assertEquals(1, $party->getDeck()->getSize());
        $this->assertEquals(2, $player0->getDeck()->getSize());
        $this->assertEquals(2, $player1->getDeck()->getSize());
        $this->assertEquals(2, $player2->getDeck()->getSize());

        $this->assertSame($this->sampleCards[5], $player1->getDeck()->getCards()[0]);
        $this->assertSame($this->sampleCards[2], $player1->getDeck()->getCards()[1]);
        $this->assertSame($this->sampleCards[6], $player0->getDeck()->getCards()[0]);
        $this->assertSame($this->sampleCards[3], $player0->getDeck()->getCards()[1]);
        $this->assertSame($this->sampleCards[0], $party->getDeck()->getCards()[0]);
    }

    public function testCheckOwlEliminationReturnsNullWhenAOwlHasNoCard()
    {
        $party = self::generatePartyWithOwls();
        $deckCards = $this->generateFreshDeckCards();

        foreach ($party->getOwls() as $owl) {
            $this->croupier->placeCard(array_pop($deckCards), $owl);
        }

        $party->getOwls()[2]->setDeckCard(null);

        $this->assertNull($this->croupier->checkOwlElimination($party));
    }

    public function testCheckOwlEliminationReturnsMinOwl()
    {
        $party = self::generatePartyWithOwls();
        $deckCards = $this->generateFreshDeckCards();

        $minOwl = $party->getOwls()[3];

        $this->croupier->placeCard($deckCards[2], $party->getOwls()[0]);
        $this->croupier->placeCard($deckCards[1], $party->getOwls()[1]);
        $this->croupier->placeCard($deckCards[4], $party->getOwls()[2]);
        $this->croupier->placeCard($deckCards[0], $party->getOwls()[3]);
        $this->croupier->placeCard($deckCards[5], $party->getOwls()[4]);
        $this->croupier->placeCard($deckCards[3], $party->getOwls()[5]);

        $this->assertSame($minOwl, $this->croupier->checkOwlElimination($party));
    }

    public function testCheckOwlEliminationReturnsNullOnEquality()
    {
        $party = self::generatePartyWithOwls();
        $deckCards = $this->generateFreshDeckCards();

        $deckCards[1]->getCard()->setNumber(1);

        $this->croupier->placeCard($deckCards[2], $party->getOwls()[0]);
        $this->croupier->placeCard($deckCards[1], $party->getOwls()[1]);
        $this->croupier->placeCard($deckCards[4], $party->getOwls()[2]);
        $this->croupier->placeCard($deckCards[0], $party->getOwls()[3]);
        $this->croupier->placeCard($deckCards[5], $party->getOwls()[4]);
        $this->croupier->placeCard($deckCards[3], $party->getOwls()[5]);

        $this->assertNull($this->croupier->checkOwlElimination($party));
    }

    public function testCheckOwlEliminationIgnoreEliminatedOwls()
    {
        $party = self::generatePartyWithOwls();
        $deckCards = $this->generateFreshDeckCards();

        $this->croupier->placeCard($deckCards[3], $party->getOwls()[0]);
        $party->getOwls()[1]->setAlive(false);
        $this->croupier->placeCard($deckCards[4], $party->getOwls()[2]);
        $party->getOwls()[3]->setAlive(false);
        $this->croupier->placeCard($deckCards[5], $party->getOwls()[4]);
        $this->croupier->placeCard($deckCards[2], $party->getOwls()[5]);

        $minOwl = $party->getOwls()[5];

        $this->assertSame($minOwl, $this->croupier->checkOwlElimination($party));
    }
}
