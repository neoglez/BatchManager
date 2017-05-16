<?php
namespace BatchManager\Listener;

use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use BatchManager\Event\BatchEvent;
use BatchManager\Persister\BatchPersisterInterface;

class ShutdownBatchListener extends AbstractListenerAggregate
{

    /**
     *
     * @var $batchMapper BatchPersisterInterface;
     */
    protected $batchMapper;

    /**
     * ShutdownBatchListener constructor.
     * @param BatchPersisterInterface $mapper
     */
    public function __construct(BatchPersisterInterface $mapper)
    {
        $this->batchMapper = $mapper;
    }

    /**
     * @param $bm
     * @return $this
     */
    public function setBatchMapper($bm)
    {
        $this->batchMapper = $bm;
        return $this;
    }

    /**
     *
     * @return BatchPersisterInterface the $batchMapper
     */
    public function getBatchMapper()
    {
        return $this->batchMapper;
    }

    /**
     * Attach one or more listeners
     *
     * Implementors may add an optional $priority argument; the EventManager
     * implementation will pass this to the aggregate.
     *
     * @param EventManagerInterface $events
     *
     * @return void
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->listeners[] = $events->attach(BatchEvent::EVENT_BATCH_SHUTDOWN, array($this, 'onBatchShutdown'), -1000);
    }

    public function onBatchShutdown(BatchEvent $event)
    {
        $batch = $event->getBatch();
        // only can save when bid and token are set
        if ($batch->getBid() && $batch->getToken()) {
            try {
                $this->getBatchMapper()->persistBatch($batch);
            } catch (\Exception $e) {
                // ignore for now
                throw $e;
            }
        }
    }
}
