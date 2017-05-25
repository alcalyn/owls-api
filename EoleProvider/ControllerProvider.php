<?php

namespace Alcalyn\Owls\EoleProvider;

use Pimple\ServiceProviderInterface;
use Pimple\Container;
use Eole\Core\Event\PartyEvent;
use Eole\Core\Event\SlotEvent;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Alcalyn\Owls\Event\Event;
use Alcalyn\Owls\EventListener\PartyListener;
use Alcalyn\Owls\EventListener\SlotListener;
use Alcalyn\Owls\Controller\Controller;

class ControllerProvider implements ServiceProviderInterface, ControllerProviderInterface
{
    /**
     * {@InheritDoc}
     */
    public function register(Container $app)
    {
        $app['owls.controller'] = function () use ($app) {
            return new Controller(
                $app['orm.em']->getRepository('Owls:Party'),
                $app['orm.em']->getRepository('Owls:Player'),
                $app['orm.em'],
                $app['owls.croupier'],
                $app['serializer'],
                $app['dispatcher']
            );
        };

        $app->before(function () use ($app) {
            if (null !== $app['user']) {
                $app['owls.controller']->setLoggedPlayer($app['user']);
            }

            $app->on(
                PartyEvent::CREATE_BEFORE,
                array($app['eole.games.owls.listener.party'], 'onPartyCreateBefore')
            );

            $app->on(
                SlotEvent::JOIN_AFTER,
                array($app['eole.games.owls.listener.slot'], 'onSlotJoinAfter')
            );

            $app->on(
                PartyEvent::STARTED,
                array($app['eole.games.owls.listener.party'], 'onPartyStarted')
            );
        });

        $app['eole.games.owls.listener.party'] = function () use ($app) {
            return new PartyListener(
                $app['owls.croupier'],
                $app['orm.em']->getRepository('Owls:Card'),
                $app['orm.em']->getRepository('Owls:Party'),
                $app['eole.party_manager'],
                $app['orm.em']
            );
        };

        $app['eole.games.owls.listener.slot'] = function () use ($app) {
            return new SlotListener(
                $app['orm.em'],
                $app['orm.em']->getRepository('Owls:Party'),
                $app['eole.party_manager']
            );
        };

        $app->forwardEventsToPushServer(array(
            Event::PLAY_BET,
            Event::PLAY_CARD,
            Event::MONKEY_ELIMINATED,
        ));
    }

    /**
     * {@InheritDoc}
     */
    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];

        $controllers->get('parties/{eolePartyId}/hand', 'owls.controller:getHand');
        $controllers->get('parties/{eolePartyId}/table', 'owls.controller:getTable');
        $controllers->post('parties/{eolePartyId}/bet/{owlColor}', 'owls.controller:playBet');
        $controllers->post('parties/{eolePartyId}/play/{cardId}', 'owls.controller:playCard');

        return $controllers;
    }
}
