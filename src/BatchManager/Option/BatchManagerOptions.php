<?php
namespace BatchManager\Option;

use Zend\Stdlib\AbstractOptions;

class BatchManagerOptions extends AbstractOptions
{
    /**
     * the manager will stop processing the batch after this amount
     * of seconds
     * 
     * @var integer
     */
    protected $stopBatchProcessAfterXSeconds = 1000;
    
    /**
     * 
     * @return number
     */
    public function getStopBatchProcessAfterXSeconds()
    {
        return $this->stopBatchProcessAfterXSeconds;
    }

    /**
     * 
     * @param integer $seconds
     * @return \BatchManager\Option\BatchManagerOptions
     */
    public function setStopBatchProcessAfterXSeconds($seconds)
    {
        $this->stopBatchProcessAfterXSeconds = (int)$seconds;
        return $this;
    }

}