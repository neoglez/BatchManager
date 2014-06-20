<?php
namespace BatchManager\Entity;

use BatchManager\Entity\BatchInterface;

class Batch implements BatchInterface
{

    protected $bid;
    
    protected $token;
    
    protected $timestamp;
    
    protected $data;

    /**
     *
     * @return mixed the batch Id
     */
    public function getBid()
    {
        return $this->bid;
    }
    
    /**
     *
     * @param mixed $batchId
     */
    public function setBid($batchId)
    {
        $this->bid = $batchId;
        return $this;
    }
    
    /**
     *
     * @return string the token.
     */
    public function getToken()
    {
        return $this->token;
    }
    
    
    /**
     *
     * @param string $token to be set.
     * The generated token should be based on the session ID of the current user.
     */
    public function setToken($token)
    {
        $this->token = $token;
    }
    
    /**
     *
     * @return string the timestamp.
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }
    
    
    /**
     *
     * @param integer $timestamp a Unix timestamp indicating when this batch
     * was submitted for processing.
     */
    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;
        return $this;
    }
    
    /**
     *
     * @return mixed the data.
     */
    public function getData()
    {
        return $this->data;
    }
    
    
    /**
     *
     * @param mixed $data contains the processing data for the batch.
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }
    
}