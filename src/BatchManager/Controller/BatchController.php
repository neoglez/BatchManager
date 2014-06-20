<?php
namespace BatchManager\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Http\Response as HTTPResponse;

class BatchController extends AbstractActionController
{
    public function indexAction()
    {
        return new ViewModel();
    }
    
    public function startAction()
    {
        
        /*@var $batchManager \BatchManager\Service\BatchManager */
        $batchManager = $this->getServiceLocator()->get('batch_manager');
        $batch = $batchManager->startBatch()->getBatch();
        
        /*@var $moduleOptions \BatchManager\Option\ModuleOptions */
        $moduleOptions = $this->getServiceLocator()->get('batch_manager_module_options');
        
        list($pRoute, $pAction, $bidKey) = $moduleOptions->getProcessRouteConfigArray();
        $params = array(
            'action' => $pAction,
            $bidKey => $batch->getBid()
        );
        
        list($fRoute, $fAction, $bidKey) = $moduleOptions->getFinishedRouteConfigArray();
        $fparams = array(
            'action' => $fAction,
            $bidKey => $batch->getBid()
        );
        
        if (!$moduleOptions->getProcessBatchAfterStart()) {
            $model = array(
                'percentage' => $batchManager->getPercentage(),
                'message' => $batchManager->getCurrentMessage(),
                'metaRefreshSeconds' => $moduleOptions->getMetaRefreshSecons(),
                'refreshUrl' => $this->url()->fromRoute($pRoute, $params),
                'finishedUrl' => $this->url()->fromRoute($fRoute, $fparams),
                'batchId' => $batch->getBid()
            );
            
            return new ViewModel($model);
        } else {
            return $this->redirect()->toRoute($pRoute, $params);
        }
    }
    
    
    public function processAction()
    {
        // Get the Batch manager and process the batch
        /*@var $batchManager \BatchManager\Service\BatchManager */
        $batchManager = $this->getServiceLocator()->get('batch_manager');
        
        /*@var $moduleOptions \BatchManager\Option\ModuleOptions */
        $moduleOptions = $this->getServiceLocator()->get('batch_manager_module_options');
        
        // If we found batch key in request object we set them in the batch
        $batchId = $this->params($moduleOptions->getIdKeyInRequest());
        
        /*@var $batch \BatchManager\Entity\Batch */
        $batch = $batchManager->getBatch();
        
        if ($batchId) {
            $batch->setBid($batchId);
            $batchManager->setBatch($batch);
        }
        
        // Now process the batch
        $result = $batchManager->processBatch();
        
        // If someone return a response shortcut right away.
        // We enforce a HTTP response
        if ($result instanceof HTTPResponse) {
            return $result;
        }
        
        
        list($fRoute, $fAction, $bidKey) = $moduleOptions->getFinishedRouteConfigArray();
        $params = array(
            'action' => $fAction,
            $bidKey => $batch->getBid()
        );
        
        
        // the view script will take care of setting the meta tags
        $model = array(
            'percentage' => $batchManager->getPercentage(),
            'message' => $batchManager->getCurrentMessage(),
            'metaRefreshSeconds' => $moduleOptions->getMetaRefreshSecons(),
            'refreshUrl' => $this->url()->fromRoute($fRoute, $params),
            'batchId' => $batch->getBid(),
            'finished' => ($batchManager->getPercentage() >= 100)
        );
        return new ViewModel($model);
    }
    
    public function finishedAction()
    {
        
    }
}
