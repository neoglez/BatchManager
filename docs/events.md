## Events triggered by the `BatchManager`

### `BatchEvent::EVENT_BATCH_START`

This event is triggered only once when the `BatchManager::startBatch` is called. Listeners on this event should initialize the required parameters (ID and TOKEN) on the batch to make it available to other requests. The `BatchManager` Module comes with one listener that implements the logic for most use cases: the `InitBatchParamsListener`.

### `BatchEvent::EVENT_BATCH_WAKEUP`

This event is trigger every time that the batch is about to be processed. Once the request has finished the batch will be persisted for upcoming requests, that’s why listeners on this event should load the batch (e.g. from DB or any other storage) and set it on the event. The so called Token is also to be set at this time. The `BatchManager` Module comes with one listener that implements the logic for most use cases: the `InitBatchParamsListener`.

### `BatchEvent::EVENT_BATCH_PROCESS`

Listeners on this event should do "their real work" here e.g. implement business logic, set percentage and current message to be shown to the user and set data on the event that they may need for other requests. If a HTTP response is returned, execution will be stopped and the response will be returned.

### `BatchEvent::EVENT_BATCH_FINISHED`

This event is triggered only once when the `BatchManager:: finishBatch` is called. Listeners on this event can set the current message to show to the user or act when the batch has an error set. The BatchManager module comes with a listener to set the message to the error found in the event (if any): the `MessageOnErrorListener`.

### `BatchEvent::EVENT_BATCH_SHUTDOWN`

This event is triggered every time on php shutdown by `BatchManager::batchShutdown` (it’s registered with `register_shutdown_function()` when the `BatchManager` is created). Listeners on this event should persist batch data to storage. The `BatchManager` module comes with a listener that implements the logic: the `ShutdownBatchListener`.
