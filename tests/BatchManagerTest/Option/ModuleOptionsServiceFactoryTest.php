<?php
namespace BatchManagerTest\Option;

use Zend\ServiceManager\ServiceManager;
use BatchManager\Option\ModuleOptionsServiceFactory;
use PHPUnit_Framework_TestCase;

class ModuleOptionsServiceFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testCreateFromFactory()
    {
        $config = array(
            'batch_manager' => array(
                'batch_entity_class' => 'Namespace\Subnamespace\Class'
            )
        );
    
        $serviceManager = new ServiceManager();
        $serviceManager->setService('Config', $config);
    
        $factory = new ModuleOptionsServiceFactory();
        $result = $factory->createService($serviceManager);
    
        $this->assertInstanceOf('BatchManager\Option\ModuleOptions', $result);
    }
}