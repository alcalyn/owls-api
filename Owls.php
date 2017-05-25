<?php

namespace Alcalyn\Owls;

use Doctrine\Common\Persistence\ObjectManager;
use Pimple\Container;
use Eole\Core\Model\Game;
use Eole\Silex\GameProvider;
use Eole\Silex\Application;

class Owls extends GameProvider
{
    /**
     * @var string
     */
    const GAME_NAME = 'owls';

    /**
     * {@InheritDoc}
     */
    public function createGame()
    {
        $game = new Game();

        $game->setName(self::GAME_NAME);

        return $game;
    }

    /**
     * {@InheritDoc}
     */
    public function createFixtures(Application $app, ObjectManager $om)
    {
        $cards = $app['owls.croupier']->createCards();

        foreach ($cards as $card) {
            $om->persist($card);
        }
    }

    /**
     * {@InheritDoc}
     */
    public function createServiceProvider()
    {
        return new EoleProvider\ServiceProvider();
    }

    /**
     * {@InheritDoc}
     */
    public function createControllerProvider()
    {
        return new EoleProvider\ControllerProvider();
    }

    /**
     * {@InheritDoc}
     */
    public function createWebsocketProvider()
    {
        return new EoleProvider\WebsocketProvider();
    }
}
