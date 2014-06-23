<?php
namespace BatchManager\Listener;

use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use BatchManager\Event\BatchEvent;
use BatchManager\Persister\BatchPersisterInterface;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\ViewModel;
use ArrayObject;

class RegisterViewStrategyListener extends AbstractListenerAggregate
{
    
    /**
     * We attach with a higher priority to be able to run
     * before the \Zend\Mvc\View\InjectViewModelListener becouse
     * we want to set the view to terminal in order to avoid rendering
     * the "layout" (nested view model).
     * 
     * @param EventManagerInterface $event
     * @return void
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_RENDER, array($this, 'registerStrategy'), 300);
    }
    
    /**
     * @param  \Zend\Mvc\MvcEvent $e The MvcEvent instance
     * @return void
     */
    public function registerStrategy(MvcEvent $e)
    {
        if ($e->isError()) {
            return;
        }
        
        $matches    = $e->getRouteMatch();
        $controller = $matches->getParam('controller');
        $action = $matches->getParam('action');
        $app = $e->getTarget();
        $locator = $app->getServiceManager();
        
        if (false === strpos($controller, 'BatchManager')) {
            // not the right controller
            return;
        }
        
        /*@var $moduleOptions \BatchManager\Option\ModuleOptions */
        $moduleOptions = $locator->get('batch_manager_module_options');
        
        // Not content negociation if not processing
        if (!($action == $moduleOptions->getProcessAction())) {
            return;
        }
    
        // Set "our" strategy
        $view = $locator->get('Zend\View\View');
        $strategy = $locator->get('batch_manager_negociate_content_strategy');
    
        // Attach strategy, which is a listener aggregate, at high priority
        $view->getEventManager()->attach($strategy, 300);
    }
    
    public function mutateViewModel(MvcEvent $e)
    {
        $result = $e->getResult();
        if (!$result instanceof ViewModel) {
            return;
        }
        
        $matches    = $e->getRouteMatch();
        $controller = $matches->getParam('controller');
        
        if (false === strpos($controller, 'BatchManager')) {
            // not the right controller
            return;
        }
        
        $action = $matches->getParam('action');
    
        /*@var $request \Zend\Http\Request */
        $request = $e->getRequest();
        $cookies = $request->getCookie();
        
        $locator = $e->getApplication()->getServiceManager();
        
        /*@var $moduleOptions \BatchManager\Option\ModuleOptions */
        $moduleOptions = $locator->get('batch_manager_module_options');
    
        if ($cookies instanceof ArrayObject &&
            $cookies->offsetExists($moduleOptions->getCookieKey())
        ) {
            $hasJs = $cookies->offsetGet($moduleOptions->getCookieKey());
        } else {
            $hasJs = false;
        }
    
        $result->setVariable('hasJs', $hasJs);
        
        // Not content negociation if not processing
        if (!($action == $moduleOptions->getProcessAction())) {
            return;
        }
    
        // For process action we mark the view as terminal when hasJs
        // to avoid rendering the 'layout'
        $result->setTerminal($hasJs);
        $e->setResult($result);
    }
}
