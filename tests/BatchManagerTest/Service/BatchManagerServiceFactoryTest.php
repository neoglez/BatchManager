<?php
namespace BatchManagerTest\Service;

use Zend\ServiceManager\ServiceManager;
use BatchManager\Service\BatchManagerServiceFactory;
use PHPUnit_Framework_TestCase;
use BatchManager\Option\BatchManagerOptions;

class BatchManagerServiceFactoryTest extends PHPUnit_Framework_TestCase
{
    protected $serviceLocator;
    
    public function setUp()
    {
        $this->serviceLocator = $this->getMock('Zend\ServiceManager\ServiceLocatorInterface');
    }
    
    public function testCreateFromFactoryNoOptions()
    {
        $this->serviceLocator->expects($this->atLeastOnce())
                             ->method('has')
                             ->with('batch_manager_options')
                             ->will($this->returnValue(false));
        $factory = new BatchManagerServiceFactory();
        $result = $factory->createService($this->serviceLocator);
        $this->assertInstanceOf('BatchManager\Service\BatchManager', $result);
    }
    
    public function testCreateFromFactoryWithOptions()
    {
        $this->serviceLocator->expects($this->atLeastOnce())
                             ->method('has')
                             ->with('batch_manager_options')
                             ->will($this->returnValue(true));
        
        $options = new BatchManagerOptions();
        $this->serviceLocator->expects($this->atLeastOnce())
                             ->method('get')
                             ->with('batch_manager_options')
                             ->will($this->returnValue($options));
        
        $factory = new BatchManagerServiceFactory();
        $result = $factory->createService($this->serviceLocator);
        $this->assertInstanceOf('BatchManager\Service\BatchManager', $result);
    }
}