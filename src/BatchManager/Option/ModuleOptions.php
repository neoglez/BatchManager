<?php
namespace BatchManager\Option;

use Zend\Stdlib\AbstractOptions;

class ModuleOptions extends AbstractOptions
{
    
    /**
     * If you use the batch mapper provided by this module this is the class
     * used as a prototype
     * 
     * @var string
     */
    protected $batchEntityClass = 'BatchManager\Entity\Batch';
    
    /**
     * If you use the batch mapper provided by this module this is the table's
     * name in the database.
     * 
     * @var string
     */
    protected $tableName = 'batch';
    
    /**
     * Should the batch controller redirect to the process action right after
     * the batch had been started?
     * 
     * @var boolean
     */
    protected $processBatchAfterStart = false;
    
    /**
     * The route used to process the batch
     * 
     * @var string
     */
    protected $processRoute = 'batch';
    
    /**
     * The accion used to process the batch
     * 
     * @var string
     */
    protected $processAction = 'process';
    
    /**
     * The route used to redirect when the batch is finished
     *
     * @var string
     */
    protected $finishedRoute = 'batch';
    
    /**
     * The accion used to redirect when the batch is finished
     * 
     * @var string
     */
    protected $finishedAction = 'finished';
    
    /**
     * Key in the request's parameters whose value is the
     * batch id
     * 
     * @var string
     */
    protected $idKeyInRequest = 'batchId';
    
    
    /**
     * This key will be used to generate the token for the batch
     * 
     * @var string
     */
    protected $secretSecretKey = '123456';
    
    /**
     * If you are using the MetaRefreshAdapter for the ProgressBar then
     * this is the amount of seconds in the string
     * <meta http-equiv="refresh" content="0; url=http://example.com/">
     * 
     * @var integer
     */
    protected $metaRefreshSecons = 0;
    
    /**
     * If a cookie with the name set in $cookieKey is found in the request
     * the batch manager will attemp to process the batch via ajax.
     * 
     * @var bool
     */
    protected $useContentNegociation = true;
    
    /**
     * This is the key used to query the cookies to determine if the user
     * has javascript enabled. 
     * 
     * @var string
     */
    protected $cookieKey = 'has_js';
    
    /**
     * 
     * @return boolean
     */
    public function getUseContentNegociation()
    {
        return $this->useContentNegociation;
    }

    /**
     * 
     * @return string
     */
    public function getCookieKey()
    {
        return $this->cookieKey;
    }

    /**
     * 
     * @param bool $useContentNegociation
     * @return \BatchManager\Option\ModuleOptions
     */
    public function setUseContentNegociation($useContentNegociation)
    {
        $this->useContentNegociation = (bool)$useContentNegociation;
        return $this;
    }

    /**
     * 
     * @param string $cookieKey
     * @return \BatchManager\Option\ModuleOptions
     */
    public function setCookieKey($cookieKey)
    {
        $this->cookieKey = (string)$cookieKey;
        return $this;
    }

    /**
     * 
     * @return number
     */
    public function getMetaRefreshSecons()
    {
        return $this->metaRefreshSecons;
    }

    /**
     * 
     * @param unknown $metaRefreshSecons
     * @return \BatchManager\Option\ModuleOptions
     */
    public function setMetaRefreshSecons($metaRefreshSecons)
    {
        $this->metaRefreshSecons = $metaRefreshSecons;
        return $this;
    }

    /**
     * 
     * @return boolean the $processBatchAfterStart
     */
    public function getProcessBatchAfterStart()
    {
        return $this->processBatchAfterStart;
    }

    /**
     * @return the $idKeyInRequest
     */
    public function getIdKeyInRequest()
    {
        return $this->idKeyInRequest;
    }


    /**
     * 
     * @return string
     */
    public function getSecretSecretKey()
    {
        return $this->secretSecretKey;
    }

    /**
     * 
     * @param string $secretSecretKey
     * @return \BatchManager\Option\ModuleOptions
     */
    public function setSecretSecretKey($secretSecretKey)
    {
        $this->secretSecretKey = $secretSecretKey;
        return $this;
    }

    /**
     * 
     * @param string $idKeyInRequest
     * @return \BatchManager\Option\ModuleOptions
     */
    public function setIdKeyInRequest($idKeyInRequest)
    {
        $this->idKeyInRequest = $idKeyInRequest;
        return $this;
    }

    /**
     * 
     * @return string the $processRoute
     */
    public function getProcessRoute()
    {
        return $this->processRoute;
    }

    /**
     * 
     * @return string the $processAction
     */
    public function getProcessAction()
    {
        return $this->processAction;
    }

    /**
     * 
     * @return string the $finishedRoute
     */
    public function getFinishedRoute()
    {
        return $this->finishedRoute;
    }

    /**
     * 
     * @return string the $finishedAction
     */
    public function getFinishedAction()
    {
        return $this->finishedAction;
    }

    /**
     * 
     * @param boolean $processBatchAfterStart
     * @return \BatchManager\Option\ModuleOptions
     */
    public function setProcessBatchAfterStart($processBatchAfterStart)
    {
        $this->processBatchAfterStart = (bool)$processBatchAfterStart;
        return $this;
    }

    /**
     * 
     * @param string $processRoute
     * @return \BatchManager\Option\ModuleOptions
     */
    public function setProcessRoute($processRoute)
    {
        $this->processRoute = $processRoute;
        return $this;
    }

    /**
     * 
     * @param string $processAction
     * @return \BatchManager\Option\ModuleOptions
     */
    public function setProcessAction($processAction)
    {
        $this->processAction = $processAction;
        return $this;
    }

    /**
     * 
     * @param string $finishedRoute
     * @return \BatchManager\Option\ModuleOptions
     */
    public function setFinishedRoute($finishedRoute)
    {
        $this->finishedRoute = $finishedRoute;
        return $this;
    }

    /**
     * 
     * @param string $finishedAction
     * @return \BatchManager\Option\ModuleOptions
     */
    public function setFinishedAction($finishedAction)
    {
        $this->finishedAction = $finishedAction;
        return $this;
    }

    /**
     * 
     * @return the $tableName
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * @param string $tableName
     * @return ModuleOptions
     */
    public function setTableName($tableName)
    {
        $this->tableName = $tableName;
        return $this;
    }

    /**
     * 
     * @return the $batchEntityClass
     */
    public function getBatchEntityClass()
    {
        return $this->batchEntityClass;
    }

    /**
     * 
     * @param string $batchEntityClass
     * @return ModuleOptions
     */
    public function setBatchEntityClass($batchEntityClass)
    {
        $this->batchEntityClass = $batchEntityClass;
        return $this;
    }
    
    /**
     * A shortcut to all options related to the process route configuration
     * 
     * @return array
     */
    public function getProcessRouteConfigArray()
    {
        return array(
            $this->getProcessRoute(),
            $this->getProcessAction(),
            $this->getIdKeyInRequest()
        );
    }
    
    /**
     * A shortcut to all options related to the finished route configuration
     * 
     * @return array
     */
    public function getFinishedRouteConfigArray()
    {
        return array(
            $this->getFinishedRoute(),
            $this->getFinishedAction(),
            $this->getIdKeyInRequest()
        );
    }

}