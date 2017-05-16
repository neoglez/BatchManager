<?php
namespace BatchManager\Factory;

use BatchManager\Mapper\DbBatchMapper;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use BatchManager\Listener\ShutdownBatchListener;

class ShutdownBatchListenerServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /** @var DbBatchMapper $batchMapper */
        $batchMapper = $container->get('batch_manager_mapper');
        return new ShutdownBatchListener($batchMapper);
    }
}
