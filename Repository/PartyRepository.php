<?php

namespace Alcalyn\Owls\Repository;

use Doctrine\ORM\NoResultException;
use Doctrine\ORM\EntityRepository;
use Alcalyn\Owls\Model\Party;

class PartyRepository extends EntityRepository
{
    /**
     * @param int $eolePartyId
     * @param bool[] $with An array with keys "party_deck" and "owls" to true if must be loaded.
     *
     * @return Party
     *
     * @throws NoResultException
     */
    public function findFullByEolePartyId($eolePartyId, $with = array())
    {
        $query = $this->createQueryBuilder('party');

        $query
            ->addSelect('eole_party', 'eole_game')
            ->leftJoin('party.eoleParty', 'eole_party')
            ->leftJoin('eole_party.game', 'eole_game')
            ->where('eole_party.id = :eole_party_id')
            ->setParameter('eole_party_id', $eolePartyId)
        ;

        if (isset($with['players']) || isset($with['players_deck'])) {
            $query
                ->addSelect('player', 'eole_player', 'slot')
                ->leftJoin('party.players', 'player')
                ->leftJoin('player.eolePlayer', 'eole_player')
                ->leftJoin('eole_player.slots', 'slot')
                ->addOrderBy('player.order')
            ;
        }

        if (isset($with['party_deck'])) {
            $query
                ->addSelect('party_deck', 'party_deck_card', 'party_card')
                ->leftJoin('party.deck', 'party_deck')
                ->leftJoin('party_deck.deckCards', 'party_deck_card')
                ->leftJoin('party_deck_card.card', 'party_card')
            ;
        }

        $party = $query
            ->getQuery()
            ->getSingleResult()
        ;

        if (isset($with['owls'])) {
            $this->createQueryBuilder('party')
                ->addSelect('owl')
                ->addSelect('bet')
                ->addSelect('bet_player')
                ->addSelect('bet_eole_player')
                ->addSelect('owl_deck_card', 'owl_card')
                ->leftJoin('party.owls', 'owl')
                ->leftJoin('owl.bets', 'bet')
                ->leftJoin('bet.player', 'bet_player')
                ->leftJoin('bet_player.eolePlayer', 'bet_eole_player')
                ->leftJoin('owl.deckCard', 'owl_deck_card')
                ->leftJoin('owl_deck_card.card', 'owl_card')
                ->addOrderBy('owl.color')
                ->addOrderBy('bet.order')
                ->where('party.id = :party_id')
                ->setParameter('party_id', $party->getId())
                ->getQuery()
                ->getResult()
            ;
        }

        if (isset($with['players_deck'])) {
            $this->createQueryBuilder('party')
                ->addSelect('player')
                ->addSelect('player_deck', 'player_deck_card', 'player_card')
                ->leftJoin('party.players', 'player')
                ->leftJoin('player.deck', 'player_deck')
                ->leftJoin('player_deck.deckCards', 'player_deck_card')
                ->leftJoin('player_deck_card.card', 'player_card')
                ->where('party.id = :party_id')
                ->setParameter('party_id', $party->getId())
                ->getQuery()
                ->getResult()
            ;
        }

        return $party;
    }
}
