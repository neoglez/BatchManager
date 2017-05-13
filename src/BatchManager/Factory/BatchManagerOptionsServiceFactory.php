<?php
namespace BatchManager\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use BatchManager\Option\BatchManagerOptions;

class BatchManagerOptionsServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('Config');

        return new BatchManagerOptions(isset($config['batch_manager']) ? $config['batch_manager'] : array());
    }
}