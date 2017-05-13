<?php

namespace BatchManager\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use BatchManager\Option\ModuleOptions;

class ModuleOptionsServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('Config');

        return new ModuleOptions(isset($config['batch_manager_module']) ? $config['batch_manager_module'] : []);
    }
}