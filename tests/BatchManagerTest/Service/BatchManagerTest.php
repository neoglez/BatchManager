<?php
namespace BatchManagerTest\Service;

use BatchManager\Service\BatchManager;
use PHPUnit_Framework_TestCase;

class BatchManagerTest extends PHPUnit_Framework_TestCase
{
    
    
    public function testStartBatch()
    {
         $batchManager = new BatchManager();
         $batch = $batchManager->getBatch();
         $eventManager = $this->getMock('Zend\EventManager\EventManagerInterface');
         $batchManager->setEventManager($eventManager);
         
         $event = $this->getMock('BatchManager\Event\BatchEvent');
         $event->expects($this->once())->method('setTarget')->with($this->identicalTo($batchManager));
         $event->expects($this->atLeastOnce())->method('setBatch')->with($this->identicalTo($batch));
         
         $batchManager->setBatchEvent($event);
         
         /* @var $responses \Zend\EventManager\ResponseCollection */
         $responses = $this->getMock('Zend\EventManager\ResponseCollection');
        
         $eventManager->expects($this->once())
                      ->method('trigger')
                      ->with('batch.start', $event)
                      ->will($this->returnValue($responses));

        $this->assertInstanceOf('BatchManager\Service\BatchManagerInterface', $batchManager->startBatch());
    }
}