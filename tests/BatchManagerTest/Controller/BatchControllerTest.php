<?php
namespace BatchManagerTest\Controller;

use PHPUnit_Framework_TestCase;
use BatchManager\Controller\BatchController;
use BatchManager\Option\ModuleOptions;
use Zend\Http\Response;

class BatchManagerControllerTest extends PHPUnit_Framework_TestCase
{
    protected $controller;
    
    protected $moduleOptions;
    
    public function setUp()
    {
        $this->controller = new BatchController();
        // plugin manager
        $plugins = $this->getMock('Zend\Mvc\Controller\PluginManager');
        $this->controller->setPluginManager($plugins);
        
        $this->moduleOptions = new ModuleOptions();
    }
    
    public function resetControllerPluginManager()
    {
        // plugin manager
        $plugins = $this->getMock('Zend\Mvc\Controller\PluginManager');
        $this->controller->setPluginManager($plugins);
    }
    
    public function prepareControllerForStartAction()
    {
        $batchManager = $this->getMock('BatchManager\Service\BatchManager');
        $batch = $this->getMock('BatchManager\Entity\BatchInterface');
        
        // configure expectations
        $batchManager->expects($this->atLeastOnce())
                     ->method('startBatch')
                     ->will($this->returnSelf());
        
        $batchManager->expects($this->atLeastOnce())
                     ->method('getBatch')
                     ->will($this->returnValue($batch));
        
        $batchManagerService = 'batch_manager';
        
        $optionsService = 'batch_manager_module_options';
        
        // mock, set expectations and set the controller service locator
        $serviceLocator = $this->getMock(
        'Zend\ServiceManager\ServiceLocatorInterface');
        $serviceLocator->expects($this->at(0))
                       ->method('get')
                       ->with($batchManagerService)
                       ->will($this->returnValue($batchManager));
        
        $serviceLocator->expects($this->at(1))
                       ->method('get')
                       ->with($optionsService)
                       ->will($this->returnValue($this->moduleOptions));
        
        $this->controller->setServiceLocator($serviceLocator);
        $this->resetControllerPluginManager();
    }
    
    public function testStartActionReturnViewModelWithBatch()
    {
        $this->moduleOptions->setProcessBatchAfterStart(false);
        $this->prepareControllerForStartAction();
        
        // params plugin
        $paramsPlugin = $this->getMock('Zend\Mvc\Controller\Plugin\Params', array('fromQuery'));
        $paramsPlugin->expects($this->any())
                     ->method('fromQuery')
                     ->will($this->returnValue(array('key' => 'value')));
        
        // url plugin
        $urlPlugin = $this->getMock('Zend\Mvc\Controller\Plugin\Url');
        $urlPlugin->expects($this->at(0))
                  ->method('fromRoute')
                  ->will($this->returnValue('/some/url'));
        
        $urlPlugin->expects($this->at(1))
                  ->method('fromRoute')
                  ->will($this->returnValue('/another/url'));
        
        $plugins = $this->controller->getPluginManager();
        
        $plugins->expects($this->at(1))
                ->method('get')
                ->with('params')
                ->will($this->returnValue($paramsPlugin));
        
        $plugins->expects($this->at(3))
                ->method('get')
                ->with('url')
                ->will($this->returnValue($urlPlugin));
        
        $plugins->expects($this->at(5))
                ->method('get')
                ->with('url')
                ->will($this->returnValue($urlPlugin));
        
        $result = $this->controller->startAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $result);
        $this->assertEquals(null, $result->getVariable('batchId', 'defaultvalue'), "Asserting view model has batch ID set");
    }
    
    public function testStartActionCanRedirectToProcessAction()
    {
        $this->moduleOptions->setProcessBatchAfterStart(true);
        $this->prepareControllerForStartAction();
        
        $expectedResponse = new Response();
        $expectedResponse->setStatusCode(Response::STATUS_CODE_302);
        $expectedResponse->getHeaders()->addHeaderLine('Location', '/some/url?key=value');
        
        // params plugin
        $paramsPlugin = $this->getMock('Zend\Mvc\Controller\Plugin\Params', array('fromQuery'));
        $paramsPlugin->expects($this->any())
                    ->method('fromQuery')
                    ->will($this->returnValue(array('key' => 'value')));
        
        // redirect plugin
        $redirectPlugin = $this->getMock('Zend\Mvc\Controller\Plugin\Redirect');
        $redirectPlugin->expects($this->once())
                       ->method('toRoute')
                       ->will($this->returnValue($expectedResponse));
        
        $plugins = $this->controller->getPluginManager();
        
        $plugins->expects($this->at(3))
                ->method('get')
                ->with('redirect')
                ->will($this->returnValue($redirectPlugin));
        
        $plugins->expects($this->at(1))
                ->method('get')
                ->with('params')
                ->will($this->returnValue($paramsPlugin));
         
        $result = $this->controller->startAction();
        
        $this->assertInstanceOf('Zend\Http\Response', $result);
        
        $this->assertTrue($result->isRedirect(), 
                          "Asserting response code is 302");
        
        $this->assertEquals($result->getHeaders()->get('Location')->getFieldValue(),
                            '/some/url?key=value', 
                            "Asserting location header was set in response");
    }
}