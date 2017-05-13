<?php
namespace BatchManager\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use BatchManager\Mapper\DbBatchMapper;
use BatchManager\Option\ModuleOptions;

class DbBatchMapperServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        // following the pattern on zfc-user

        /* @var $options ModuleOptions */
        $options = $container->get(ModuleOptions::class);

        $entityClass = $options->getBatchEntityClass();

        /* @var $dbAdapter \Zend\Db\Adapter\Adapter */
        $dbAdapter = $container->get('batch_manager_zend_db_adapter');

        $mapper = new DbBatchMapper();
        $mapper->setDbAdapter($dbAdapter);


        /* @var $hydrator \Zend\Hydrator\HydratorInterface */
        $hydrator = $container->get('batch_manager_hydrator');

        $mapper->setTableName($options->getTableName())
            ->setEntityPrototype(new $entityClass)
            ->setDbAdapter($dbAdapter)
            ->setHydrator($hydrator);

        return $mapper;
    }
}
