<?php
namespace BatchManager\Listener;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use BatchManager\Listener\InitBatchParamsListener;
use BatchManager\Generator\BatchParamsGenerator;

class InitBatchParamsListenerServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /*@var $moduleOptions \BatchManager\Option\ModuleOptions */
        $moduleOptions = $serviceLocator->get('batch_manager_module_options');
        
        $paramsGenerator = new BatchParamsGenerator($moduleOptions->getSecretSecretKey());
        
        /*@var $batchMapper \BatchManager\Persister\BatchPersisterInterface */
        $batchMapper = $serviceLocator->get('batch_manager_mapper'); 
        return new InitBatchParamsListener($batchMapper, $paramsGenerator);
    }
}