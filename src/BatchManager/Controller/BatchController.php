<?php

namespace BatchManager\Controller;

use BatchManager\Option\ModuleOptions;
use BatchManager\Service\BatchManager;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Http\Response as HTTPResponse;

class BatchController extends AbstractActionController
{
    /**
     * @var BatchManager
     */
    protected $batchManager;

    /**
     * @var ModuleOptions
     */
    protected $moduleOptions;

    public function __construct(BatchManager $batchManager, ModuleOptions $options)
    {
        $this->batchManager = $batchManager;
        $this->moduleOptions = $options;
    }

    public function indexAction()
    {
        return new ViewModel();
    }

    public function startAction()
    {
        $batchManager = $this->batchManager;
        $batch = $batchManager->startBatch()->getBatch();

        $moduleOptions = $this->moduleOptions;

        list($pRoute, $pAction, $bidKey) = $moduleOptions->getProcessRouteConfigArray();
        $params = [
            'action' => $pAction,
            $bidKey => $batch->getBid(),
        ];

        $queryParams = $this->params()->fromQuery();
        $queryParams = !empty($queryParams) ? ['query' => $queryParams] : null;

        list($fRoute, $fAction, $bidKey) = $moduleOptions->getFinishedRouteConfigArray();
        $fparams = [
            'action' => $fAction,
            $bidKey => $batch->getBid(),
        ];

        if (!$moduleOptions->getProcessBatchAfterStart()) {
            $model = [
                'percentage' => $batchManager->getPercentage(),
                'message' => $batchManager->getCurrentMessage(),
                'metaRefreshSeconds' => $moduleOptions->getMetaRefreshSecons(),
                'refreshUrl' => $this->url()->fromRoute($pRoute, $params, $queryParams),
                'finishedUrl' => $this->url()->fromRoute($fRoute, $fparams, $queryParams),
                'batchId' => $batch->getBid(),
            ];

            return new ViewModel($model);
        } else {
            return $this->redirect()->toRoute($pRoute, $params, $queryParams);
        }
    }


    public function processAction()
    {
        // Get the Batch manager and process the batch
        $batchManager = $this->batchManager;
        $moduleOptions = $this->moduleOptions;

        // If we found batch key in request object we set them in the batch
        $batchId = $this->params($moduleOptions->getIdKeyInRequest());

        $queryParams = $this->params()->fromQuery();
        $queryParams = !empty($queryParams) ? ['query' => $queryParams] : null;

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

        $pRoute = $moduleOptions->getProcessRoute();

        list($fRoute, $fAction, $bidKey) = $moduleOptions->getFinishedRouteConfigArray();
        $fParams = [
            'action' => $fAction,
            $bidKey => $batch->getBid(),
        ];

        $queryParams = $this->params()->fromQuery();
        $queryParams = !empty($queryParams) ? ['query' => $queryParams] : [];

        // the view script will take care of setting the meta tags
        $model = [
            'percentage' => $batchManager->getPercentage(),
            'message' => $batchManager->getCurrentMessage(),
            'metaRefreshSeconds' => $moduleOptions->getMetaRefreshSecons(),
            'refreshUrl' => $this->url()->fromRoute($pRoute, [], $queryParams, true),
            'batchId' => $batch->getBid(),
            'finished' => ($batchManager->getPercentage() >= 100),
        ];
        if ($batchManager->getPercentage() >= 100) {
            $model['refreshUrl'] = $this->url()->fromRoute($fRoute, $fParams, $queryParams);
        }
        return new ViewModel($model);
    }

    public function finishedAction()
    {
        // Get the Batch manager and process the batch
        $batchManager = $this->batchManager;

        if (!$batchManager->isError()) {
            $moduleOptions = $this->moduleOptions;

            // If we found batch key in request object we set them in the batch
            $batchId = $this->params($moduleOptions->getIdKeyInRequest());

            /*@var $batch \BatchManager\Entity\Batch */
            $batch = $batchManager->getBatch();

            if ($batchId) {
                $batch->setBid($batchId);
                $batchManager->setBatch($batch);
            }

            $result = $batchManager->finishBatch();
            // If someone return a response shortcut right away.
            // We enforce a HTTP response
            if ($result instanceof HTTPResponse) {
                return $result;
            }
        }

        $model = [
            'message' => $batchManager->getCurrentMessage(),
            'isError' => $batchManager->isError(),
            'finished' => ($batchManager->getPercentage() >= 100),
        ];
        return new ViewModel($model);
    }
}
