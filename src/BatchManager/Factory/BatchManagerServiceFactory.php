<?php
namespace BatchManager\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use BatchManager\Service\BatchManager;

class BatchManagerServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        // fetch options from service locator
        $options = null;
        if ($container->has('batch_manager_options')) {
            $options = $container->get('batch_manager_options');
        }

        return new BatchManager($options);
    }
}