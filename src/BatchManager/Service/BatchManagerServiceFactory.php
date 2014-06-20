<?php
namespace BatchManager\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use BatchManager\Service\BatchManager;

class BatchManagerServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        // fetch options from service locator
        $options = null;
        if ($serviceLocator->has('batch_manager_options')) {
            $options = $serviceLocator->get('batch_manager_options');
        }
        
        return new BatchManager($options);
    }
}