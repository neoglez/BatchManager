<?php
namespace BatchManagerTest\Generator;

use BatchManager\Generator\BatchParamsGenerator;
use PHPUnit_Framework_TestCase;

class BatchParamsGeneratorTest extends PHPUnit_Framework_TestCase
{
    /**
     * 
     * @var \BatchManager\Generator\BatchParamsGenerator
     */
    protected $generator;
    
    public function setUp()
    {
        $secretKey = '123456';
        $this->generator = new BatchParamsGenerator($secretKey);
    }
    
    public function testThrowExceptionIfSecretKeyIsEmpty()
    {
        try {
            $generator = new BatchParamsGenerator('');
        } catch (\Exception $e) {
            $this->assertSame("Secret key can't be empty", $e->getMessage());
            return;
        }
        
        $this->fail('Expected exception was not thrown');
    }
    
    public function testCanGenerateNotEmptyBatchId()
    {
        $generatedBatchId = $this->generator->generateBatchId();
        $this->assertNotEmpty($generatedBatchId);
    }
    
    public function testCanGenerateNotEmptyBatchTokenForStringBatchId()
    {
        $batchId = '123456';
        $generatedToken = $this->generator->generateToken($batchId);
        $this->assertNotEmpty($generatedToken);
    }
    
    public function testCanGenerateNotEmptyBatchTokenForIntegerBatchId()
    {
        $batchId = 123456;
        $generatedToken = $this->generator->generateToken($batchId);
        $this->assertNotEmpty($generatedToken);
    }
}