<?php
namespace BatchManager\Event;

use Zend\EventManager\Event;
use BatchManager\Entity\BatchInterface;
use BatchManager\Entity\Batch;
use AssetManager\Exception\InvalidArgumentException;

class BatchEvent extends Event
{
    /**
     * Batch events triggered by eventmanager
     */
    CONST EVENT_BATCH_CONFIG        = 'batchConfig';
    CONST EVENT_BATCH_START         = 'batch.start';
    CONST EVENT_BATCH_WAKEUP        = 'batch.wakeup';
    CONST EVENT_BATCH_PROCESS       = 'batch.process';
    CONST EVENT_BATCH_SLEEP         = 'batch.sleep';
    CONST EVENT_BATCH_FINISHED      = 'batch.finished';
    CONST EVENT_BATCH_SHUTDOWN      = 'batch.shutdown';
    
    /**
     * 
     * @var \BatchManager\Entity\BatchInterface
     */
    protected $batch;
    
    /**
     * @var mixed
     */
    protected $startTime;
    
    /**
     * 
     * @var boolean
     */
    protected $progressive = true;
    
    /**
     * If the batch is loaded or not
     * 
     * @var boolean
     */
    protected $batchLoaded = false;
    
    /**
     * Maximum number of elements
     * If this is set it can be used to calculate percentage
     * 
     * @var integer
     */
    protected $min = 0;
    
    /**
     * Current number of elements already processed
     * If this is set it can be used to calculate percentage
     *
     * @var integer
     */
    protected $current;
    
    /**
     * Maximum number of elements
     * If this is set it can be used to calculate percentage
     * 
     * @var integer
     */
    protected $max = 100;
    
    
    /**
     * The percentage of the operations that has been finished
     * 
     * @var mixed 
     */
    protected $percentage = 0;
    
    /**
     * 
     * @var string the current message to be send to the client
     */
    protected $currentMessage;
    
    /**
     * 
     * @return number
     */
    public function getMin()
    {
        return $this->min;
    }

    /**
     * 
     * @return number
     */
    public function getCurrent()
    {
        return $this->current;
    }

    /**
     * 
     * @return number
     */
    public function getMax()
    {
        return $this->max;
    }

    /**
     * 
     * @param integer $min
     * @return \BatchManager\Event\BatchEvent
     */
    public function setMin($min)
    {
        // enforcing strong typing
        if (!is_numeric($min)) {
            throw new InvalidArgumentException("$min must be integer");
        }
        $this->min = $min;
        return $this;
    }

    /**
     * 
     * @param integer $current
     * @return \BatchManager\Event\BatchEvent
     */
    public function setCurrent($current)
    {
        // enforcing strong typing
        if (!is_numeric($current)) {
            throw new InvalidArgumentException("$current must be integer");
        }
        $this->current = $current;
        return $this;
    }

    /**
     * 
     * @param integer $max
     * @return \BatchManager\Event\BatchEvent
     */
    public function setMax($max)
    {
        // enforcing strong typing
        if (!is_numeric($max)) {
            throw new InvalidArgumentException("$max must be integer");
        }
        // max can't be bigger smaller than min
        if ($max < $this->min) {
            throw new InvalidArgumentException("Maximum can't be smaller than minimum");
        }
        $this->max = $max;
        return $this;
    }
    
    public function updatePercentage($current = null)
    {
        // if current was given we update our current
        if (null !== $current) {
            $this->setCurrent($current);
        }
        if ($this->getCurrent() == $this->getMax()) {
            // done
            $this->setPercentage(100);
        } else {
            $total = $this->getMax();
            $current = $this->getCurrent();
            
            // @see https://api.drupal.org/api/drupal/includes!batch.inc/function/_batch_api_percentage/7
            $decimalPlaces = max(0, floor(log10($total / 2.0)) - 1);
            do {
                // Calculate the percentage to the specified number of decimal places.
                $percentage = sprintf('%01.' . $decimalPlaces . 'f', round($current / $total * 100, $decimalPlaces));
                // When $current is an integer, the above calculation will always be
                // correct. However, if $current is a floating point number (in the case
                // of a multi-step batch operation that is not yet complete), $percentage
                // may be erroneously rounded up to 100%. To prevent that, we add one
                // more decimal place and try again.
                $decimalPlaces++;
            } while ($percentage == '100');
            $this->setPercentage((int)$percentage);
        }
        
    }

    /**
     * 
     * @return BatchInterface
     */
    public function getBatch()
    {
        if (null === $this->batch) {
            $this->setBatch(new Batch());
        }
        return $this->batch;
    }

    /**
     * 
     * @param BatchInterface $batch
     * @return \BatchManager\Event\BatchEvent
     */
    public function setBatch(BatchInterface $batch)
    {
        $this->setParam('batch', $batch);
        $this->batch = $batch;
        return $this;
    }

    /**
     *  
     * @return bool
     */
    public function isStarted()
    {
        $timestamp = $this->getBatch()->getTimestamp();
        return !empty($timestamp);
    }
    
    /**
     * Does the batch is going to be processed in more than one step (progressive)
     * or in one go (not progressive)
     *  
     * @return boolean the $progressive
     */
    public function isProgressive($flag = null)
    {
        if ($flag !== null) {
            $this->progressive = (bool) $flag;
        }
        return $this->progressive;
    }
    
    /**
     * 
     * @return mixed
     */
    public function getPercentage()
    {
        return $this->percentage;
    }
    
    /**
     * 
     * @param mixed $percentage
     * @return \BatchManager\Event\BatchEvent
     */
    public function setPercentage($percentage)
    {
        $this->percentage = (int) $percentage;
        return $this;
    }
    
    /**
     * 
     * @return string
     */
    public function getCurrentMessage()
    {
        return $this->currentMessage;
    }
    
    /**
     * 
     * @param unknown $message
     * @return \BatchManager\Event\BatchEvent
     */
    public function setCurrentMessage($message)
    {
        $this->currentMessage = (string)$message;
        return $this;
    }

    /**
     * @return mixed the $startTime
     */
    public function getStartTime()
    {
        return $this->startTime;
    }
    
    /**
     * 
     * @param mixed $time
     * @return \BatchManager\Event\BatchEvent
     */
    public function setStartTime($time)
    {
        $this->startTime = $time;
        return $this;
    }
    
    /**
     * Does the batch is fully loaded and ready to be
     * processed by batch listeners?
     * 
     * @param string $flag
     * @return boolean
     */
    public function isBatchLoaded($flag = null)
    {
        if (null !== $flag) {
            $this->batchLoaded = (bool)$flag;
        }
        return $this->batchLoaded;
    }
    
    /**
     * Does the event represent an error response?
     *
     * @return bool
     */
    public function isError()
    {
        return (bool) $this->getParam('error', false);
    }
    
    /**
     * Set the error message (indicating error in some listener)
     * 
     * @param mixed $message
     * @return \BatchManager\Event\BatchEvent
     */
    public function setError($message)
    {
        $this->setParam('error', $message);
        return $this;
    }
    
    /**
     * Retrieve the error message, if any
     *
     * @return string
     */
    public function getError()
    {
        return $this->getParam('error', '');
    }

    
}