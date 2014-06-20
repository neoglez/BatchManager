<?php
namespace BatchManager\Option;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use BatchManager\Option\ModuleOptions;

class ModuleOptionsServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config');
        
        return new ModuleOptions(isset($config['batch_manager_module']) ? $config['batch_manager_module'] : array());
    }
}