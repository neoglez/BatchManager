<?php

namespace BatchManagerTest\Controller;

use BatchManager\Service\BatchManager;
use PHPUnit_Framework_TestCase;
use BatchManager\Controller\BatchController;
use BatchManager\Option\ModuleOptions;
use Zend\Http\Response;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\Controller\PluginManager;

class BatchManagerControllerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var AbstractActionController
     */
    protected $controller;

    /**
     * @var ModuleOptions
     */
    protected $moduleOptions;

    public function setUp()
    {
        $this->moduleOptions = new ModuleOptions();
        $batchManager = $this->createMock('BatchManager\Service\BatchManager');
        $batch = $this->createMock('BatchManager\Entity\BatchInterface');

        // configure expectations
        $batchManager->expects($this->atLeastOnce())
            ->method('startBatch')
            ->will($this->returnSelf());

        $batchManager->expects($this->atLeastOnce())
            ->method('getBatch')
            ->will($this->returnValue($batch));


        $this->controller = new BatchController($batchManager, $this->moduleOptions);
    }

    public function resetControllerPluginManager()
    {

    }


    public function testStartActionReturnViewModelWithBatch()
    {
        $this->moduleOptions->setProcessBatchAfterStart(false);
        $this->resetControllerPluginManager();

        // plugin manager
        $plugins = $this->getMockBuilder(PluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        // params plugin
        $paramsPlugin = $this->getMockBuilder('Zend\Mvc\Controller\Plugin\Params')
            ->setMethods(['fromQuery'])
            ->getMock();

        $paramsPlugin->expects($this->any())
            ->method('fromQuery')
            ->will($this->returnValue(['key' => 'value']));


        // url plugin
        $urlPlugin = $this->getMockBuilder('Zend\Mvc\Controller\Plugin\Url')
            ->setMethods(['fromRoute'])
            ->getMock();

        $urlPlugin->expects($this->any())
            ->method('fromRoute')
            ->will($this->returnValue('/some/url'));


        $plugins->expects($this->exactly(3))
            ->method('get')
            ->withConsecutive([$this->equalTo('params'), $this->equalTo(null)],
                [$this->equalTo('url'), $this->equalTo(null)],
                [$this->equalTo('url'), $this->equalTo(null)])
            ->willReturnOnConsecutiveCalls($this->returnValue($paramsPlugin), $this->returnValue($urlPlugin), $this->returnValue($urlPlugin));

        $this->controller->setPluginManager($plugins);

        $result = $this->controller->startAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $result);
        $this->assertEquals(null, $result->getVariable('batchId', 'defaultvalue'),
            "Asserting view model has batch ID set");
    }

    public function testStartActionCanRedirectToProcessAction()
    {
        $this->moduleOptions->setProcessBatchAfterStart(true);
        $this->resetControllerPluginManager();

        $expectedResponse = new Response();
        $expectedResponse->setStatusCode(Response::STATUS_CODE_302);
        $expectedResponse->getHeaders()->addHeaderLine('Location', '/some/url?key=value');

        // plugin manager
        $plugins = $this->getMockBuilder(PluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        // params plugin
        $paramsPlugin = $this->getMockBuilder('Zend\Mvc\Controller\Plugin\Params')
            ->setMethods(['fromQuery'])
            ->getMock();

        $paramsPlugin->expects($this->any())
            ->method('fromQuery')
            ->will($this->returnValue(['key' => 'value']));

        // redirect plugin
        $redirectPlugin = $this->createMock('Zend\Mvc\Controller\Plugin\Redirect');
        $redirectPlugin->expects($this->once())
            ->method('toRoute')
            ->will($this->returnValue($expectedResponse));


        $plugins->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(
                [$this->equalTo('params'), $this->equalTo(null)],
                [$this->equalTo('redirect'), $this->equalTo(null)])
            ->willReturnOnConsecutiveCalls(
                $this->returnValue($paramsPlugin),
                $this->returnValue($redirectPlugin));

        $this->controller->setPluginManager($plugins);


        $result = $this->controller->startAction();

        $this->assertInstanceOf('Zend\Http\Response', $result);

        $this->assertTrue($result->isRedirect(),
            "Asserting response code is 302");

        $this->assertEquals($result->getHeaders()->get('Location')->getFieldValue(),
            '/some/url?key=value',
            "Asserting location header was set in response");
    }
}