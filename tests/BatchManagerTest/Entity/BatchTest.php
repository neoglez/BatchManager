<?php
namespace BatchManagerTest\Entity;

use PHPUnit_Framework_TestCase;
use BatchManager\Entity\Batch;

class BatchTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Batch
     */
    protected $batchEntity;
    
    public function setUp()
    {
        $this->batchEntity = new Batch(); 
    }
    
    public function testSetGetIntegerBid()
    {
        $this->batchEntity->setBid(123);
        $this->assertEquals(123, $this->batchEntity->getBid());
    }
    
    public function testSetGetStringBid()
    {
        $this->batchEntity->setBid('123');
        $this->assertEquals('123', $this->batchEntity->getBid());
    }
    
    public function testSetGetToken()
    {
        $this->batchEntity->setToken('Token123');
        $this->assertEquals('Token123', $this->batchEntity->getToken());
    }
    
    public function testSetGetTimestamp()
    {
        $time = time();
        $this->batchEntity->setTimestamp($time);
        $this->assertEquals($time, $this->batchEntity->getTimestamp());
    }
    
    public function testSetGetData()
    {
        $data = 'some data 123456';
        $this->batchEntity->setData($data);
        $this->assertEquals($data, $this->batchEntity->getData());
    }
}