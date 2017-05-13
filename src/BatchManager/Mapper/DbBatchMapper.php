<?php
namespace BatchManager\Mapper;

use BatchManager\Entity\BatchInterface;
use BatchManager\Persister\BatchPersisterInterface;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\HydratingResultSet;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Hydrator\ClassMethods;
use Zend\Hydrator\HydratorInterface;

/**
 * Class mostly copied from https://github.com/ZF-Commons/ZfcBase/blob/master/src/ZfcBase/Mapper/AbstractDbMapper.php
 *
 * Class DbBatchMapper
 * @package BatchManager\Mapper
 */
class DbBatchMapper implements BatchPersisterInterface
{
    /**
     * @var Adapter
     */
    protected $dbAdapter;
    /**
     * @var Adapter
     */
    protected $dbSlaveAdapter;
    /**
     * @var HydratorInterface
     */
    protected $hydrator;
    /**
     * @var object
     */
    protected $entityPrototype;
    /**
     * @var HydratingResultSet
     */
    protected $resultSetPrototype;
    /**
     * @var Select
     */
    protected $selectPrototype;
    /**
     * @var Sql
     */
    private $sql;
    /**
     * @var Sql
     */
    private $slaveSql;
    /**
     * @var string
     */
    protected $tableName;
    /**
     * @var boolean
     */
    private $isInitialized = false;

    /**
     * Performs some basic initialization setup and checks before running a query
     * @throws \Exception
     */
    protected function initialize()
    {
        if ($this->isInitialized) {
            return;
        }
        if (!$this->dbAdapter instanceof Adapter) {
            throw new \Exception('No db adapter present');
        }
        if (!$this->hydrator instanceof HydratorInterface) {
            $this->hydrator = new ClassMethods();
        }
        if (!is_object($this->entityPrototype)) {
            throw new \Exception('No entity prototype set');
        }
        $this->isInitialized = true;
    }

    /**
     * @param string|null $table
     * @return Select
     */
    protected function getSelect($table = null)
    {
        $this->initialize();
        return $this->getSlaveSql()->select($table ?: $this->getTableName());
    }
    /**
     * @param Select $select
     * @param object|null $entityPrototype
     * @param HydratorInterface|null $hydrator
     * @return HydratingResultSet
     */
    protected function select(Select $select, $entityPrototype = null, HydratorInterface $hydrator = null)
    {
        $this->initialize();
        $stmt = $this->getSlaveSql()->prepareStatementForSqlObject($select);
        $resultSet = new HydratingResultSet($hydrator ?: $this->getHydrator(),
            $entityPrototype ?: $this->getEntityPrototype());
        $resultSet->initialize($stmt->execute());
        return $resultSet;
    }

    /**
     * @param $entity
     * @param null $tableName
     * @param HydratorInterface|null $hydrator
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    protected function insert($entity, $tableName = null, HydratorInterface $hydrator = null)
    {
        $this->initialize();
        $tableName = $tableName ?: $this->tableName;
        $sql = $this->getSql()->setTable($tableName);
        $insert = $sql->insert();
        $rowData = $this->entityToArray($entity, $hydrator);
        $insert->values($rowData);
        $statement = $sql->prepareStatementForSqlObject($insert);
        return $statement->execute();
    }

    /**
     * @param $entity
     * @param $where
     * @param null $tableName
     * @param HydratorInterface|null $hydrator
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    protected function update($entity, $where, $tableName = null, HydratorInterface $hydrator = null)
    {
        $this->initialize();
        $tableName = $tableName ?: $this->tableName;
        $sql = $this->getSql()->setTable($tableName);
        $update = $sql->update();
        $rowData = $this->entityToArray($entity, $hydrator);
        $update->set($rowData)
            ->where($where);
        $statement = $sql->prepareStatementForSqlObject($update);
        return $statement->execute();
    }

    /**
     * @param $where
     * @param null $tableName
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    protected function delete($where, $tableName = null)
    {
        $tableName = $tableName ?: $this->tableName;
        $sql = $this->getSql()->setTable($tableName);
        $delete = $sql->delete();
        $delete->where($where);
        $statement = $sql->prepareStatementForSqlObject($delete);
        return $statement->execute();
    }
    /**
     * @return string
     */
    protected function getTableName()
    {
        return $this->tableName;
    }
    /**
     * @return object
     */
    public function getEntityPrototype()
    {
        return $this->entityPrototype;
    }

