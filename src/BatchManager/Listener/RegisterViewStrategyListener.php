<?php

namespace BatchManager\Listener;

use BatchManager\Option\ModuleOptions;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\Http\Request;
use Zend\Mvc\Application;
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
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_RENDER, [$this, 'registerStrategy'], 300);
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

        $matches = $e->getRouteMatch();
        $controller = $matches->getParam('controller');
        $action = $matches->getParam('action');
        /** @var Application $app */
        $app = $e->getTarget();
        $locator = $app->getServiceManager();

        if (false === strpos($controller, 'BatchManager')) {
            // not the right controller
            return;
        }

        /** @var ModuleOptions $moduleOptions */
        $moduleOptions = $locator->get(ModuleOptions::class);

        // Not content negotiation if not processing
        if (!($action == $moduleOptions->getProcessAction())) {
            return;
        }

        // Set "our" strategy
        $view = $locator->get('Zend\View\View');
        /** @var ListenerAggregateInterface $strategy */
        $strategy = $locator->get(RegisterViewStrategyListener::class);

        // Attach strategy, which is a listener aggregate, at high priority
        $strategy->attach($view->getEventManager(),300);
    }

    public function mutateViewModel(MvcEvent $e)
    {
        $result = $e->getResult();
        if (!$result instanceof ViewModel) {
            return;
        }

        $matches = $e->getRouteMatch();
        $controller = $matches->getParam('controller');

        if (false === strpos($controller, 'BatchManager')) {
            // not the right controller
            return;
        }

        $action = $matches->getParam('action');

        /** @var Request $request */
        $request = $e->getRequest();
        $cookies = $request->getCookie();

        $locator = $e->getApplication()->getServiceManager();

        /** @var ModuleOptions $moduleOptions */
        $moduleOptions = $locator->get(ModuleOptions::class);

        if ($cookies instanceof ArrayObject &&
            $cookies->offsetExists($moduleOptions->getCookieKey())
        ) {
            $hasJs = $cookies->offsetGet($moduleOptions->getCookieKey());
        } else {
            $hasJs = false;
        }

        $result->setVariable('hasJs', $hasJs);

        // Not content negotiation if not processing
        if (!($action == $moduleOptions->getProcessAction())) {
            return;
        }

        // For process action we mark the view as terminal when hasJs
        // to avoid rendering the 'layout'
        $result->setTerminal($hasJs);
        $e->setResult($result);
    }
}
