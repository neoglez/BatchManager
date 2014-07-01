# Basic use

At the most basic use you'll be doing four things:

1. Create a listener (or listener aggregate) to listen to one or more events triggered by the `BatchManager`.
2. At the convenient time attach the listener you create on 1. to the Application event manager.
3. Set a mark (e.g. URL parameter) and redirect to the `BatchController::startAction`.

We are going to use an example to explain the steps: Let's generate 500 000 users.

## Example Generate 500 000 users and save it to a csv file.

### Create a listener aggregate that listen to the BatchEvent::EVENT_BATCH_PROCESS and the BatchEvent::EVENT_BATCH_FINISHED events

```php
namespace Application\Listener;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use BatchManager\Event\BatchEvent;

class GenerateUserListener extends AbstractListenerAggregate
{
    public function attach(EventManagerInterface $events)
    {
        /*@var $sharedEvents Zend\EventManage\SharedEventManagerInterface */
        $sharedEvents = $events->getSharedManager();
        $sharedEvents->attach(
            'BatchManager\Service\BatchManager',
            BatchEvent::EVENT_BATCH_PROCESS,
            array($this, 'onBatchProcess')
        );
        $sharedEvents->attach(
            'BatchManager\Service\BatchManager',
            BatchEvent::EVENT_BATCH_FINISHED,
            array($this, 'setMessage')
        );
    }
    
    // more here
}
```

### Implement your business logic

```php
public function onBatchProcess(BatchEvent $event)
    {
        $filename = 'data/generated-user.csv';
        $totalAmount = 500000;
        
        // we are going to generate 10007 users per batch
        $batchSize = 10007;
        
        $batch = $event->getBatch();
        
        // we can save data/state information into this variable
        // between the batch
        $data = $batch->getData();
        
        if (empty($data) || empty($data['all_users']) || empty($data['processed_users'])) {
            $data = is_array($data) ?: array();
            $data['all_users'] = $totalAmount;
            
            // open the file and place the pointer at the begining to write the first info;
            $fhandle = fopen($filename, 'w+');
            if (!$fhandle) {
                $event->setError("File can't be created");
                return;
            }
            for ($i = 1; $i <= $batchSize; $i++) {
                $username = $this->generateUsername($i);
                $userEmail = $this->generateUserEmail($i);
                $userPassword = $this->generateStrongPassword();
                $userRow = $username . ';' . $userEmail . ';' . $userPassword . PHP_EOL;
                fwrite($fhandle, $userRow);
            }
            // close the file
            fclose($fhandle);
            
            $data['processed_users'] = $batchSize;
            $percentage = ($data['processed_users']/$data['all_users']) * 100;
            
            // we set ourself the percentage but we could have set max, min and current
            // and call updatePercentage
            $event->setPercentage($percentage);
            $event->setCurrentMessage('Processing the first 10007 users out of 500000');
        } else {
            $rest = $data['all_users'] - $data['processed_users'];
            $start = $data['processed_users'] + 1;
            
            if ($rest >= $batchSize) {
                $limit = $start + $batchSize;
            } else {
                $limit = $totalAmount;
            }
            // open the file and place the pointer at the END to write another 10000
            // or what is left.
            $fhandle = fopen($filename, 'a+');
            if (!$fhandle) {
                $event->setError("File not accesible");
                return;
            }
            for ($i = $start; $i <= $limit; $i++) {
                $username = $this->generateUsername($i);
                $userEmail = $this->generateUsername($i);
                $userPassword = $this->generateStrongPassword();
                $userRow = $username . ';' . $userEmail . ';' . $userPassword . PHP_EOL;
                fwrite($fhandle, $userRow);
            }
            // close the file
            fclose($fhandle);
            $data['processed_users'] = $limit;

            $percentage = ($data['processed_users']/$data['all_users']) * 100;
            $event->setPercentage($percentage);
            $event->setCurrentMessage(
                            sprintf('%d users have been processed out of %d', 
                            $data['processed_users'], 
                            $data['all_users']));
        }
        // set the data so that the batch can persist it
        $batch->setData($data);
        $event->setBatch($batch);
    }
```
### At the convinient time attach the listener you create to the Application event manager.

The "convenient time" refers here to the event where you have enough information to decide when to attach you listener given that you may have several batch listeners doing different things.

```php
class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
        
        // register our listener at dispatching time with high priority
        // to select the batch listener we want to activate for the
        // corresponding controller
        $eventManager->attach(MvcEvent::EVENT_DISPATCH, array($this, 'registerBatchListeners'), 1000);
    }
    
    // more here
    
    public function registerBatchListeners(MvcEvent $e)
    {
        $controller = $e->getRouteMatch()->getParam('controller');
        $eventManager = $e->getApplication()->getEventManager();
        
        if ($controller == 'BatchManager\Controller\Batch') {
            $caller = $e->getRequest()->getQuery('caller');
            if (!$caller) {
                // can't find the controller who called me :(
                return;
            }
            if ($caller == 'Application\Controller\GenerateController') {
                // attach listener (wich is a ListenerAggregate) to batch manager
                $generateListener = 'Application\Listener\GenerateUserListener';
                $generateListener = $e->getApplication()->getServiceManager()->get($generateListener);
                $eventManager->attach($generateListener);
            }
            if ($caller == 'Application\Controller\ImportController') {
                // attach listener (wich is a ListenerAggregate) to batch manager
                $importListener = 'Application\Listener\ImportUserListener';
                $importListener = $e->getApplication()->getServiceManager()->get($importListener);
                $eventManager->attach($importListener);
            }
        }
    }
    
    public function setMessage(BatchEvent $event)
    {
        if (!$event->isError()) {
            $data = $event->getBatch()->getData();
            if ($data['all_users'] == $data['processed_users']) {
                $message = 'All ' . $data['all_users'] . ' were successfully generated';
            } else {
                $message = 'Only' . $data['processed_users'] . ' out of ' . $data['all_users'] . ' were successfully generated';
            }
            $event->setCurrentMessage($message);
        }
        
    }
}
```

### Set a mark (e.g. URL parameter) and redirect to the `BatchController::startAction`

Now you can just set the `caller` parameter in the url, redirect to the startAction and let the BatchManager do his work.

```php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class GenerateController extends AbstractActionController
{
    public function indexAction()
    {
        return new ViewModel();
    }
    
    public function usersAction()
    {
        // Here we could do some more processing...
        // we redirect for processing to the batch controller
        // but we have to set a mark in the request so the listeners
        // know when to act.
        // Be aware you shouldn't disclosure any information related with your code (here __CLASS__)!
        // This is just to show how a marker can be set!
        $query = array('caller' => __CLASS__);
        return $this->redirect()->toRoute('batch', 
            array('action' => 'start'), 
            array('query' => $query));
    }
    
    // more here
}
```
