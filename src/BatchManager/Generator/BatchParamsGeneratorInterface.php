<?php
namespace BatchManager\Generator;

interface BatchParamsGeneratorInterface
{
    /**
     * Generate an identifier for a batch
     * @return integer|string 
     */
    public function generateBatchId();
    
    /**
     * Generates a HMAC token for a given batch identifer
     *
     * @param null|integer|string $batchId
     */
    public function generateToken($batchId = null);
}
