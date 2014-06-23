<?php
namespace BatchManager\Listener;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use BatchManager\Listener\ShutdownBatchListener;

class ShutdownBatchListenerServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /*@var $batchMapper \BatchManager\Persister\BatchPersisterInterface */
        $batchMapper = $serviceLocator->get('batch_manager_mapper');
        return new ShutdownBatchListener($batchMapper);
    }
}
