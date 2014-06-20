<?php
namespace BatchManager\Service;

use Zend\EventManager\EventManagerAwareInterface;

/**
 * Batch manager interface
 */
interface BatchManagerInterface extends EventManagerAwareInterface
{
    public function getOptions();
    
    public function startBatch();    
    
    public function processBatch();
    
    public function finishBatch();

}
