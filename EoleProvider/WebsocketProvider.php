<?php

namespace Alcalyn\Owls\EoleProvider;

use Pimple\ServiceProviderInterface;
use Pimple\Container;
use Alcalyn\Owls\Websocket\Topic;

class WebsocketProvider implements ServiceProviderInterface
{
    /**
     * {@InheritDoc}
     */
    public function register(Container $app)
    {
        $app
            ->topic('eole/games/owls/parties/{party_id}', function ($topicPattern, $arguments) {
                return new Topic($topicPattern, intval($arguments['party_id']));
            })
            ->assert('party_id', '^[0-9]+$')
        ;
    }
}