    /**
     * @param $entityPrototype
     * @return $this
     */
    public function setEntityPrototype($entityPrototype)
    {
        $this->entityPrototype = $entityPrototype;
        $this->resultSetPrototype = null;
        return $this;
    }
    /**
     * @return Adapter
     */
    public function getDbAdapter()
    {
        return $this->dbAdapter;
    }

    /**
     * @param Adapter $dbAdapter
     * @return $this
     */
    public function setDbAdapter(Adapter $dbAdapter)
    {
        $this->dbAdapter = $dbAdapter;
        return $this;
    }
    /**
     * @return Adapter
     */
    public function getDbSlaveAdapter()
    {
        return $this->dbSlaveAdapter ?: $this->dbAdapter;
    }

    /**
     * @param Adapter $dbSlaveAdapter
     * @return $this
     */
    public function setDbSlaveAdapter(Adapter $dbSlaveAdapter)
    {
        $this->dbSlaveAdapter = $dbSlaveAdapter;
        return $this;
    }
    /**
     * @return HydratorInterface
     */
    public function getHydrator()
    {
        if (!$this->hydrator) {
            $this->hydrator = new ClassMethods(false);
        }
        return $this->hydrator;
    }

    /**
     * @param HydratorInterface $hydrator
     * @return $this
     */
    public function setHydrator(HydratorInterface $hydrator)
    {
        $this->hydrator = $hydrator;
        $this->resultSetPrototype = null;
        return $this;
    }
    /**
     * @return Sql
     */
    protected function getSql()
    {
        if (!$this->sql instanceof Sql) {
            $this->sql = new Sql($this->getDbAdapter());
        }
        return $this->sql;
    }

    /**
     * @param Sql $sql
     * @return $this
     */
    protected function setSql(Sql $sql)
    {
        $this->sql = $sql;
        return $this;
    }
    /**
     * @return Sql
     */
    protected function getSlaveSql()
    {
        if (!$this->slaveSql instanceof Sql) {
            $this->slaveSql = new Sql($this->getDbSlaveAdapter());
        }
        return $this->slaveSql;
    }

    /**
     * @param Sql $sql
     * @return $this
     */
    protected function setSlaveSql(Sql $sql)
    {
        $this->slaveSql = $sql;
        return $this;
    }
    /**
     * Uses the hydrator to convert the entity to an array.
     *
     * Use this method to ensure that you're working with an array.
     *
     * @param object $entity
     * @param HydratorInterface|null $hydrator
     * @return array
     */
    protected function entityToArray($entity, HydratorInterface $hydrator = null)
    {
        if (is_array($entity)) {
            return $entity; // cut down on duplicate code
        } elseif (is_object($entity)) {
            if (!$hydrator) {
                $hydrator = $this->getHydrator();
            }
            return $hydrator->extract($entity);
        }
        throw new \InvalidArgumentException('Entity passed to db mapper should be an array or object.');
    }
    
    /**
     * 
     * @param string $tableName
     * @return \BatchManager\Mapper\DbBatchMapper
     */
    public function setTableName($tableName)
    {
        $this->tableName = $tableName;
        return $this;
    }

    /**
     * @param BatchInterface $batch
     * @return BatchInterface
     */
    public function persistBatch(BatchInterface $batch)
    {
        $newBatch = !$batch->getBid();
        
        if ($newBatch) {
            // here the db has a chance to assign the batch an id
            // (e.g. autogenerated values)
            $this->insert($batch);
        } else {
            $where = array('bid = ?' => $batch->getBid());
            $select = $this->getSelect()->where($where);
            $result = $this->select($select);
            if ($result->count()) {
                // the batch already exist -> update
                $this->update($batch, $where);
            } else {
                $this->insert($batch);
            }
        }
        return $batch;
    }

    /**
     * @param $batchId
     * @param $token
     * @return bool|object
     */
    public function retreiveBatch($batchId, $token)
    {
        $limit = 1;
        $where = array('bid = ?' => $batchId, 'token = ?' => $token);
        $select = $this->getSelect()
                       ->where($where)
                       ->limit($limit);
        $result = $this->select($select);
        if ($result->count()) {
            return $result->current();
        }
        return false;
    }
}
