<?php

namespace BatchManager;

use BatchManager\Listener\InitBatchParamsListener;
use BatchManager\Factory\InitBatchParamsListenerServiceFactory;
use BatchManager\Listener\MessageOnErrorListener;
use BatchManager\Listener\RegisterViewStrategyListener;
use BatchManager\Listener\ShutdownBatchListener;
use BatchManager\Factory\ShutdownBatchListenerServiceFactory;
use BatchManager\Factory\DbBatchMapperServiceFactory;
use BatchManager\Option\BatchManagerOptions;
use BatchManager\Factory\BatchManagerOptionsServiceFactory;
use BatchManager\Option\ModuleOptions;
use BatchManager\Factory\ModuleOptionsServiceFactory;
use BatchManager\Factory\BatchHydratorServiceFactory;
use BatchManager\Service\BatchManager;
use BatchManager\Factory\BatchManagerServiceFactory;
use BatchManager\Service\NegociateContentForJsStrategyServiceFactory;
use BatchManager\View\Helper\SetHasJsCookie;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\InitProviderInterface;
use Zend\ModuleManager\Feature\BootstrapListenerInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\Mvc\Application;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\EventManager\EventInterface;
use Zend\ModuleManager\ModuleManagerInterface;
use Zend\ModuleManager\Feature\ViewHelperProviderInterface;
use Zend\ServiceManager\Factory\InvokableFactory;

class Module implements
    AutoloaderProviderInterface,
    InitProviderInterface,
    ConfigProviderInterface,
    BootstrapListenerInterface,
    ViewHelperProviderInterface
{
    public function init(ModuleManagerInterface $manager)
    {
        //just in case we need it
    }

    public function onBootstrap(EventInterface $e)
    {
        /** @var Application $app */
        $app = $e->getApplication();

        $eventManager = $app->getEventManager();
        $serviceManager = $app->getServiceManager();

        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

        // attach the init listener
        /** @var AbstractListenerAggregate $initListener */
        $initListener = $serviceManager->get(InitBatchParamsListener::class);

        // attach the shutdown listener
        /** @var AbstractListenerAggregate $sdListener */
        $sdListener = $serviceManager->get(ShutdownBatchListener::class);

        // attach the message listener
        /** @var AbstractListenerAggregate $moeListener */
        $moeListener = $serviceManager->get(MessageOnErrorListener::class);

        // register the content negotiation
        /** @var ModuleOptions $moduleOptions */
        $moduleOptions = $serviceManager->get(ModuleOptions::class);
        if ($moduleOptions->getUseContentNegociation()) {
            /** @var AbstractListenerAggregate $registerStrategy */
            $registerStrategy = $serviceManager->get(RegisterViewStrategyListener::class);
            $registerStrategy->attach($eventManager);

            /*@var $sharedEvents \Zend\EventManager\SharedEventManagerInterface */
            $sharedEvents = $eventManager->getSharedManager();
            $sharedEvents->attach(
                'Zend\Stdlib\DispatchableInterface',
                MvcEvent::EVENT_DISPATCH,
                [$registerStrategy, 'mutateViewModel'],
                -95
            );
        }

        $bem = $serviceManager
            ->get(BatchManager::class)
            ->getEventManager();

        $initListener->attach($bem);
        $sdListener->attach($bem);
        $moeListener->attach($bem);
    }

    public function getConfig()
    {
        return include __DIR__ . '/../../config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return [
            'Zend\Loader\StandardAutoloader' => [
                'namespaces' => [
                    __NAMESPACE__ => __DIR__ . '/../../src/' . __NAMESPACE__,
                ],
            ],
        ];
    }

    public function getServiceConfig()
    {
        return [
            'factories' => [
                ModuleOptions::class => ModuleOptionsServiceFactory::class,
                BatchManagerOptions::class => BatchManagerOptionsServiceFactory::class,
                BatchManager::class => BatchManagerServiceFactory::class,
                'batch_manager_mapper' => DbBatchMapperServiceFactory::class,
                InitBatchParamsListener::class => InitBatchParamsListenerServiceFactory::class,
                ShutdownBatchListener::class => ShutdownBatchListenerServiceFactory::class,
                'batch_manager_hydrator' => BatchHydratorServiceFactory::class,
                'batch_manager_negociate_content_strategy' => NegociateContentForJsStrategyServiceFactory::class,
                RegisterViewStrategyListener::class => InvokableFactory::class,
                MessageOnErrorListener::class => InvokableFactory::class,
            ],
            'aliases' => [
                'batch_manager_register_strategy' => RegisterViewStrategyListener::class,
            ],
        ];
    }

    public function getViewHelperConfig()
    {
        return [
            'factories' => [
                SetHasJsCookie::class => InvokableFactory::class,
            ],
            'aliases' => [
                'setHasJsCookie' => SetHasJsCookie::class,
            ],
        ];
    }
}
