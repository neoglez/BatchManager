<?php
namespace BatchManager\Entity;

interface BatchInterface
{
    /**
     *
     * @return mixed the batch ID
     */
    public function getBid();

    /**
     *
     * @param mixed $batchId 
     */
    public function setBid($batchId);
    
    /**
     * 
     * @return string the token.
     */
    public function getToken();
    
    
    /**
     * 
     * @param string $token to be set.
     * The generated token should be based on the session ID of the current user.
     */
    public function setToken($token);
    
    /**
     *
     * @return string the timestamp.
     */
    public function getTimestamp();
    
    
    /**
     *
     * @param integer $timestamp a Unix timestamp indicating when this batch 
     * was submitted for processing.
     */
    public function setTimestamp($timestamp);
    
    /**
     *
     * @return mixed the data.
     */
    public function getData();
    
    
    /**
     *
     * @param mixed $data contains the processing data for the batch.
     */
    public function setData($data);
}
