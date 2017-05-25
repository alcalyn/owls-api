<?php

namespace Alcalyn\Owls\EoleProvider;

use Pimple\ServiceProviderInterface;
use Pimple\Container;
use Alcalyn\Owls\Service\Croupier;

class ServiceProvider implements ServiceProviderInterface
{
    /**
     * {@InheritDoc}
     */
    public function register(Container $app)
    {
        $app['serializer.builder']->addMetadataDir(
            __DIR__.'/../Serializer',
            'Alcalyn\\Owls'
        );

        $app->extend('eole.mappings', function ($mappings, $app) {
            $mappings []= array(
                'type' => 'yml',
                'namespace' => 'Alcalyn\\Owls\\Model',
                'path' => __DIR__.'/../Mapping',
                'alias' => 'Owls',
            );

            return $mappings;
        });

        $app['owls.croupier'] = function () use ($app) {
            return new Croupier($app['eole.party_manager']);
        };
    }
}
