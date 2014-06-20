<?php
namespace BatchManagerTest\Event;

use PHPUnit_Framework_TestCase;
use BatchManager\Event\BatchEvent;
use BatchManager\Entity\Batch;

class BatchEventTest extends PHPUnit_Framework_TestCase
{
    protected $event;
    
    /**
     * Prepare the object to be tested.
     */
    protected function setUp()
    {
        $this->event = new BatchEvent();
    }
    
    public function testSetGetBatch()
    {
        $this->event->setBatch(new Batch);
        $this->assertInstanceOf('BatchManager\Entity\BatchInterface', $this->event->getBatch());
        $this->assertInstanceOf('BatchManager\Entity\BatchInterface', $this->event->getParam('batch'));
    }
    
    public function testIsStartedIsProgressiveIsBatchLoaded()
    {
        $isStarted = true;
        $isProgressive = true;
        $isBatchLoaded = true;
        
        $this->assertEquals(false, $this->event->isStarted(), "Asserting batch event default is no started.");
        $this->assertEquals(true, $this->event->isProgressive(), "Asserting batch event default is progressive.");
        $this->assertEquals(false, $this->event->isBatchLoaded(), "Asserting batch event default has no loaded batch.");
        
        $this->event->isStarted(true);
        $this->event->isProgressive(false);
        $this->event->isBatchLoaded(true);
        
        $this->assertEquals(false, $this->event->isStarted(), "Asserting can't mark event as started.");
        $this->assertEquals(false, $this->event->isProgressive(), "Asserting can mark event as not progressive.");
        $this->assertEquals(true, $this->event->isBatchLoaded(), "Asserting can mark event batch as loaded.");
        
    }
    
    public function testEventIsStartedWhenBatchHasTimestamp()
    {
        $time = time();
        $batch = $this->getMock('BatchManager\Entity\BatchInterface');
        $batch->expects($this->once())
              ->method('getTimestamp')
              ->will($this->returnValue($time));
        
        $this->event->setBatch($batch);
        
        $this->assertEquals(true, $this->event->isStarted());
    }
    
    public function testEventLazyLoadABatch()
    {    
        $this->assertInstanceOf('BatchManager\Entity\BatchInterface', $this->event->getBatch());
    }
    
    public function testGetSetPercetageAndCurrentMessage()
    {
        $percentage = 97;
        $currentMessage = 'Processing 97 out of 100';
    
        $this->event->setPercentage($percentage);
        $this->event->setCurrentMessage($currentMessage);
    
        $this->assertEquals($percentage, $this->event->getPercentage());
        $this->assertEquals($currentMessage, $this->event->getCurrentMessage());    
    }
    
    public function testGetSetMaxMinAndCurrent()
    {
        $this->event->setMax(500);
        $this->event->setMin(0);
        $this->event->setCurrent(270);
        
        $this->assertEquals(500, $this->event->getMax());
        $this->assertEquals(0, $this->event->getMin());
        $this->assertEquals(270, $this->event->getCurrent());
    }
    
    public function testThrowExceptionWhenMaxSmallerThanMin()
    {
        $max = 250;
        $min = 300;
        $message = "Maximum can't be smaller than minimum";
        $this->setExpectedException('InvalidArgumentException', $message);
        
        $this->event->setMin($min);
        $this->event->setMax($max);
    }
    
    public function testUpdatePercentageWhenCurrentEqualsMax()
    {
        $current = 500;
        $max = 500;
        $this->event->setMax($max);
        $this->event->setCurrent($current);
        $this->event->updatePercentage();
        $this->assertEquals(100, $this->event->getPercentage());
    }
    
    public function testUpdatePercentageWhenCurrentIsCloseToMax()
    {
        $current = 199;
        $max = 200;
        $this->event->setMax($max);
        $this->event->setCurrent($current);
        $this->event->updatePercentage();
        $this->assertNotEquals(100, $this->event->getPercentage());
    }
    
}