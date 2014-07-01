## Events triggered by the `BatchManager`

### `BatchEvent::EVENT_BATCH_START`

This event is triggered only once when the BatchManager::startBatch is called. Listeners on this event should initialize the required parameters (ID and TOKEN) on the batch to make it available to other requests. The BatchManager Module comes with one listener that implements the logic for most use cases: the InitBatchParamsListener.

### `BatchEvent::EVENT_BATCH_WAKEUP`



### `BatchEvent::EVENT_BATCH_PROCESS`



### `BatchEvent::EVENT_BATCH_FINISHED`



### `BatchEvent::EVENT_BATCH_SHUTDOWN`
