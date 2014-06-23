<?php
namespace BatchManager;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\InitProviderInterface;
use Zend\ModuleManager\Feature\BootstrapListenerInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\EventManager\EventInterface;
use Zend\ModuleManager\ModuleManagerInterface;
use Zend\ModuleManager\Feature\ViewHelperProviderInterface;

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
        /*@var $eventManager \Zend\EventManager\EventManagerInterface */
        $eventManager  = $e->getApplication()->getEventManager();
        
        /*@var $serviceManager \Zend\ServiceManager\ServiceLocatorInterface */
        $serviceManager = $e->getApplication()->getServiceManager();
        
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
        
        // attach the init listener
        $initName = 'BatchManager\Listener\InitBatchParamsListener';
        $initListener = $serviceManager->get($initName);
        
        // attach the shutdown listener
        $sdName = 'BatchManager\Listener\ShutdownBatchListener';
        $sdListener = $serviceManager->get($sdName);
        
        // register the content negociation
        /*@var $moduleOptions \BatchManager\Option\ModuleOptions */
        $moduleOptions = $serviceManager->get('batch_manager_module_options');
        if ($moduleOptions->getUseContentNegociation()) {
            $registerStrategy = $serviceManager->get('batch_manager_register_strategy');
            $eventManager->attach($registerStrategy);
            
            /*@var $sharedEvents \Zend\EventManager\SharedEventManagerInterface */
            $sharedEvents = $eventManager->getSharedManager();
            $sharedEvents->attach(
                'Zend\Stdlib\DispatchableInterface',
                MvcEvent::EVENT_DISPATCH, 
                array($registerStrategy, 'mutateViewModel'),
                -95
                );
        }
        
        $bem = $e->getApplication()
                 ->getServiceManager()
                 ->get('batch_manager')
                 ->getEventManager();
        
        $bem->attach($initListener);
        $bem->attach($sdListener);
    }

    public function getConfig()
    {
        return include __DIR__ . '/../../config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/../../src/' . __NAMESPACE__,
                ),
            ),
        );
    }
    
    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'batch_manager_module_options' => 'BatchManager\Option\ModuleOptionsServiceFactory',
                'batch_manager_options' => 'BatchManager\Option\BatchManagerOptionsServiceFactory',
                'batch_manager' => 'BatchManager\Service\BatchManagerServiceFactory',
                'batch_manager_mapper' => 'BatchManager\Mapper\DbBatchMapperServiceFactory',
                'BatchManager\Listener\InitBatchParamsListener' => 'BatchManager\Listener\InitBatchParamsListenerServiceFactory',
                'BatchManager\Listener\ShutdownBatchListener' => 'BatchManager\Listener\ShutdownBatchListenerServiceFactory',
                'batch_manager_hydrator' => 'BatchManager\Service\BatchHydratorServiceFactory',
                'batch_manager_negociate_content_strategy' => 'BatchManager\Service\NegociateContentForJsStrategyServiceFactory'
            ),
            'invokables' => array(
                'batch_manager_register_strategy' => 'BatchManager\Listener\RegisterViewStrategyListener',
            ),
        );
    }
    
    public function getViewHelperConfig()
    {
        return array(
            'invokables' => array(
                'setHasJsCookie' => 'BatchManager\View\Helper\SetHasJsCookie',
            ),
        );
    }
}
