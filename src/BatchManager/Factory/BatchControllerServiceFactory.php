<?php
namespace BatchManager\Service;


use BatchManager\Controller\BatchController;
use BatchManager\Option\ModuleOptions;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class BatchControllerServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new BatchController($container->get(BatchManager::class), $container->get(ModuleOptions::class));
    }
}