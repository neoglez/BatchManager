<?php
namespace BatchManager\Factory;

use BatchManager\Mapper\DbBatchMapper;
use BatchManager\Option\ModuleOptions;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use BatchManager\Listener\InitBatchParamsListener;
use BatchManager\Generator\BatchParamsGenerator;

class InitBatchParamsListenerServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /** @var ModuleOptions $moduleOptions */
        $moduleOptions = $container->get('batch_manager_module_options');

        $paramsGenerator = new BatchParamsGenerator($moduleOptions->getSecretSecretKey());

        /** @var DbBatchMapper $batchMapper */
        $batchMapper = $container->get('batch_manager_mapper');
        return new InitBatchParamsListener($batchMapper, $paramsGenerator);
    }
}
