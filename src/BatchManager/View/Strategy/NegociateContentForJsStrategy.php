<?php
namespace BatchManager\View\Strategy;

use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Zend\View\ViewEvent;
use Zend\View\Renderer\JsonRenderer;
use Zend\View\Renderer\PhpRenderer;

class NegociateContentForJsStrategy extends AbstractListenerAggregate
{
    /**
     * @var JsonRenderer
     */
    protected $jsonRenderer;

    /**
     * @var PhpRenderer
     */
    protected $phpRenderer;
    
    public function __construct(
        PhpRenderer $phpRenderer,
        JsonRenderer $jsonRenderer
    ) {
        $this->phpRenderer  = $phpRenderer;
        $this->jsonRenderer = $jsonRenderer;
    }

    /**
     *
     * @param EventManagerInterface $events
     * @param int $priority
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->listeners[] = $events->attach(ViewEvent::EVENT_RENDERER, array($this, 'selectRenderer'));
        $this->listeners[] = $events->attach(ViewEvent::EVENT_RESPONSE, array($this, 'injectResponse'));
    }
    
    /**
     * 
     * @param ViewEvent $e
     * @return PhpRenderer|JsonRenderer
     */
    public function selectRenderer(ViewEvent $e)
    {
        // return the php renderer if the view isn't terminal
        if (!($e->getModel()->terminate())) {
            return $this->phpRenderer;
        }
        
        // if the user has a cookie set indicating he has js activated
        // we return the json renderer for the 
        // process action (= view terminate return true)
        $hasJs = $e->getModel()->getVariable('hasJs');
        
        if ($hasJs) {
            return $this->jsonRenderer;
        }
        
        return $this->phpRenderer;
    }
    
    /**
     * @param  \Zend\Mvc\MvcEvent $e The MvcEvent instance
     * @return void
     */
    public function injectResponse($e)
    {
        $renderer = $e->getRenderer();
        $response = $e->getResponse();
        $result   = $e->getResult();
    
        if ($renderer === $this->jsonRenderer) {
            // JSON Renderer; set content-type header
            $headers = $response->getHeaders();
            $headers->addHeaderLine('content-type', 'application/json');
        } elseif ($renderer !== $this->phpRenderer) {
            // Not a renderer we support, therefor not our strategy. Return
            return;
        }
        
        $response->setContent($result);
    }
}