<?php

namespace Alcalyn\Owls\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Alcalyn\Owls\Model\Owl;

class OwlRepository extends EntityRepository
{
    /**
     * @param int $eolePartyId
     *
     * @return QueryBuilder
     */
    private function findByEolePartyIdQuery($eolePartyId)
    {
        return $this->createQueryBuilder('owl')
            ->addSelect('deck_card', 'card', 'bet', 'player')
            ->leftJoin('owl.deckCard', 'deck_card')
            ->leftJoin('deck_card.card', 'card')
            ->leftJoin('owl.bets', 'bet')
            ->leftJoin('bet.player', 'player')
            ->leftJoin('owl.party', 'party')
            ->leftJoin('party.eoleParty', 'eole_party')
            ->where('eole_party.id = :eole_party_id')
            ->setParameter('eole_party_id', $eolePartyId)
        ;
    }

    /**
     * @param int $eolePartyId
     *
     * @return Owl[]
     */
    public function findByEolePartyId($eolePartyId)
    {
        $query = $this
            ->findByEolePartyIdQuery($eolePartyId)
            ->orderBy('owl.color')
        ;

        return $query
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param int $eolePartyId
     * @param int $color
     *
     * @return Owl
     */
    public function findOneByEolePartyIdAndColor($eolePartyId, $color)
    {
        $query = $this
            ->findByEolePartyIdQuery($eolePartyId)
            ->andWhere('owl.color = :color')
            ->setParameter('color', $color)
        ;

        return $query
            ->getQuery()
            ->getSingleResult()
        ;
    }
}
