<?php
namespace BatchManager\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\Hydrator\ClassMethods as ClassMethodsHydrator;
use Zend\Hydrator\Strategy\SerializableStrategy;
use Zend\Serializer\Serializer as SerializerFactory;

class BatchHydratorServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        // maybe fetch config from service locator
        $hydrator = new ClassMethodsHydrator();

        // add strategy to data property to serialize
        $serializeStrategy = new SerializableStrategy(SerializerFactory::getDefaultAdapter());

        $hydrator->addStrategy('data', $serializeStrategy);

        return $hydrator;
    }
}