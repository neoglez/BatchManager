<?php
namespace BatchManager\Persister;

use BatchManager\Entity\BatchInterface;

interface BatchPersisterInterface
{
    public function persistBatch(BatchInterface $batch);
    
    public function retreiveBatch($batchId, $token);
}
