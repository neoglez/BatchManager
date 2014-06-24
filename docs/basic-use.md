# Basic use

At the most basic use you'll be doing two things:

1. Create a listener to listen to one or more events triggered by the BatchManager.
2. Attach the listener to the SharedEventManager.

We are going to use an example to explain the steps: Let's generate 500 000 users.

## Example Generate 500 000 users and save it to a csv file.

### Create a listener that listen to the BatchEvent::EVENT_BATCH_PROCESS event

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
        
        // we are going to generate 10000 users per batch
        $batchSize = 10000;
        
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
                $userEmail = $this->generateUsername($i);
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
            $event->setCurrentMessage('Processing the first 10000 users out of 500000');
        } else {
            $rest = $data['all_users'] - $data['processed_users'];
            $start = $data['processed_users'] + 1;
            
            if ($rest >= $batchSize) {
                $limit = $data['processed_users'] + $batchSize;
            } else {
                $limit = $totalAmount;
            }
            // open the file and place the pointer at the END to write another 5000
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
                            sprintf('%d users have been processed out of %d total', 
                            $data['processed_users'], 
                            $data['all_users']));
        }
        // set the data so that the batch can persist it
        $batch->setData($data);
        $event->setBatch($batch);
    }
```
