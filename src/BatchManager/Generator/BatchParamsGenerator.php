<?php
namespace BatchManager\Generator;

use BatchManager\Generator\BatchParamsGeneratorInterface;
use Zend\Session\Container as SessionContainer;
use Zend\Crypt\Hmac;

class BatchParamsGenerator implements BatchParamsGeneratorInterface
{
    /**
     * 
     * @var string
     */
    protected $secretKey;
    
    /**
     * 
     * @var integer|string
     */
    protected $generatedBatchId;
    
    /**
     * 
     * @param string $secretKey
     */
    public function __construct($secretKey)
    {
        if (empty($secretKey)) {
            throw new \Exception("Secret key can't be empty");
        }
        $this->secretKey = $secretKey;
    }
    
    /**
     * (non-PHPdoc)
     * @see \BatchManager\Generator\BatchParamsGeneratorInterface::generateBatchId()
     */
    public function generateBatchId()
    {
        $this->generatedBatchId = uniqid();
        return $this->generatedBatchId;
    }
    
    /**
     * (non-PHPdoc)
     * @see \BatchManager\Generator\BatchParamsGeneratorInterface::generateToken()
     */
    public function generateToken($batchId = null)
    {
        // generate a token based on the session
        /*@var $sessionManager \Zend\Session\SessionManager */
        $sessionManager = SessionContainer::getDefaultManager();
        
        $sessionId = $sessionManager->getId();
        // in this case this is the data
        $data = $batchId ? $batchId : $this->generatedBatchId;
        // the key
        $key = $sessionId . $this->secretKey;
        
        $binaryHmac = Hmac::compute($key, 'sha256', $data, Hmac::OUTPUT_BINARY);
        return base64_encode($data);
    }
}