<?php
namespace BatchManager\Mapper;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use BatchManager\Mapper\DbBatchMapper;
use BatchManager\Option\ModuleOptions;

class DbBatchMapperServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        // following the pattern on zfc-user
        
        /* @var $options ModuleOptions */
        $options = $serviceLocator->get('batch_manager_module_options');
        
        $entityClass = $options->getBatchEntityClass();
        
        /* @var $dbAdapter \Zend\Db\Adapter\Adapter */
        $dbAdapter = $serviceLocator->get('batch_manager_zend_db_adapter');
        
        $mapper = new DbBatchMapper();
        $mapper->setDbAdapter($dbAdapter);
        
        
        /* @var $hydrator \Zend\Stdlib\Hydrator\HydratorInterface */
        $hydrator = $serviceLocator->get('batch_manager_hydrator');
        
        $mapper->setTableName($options->getTableName())
               ->setEntityPrototype(new $entityClass)
               ->setDbAdapter($dbAdapter)
               ->setHydrator($hydrator);
        
        return $mapper;
    }
}