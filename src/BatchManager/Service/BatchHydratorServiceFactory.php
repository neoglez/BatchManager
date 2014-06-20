<?php
namespace BatchManager\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Stdlib\Hydrator\ClassMethods as ClassMethodsHydrator;
use Zend\Stdlib\Hydrator\Strategy\SerializableStrategy;
use Zend\Serializer\Serializer as SerializerFactory;

class BatchHydratorServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        // maybe fetch config from service locator
        $hydrator = new ClassMethodsHydrator();
        
        // add strategy to data property to serialize
        $serializeStrategy = new SerializableStrategy(SerializerFactory::getDefaultAdapter());
        
        $hydrator->addStrategy('data', $serializeStrategy);
        
        return $hydrator;
    }
}