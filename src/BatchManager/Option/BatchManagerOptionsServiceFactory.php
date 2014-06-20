<?php
namespace BatchManager\Option;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use BatchManager\Option\BatchManagerOptions;

class BatchManagerOptionsServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config');
        
        return new BatchManagerOptions(isset($config['batch_manager']) ? $config['batch_manager'] : array());
    }
}