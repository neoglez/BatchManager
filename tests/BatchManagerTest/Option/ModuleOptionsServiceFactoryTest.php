<?php

namespace BatchManagerTest\Option;

use Zend\ServiceManager\ServiceManager;
use BatchManager\Factory\ModuleOptionsServiceFactory;
use PHPUnit_Framework_TestCase;

class ModuleOptionsServiceFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testCreateFromFactory()
    {
        $config = [
            'batch_manager' => [
                'batch_entity_class' => 'Namespace\Subnamespace\Class',
            ],
        ];

        $serviceManager = new ServiceManager();
        $serviceManager->setService('Config', $config);

        $factory = new ModuleOptionsServiceFactory();
        $result = $factory->__invoke($serviceManager, 'BatchManager\Option\ModuleOptions');

        $this->assertInstanceOf('BatchManager\Option\ModuleOptions', $result);
    }
}