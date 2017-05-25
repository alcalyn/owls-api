<?php

namespace Alcalyn\Owls\Repository;

use Doctrine\ORM\EntityRepository;
use Eole\Core\Model\Player as EolePlayer;
use Alcalyn\Owls\Model\Player;

class PlayerRepository extends EntityRepository
{
    /**
     * @param int $eolePartyId
     * @param EolePlayer $eolePlayer
     * @param bool $withCards
     *
     * @return Player
     *
     * @throws NonUniqueResultException
     * @throws NoResultException when player is not in party.
     */
    public function findByEolePartyIdAndEolePlayer($eolePartyId, EolePlayer $eolePlayer, $withCards = false)
    {
        $query = $this->createQueryBuilder('player')
            ->leftJoin('player.party', 'party')
            ->leftJoin('party.eoleParty', 'eole_party')
            ->leftJoin('player.eolePlayer', 'eole_player')
            ->where('eole_party.id = :eole_party_id')
            ->andWhere('eole_player = :eole_player')
            ->setParameters(array(
                'eole_party_id' => $eolePartyId,
                'eole_player' => $eolePlayer,
            ))
        ;

        if ($withCards) {
            $query
                ->addSelect('deck')
                ->addSelect('deck_card')
                ->addSelect('card')
                ->leftJoin('player.deck', 'deck')
                ->leftJoin('deck.deckCards', 'deck_card')
                ->leftJoin('deck_card.card', 'card')
            ;
        }

        return $query
            ->getQuery()
            ->getSingleResult()
        ;
    }
}
