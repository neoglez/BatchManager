<?php
namespace BatchManager\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use BatchManager\View\Strategy\NegociateContentForJsStrategy;
use Zend\View\Renderer\JsonRenderer;

class NegociateContentForJsStrategyServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $phpRenderer = $serviceLocator->get('Zend\View\Renderer\PhpRenderer');
        $jsonRenderer = new JsonRenderer();
        
        return new NegociateContentForJsStrategy(
            $phpRenderer, 
            $jsonRenderer
        );
    }
}