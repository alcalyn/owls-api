<?php

namespace Alcalyn\Owls\Controller;

use JMS\Serializer\ArrayTransformerInterface;
use JMS\Serializer\SerializationContext;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\OptimisticLockException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Alcalyn\SerializableApiResponse\ApiResponse;
use Eole\Core\Controller\LoggedPlayerTrait;
use Alcalyn\Owls\Exception\CroupierException;
use Alcalyn\Owls\Model\Party;
use Alcalyn\Owls\Event\Event;
use Alcalyn\Owls\Event\PlayBetEvent;
use Alcalyn\Owls\Event\PlayCardEvent;
use Alcalyn\Owls\Event\OwlEliminatedEvent;
use Alcalyn\Owls\Repository\PartyRepository;
use Alcalyn\Owls\Repository\PlayerRepository;
use Alcalyn\Owls\Service\Croupier;

class Controller
{
    use LoggedPlayerTrait;

    /**
     * @var PartyRepository
     */
    private $partyRepository;

    /**
     * @var PlayerRepository
     */
    private $playerRepository;

    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @var Croupier
     */
    private $croupier;

    /**
     * @var ArrayTransformerInterface
     */
    private $normalizer;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @param PartyRepository $partyRepository
     * @param PlayerRepository $playerRepository
     * @param ObjectManager $om
     * @param Croupier $croupier
     * @param ArrayTransformerInterface $normalizer
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(
        PartyRepository $partyRepository,
        PlayerRepository $playerRepository,
        ObjectManager $om,
        Croupier $croupier,
        ArrayTransformerInterface $normalizer,
        EventDispatcherInterface $dispatcher
    ) {
        $this->partyRepository = $partyRepository;
        $this->playerRepository = $playerRepository;
        $this->om = $om;
        $this->croupier = $croupier;
        $this->normalizer = $normalizer;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Get current logged in player cards in his hands.
     * Returns 404 if player has no hand in this party (i.e observers).
     *
     * @param int $eolePartyId
     *
     * @return ApiResponse
     */
    public function getHand($eolePartyId)
    {
        $this->mustBeLogged();

        try {
            $player = $this->playerRepository->findByEolePartyIdAndEolePlayer($eolePartyId, $this->loggedPlayer, true);
        } catch (NoResultException $e) {
            throw new NotFoundHttpException('You have no hand in this party.', $e);
        }

        return new ApiResponse($this->normalize($player->getDeck(), array('card_visible')));
    }

    /**
     * Returns party public data, with owls and players.
     *
     * @param int $eolePartyId
     *
     * @return ApiResponse
     */
    public function getTable($eolePartyId)
    {
        $party = $this->partyRepository->findFullByEolePartyId($eolePartyId, array(
            'owls' => true,
            'players' => true,
        ));

        return new ApiResponse($this->normalize($party, array('card_visible')));
    }

    /**
     * Logged player add a bet on a owl.
     *
     * @param int $eolePartyId
     * @param int $owlColor
     *
     * @return ApiResponse
     */
    public function playBet($eolePartyId, $owlColor)
    {
        $this->mustBeLogged();

        $party = $this->loadPartyOr404($eolePartyId);

        try {
            $this->croupier->checkPartyIsActive($party);

            $owl = $this->croupier->getOwlByColor($party, intval($owlColor));
            $player = $this->playerRepository->findByEolePartyIdAndEolePlayer($eolePartyId, $this->loggedPlayer);

            $this->croupier->checkTurnPhase($party, $player, Party::PHASE_BET);

            $bet = $this->croupier->placeBet($player, $owl);

            $this->croupier->nextPhase($party);
            $this->croupier->updateScores($party);

            $this->om->persist($bet);
            $this->om->flush();
        } catch (NoResultException $e) {
            throw new AccessDeniedHttpException('You cannot bet as you are observer.', $e);
        } catch (CroupierException $e) {
            throw new ConflictHttpException($e->getMessage(), $e);
        } catch (OptimisticLockException $e) {
            throw new ConflictHttpException('Bet failed, party has been updated by another processus.', $e);
        }

        $this->dispatcher->dispatch(Event::PLAY_BET, new PlayBetEvent($party, $bet));

        $response = array(
            'owl' => $owl,
            'bet' => $bet,
        );

        return new ApiResponse($this->normalize($response, array('card_visible')));
    }

    /**
     * @param int $eolePartyId
     * @param int $cardId
     *
     * @return ApiResponse
     */
    public function playCard($eolePartyId, $cardId)
    {
        $this->mustBeLogged();

        $party = $this->loadPartyOr404($eolePartyId);

        try {
            $this->croupier->checkPartyIsActive($party);

            $player = $this->playerRepository->findByEolePartyIdAndEolePlayer($eolePartyId, $this->loggedPlayer, true);

            $this->croupier->checkTurnPhase($party, $player, Party::PHASE_CARD);

            $deckCard = $this->croupier->getDeckCardByCardId($player->getDeck(), intval($cardId));
        } catch (NoResultException $e) {
            throw new AccessDeniedHttpException('You cannot play as you are not in party.', $e);
        } catch (CroupierException $e) {
            throw new ConflictHttpException($e->getMessage(), $e);
        }

        $owlColor = intval($deckCard->getCard()->getColor());
        $owl = $this->croupier->getOwlByColor($party, $owlColor);
        $newCard = null;
        $newHand = null;

        $this->croupier->placeCard($deckCard, $owl);

        if ($party->getDeck()->hasCards()) {
            $newCard = $this->croupier->distribute($party->getDeck(), $player->getDeck())->getCard();
        }

        $eliminatedOwl = $this->croupier->eliminateOwl($party);
        $hasElimination = null !== $eliminatedOwl;

        $this->croupier->nextTurn($party);

        if ($hasElimination) {
            $this->croupier->updateScores($party);
            $this->croupier->stopIfPartyOver($party);
        }

        $this->om->flush();

        $this->dispatcher->dispatch(Event::PLAY_CARD, new PlayCardEvent($party, $owl, $deckCard, $hasElimination));

        if ($hasElimination) {
            $this->dispatcher->dispatch(Event::MONKEY_ELIMINATED, new OwlEliminatedEvent($party, $owl));
        }

        return new ApiResponse(array(
            'new_card' => $newCard,
            'new_hand' => $newHand,
            'eliminated_owl' => $eliminatedOwl,
            'party' => $party,
        ));
    }

    /**
     * @param int $eolePartyId
     *
     * @return Party
     *
     * @throws NotFoundHttpException If party does not exists.
     */
    private function loadPartyOr404($eolePartyId)
    {
        try {
            return $this->partyRepository->findFullByEolePartyId($eolePartyId, array(
                'owls' => true,
                'party_deck' => true,
                'players_deck' => true,
            ));
        } catch (NoResultException $e) {
            throw new NotFoundHttpException('This party does not exists.', $e);
        }
    }

    /**
     * Shortcut to normalize with groups.
     *
     * @param mixed $data
     * @param string[] $groups
     *
     * @return array
     */
    private function normalize($data, $groups = array())
    {
        $groups []= 'Default';

        $faceUpContext = SerializationContext::create()
            ->setSerializeNull(true)
            ->setGroups($groups)
        ;

        return $this->normalizer->toArray($data, $faceUpContext);
    }
}
