<?php
namespace Let\Db;

use PDO;
use Yaf\Registry;

class Db extends PDO
{
    public static $instance = [];

    public function __construct($target = 'master')
    {
        $dsn = 'mysql:host='.Registry::get('Config')->mysql->{$target}['host'];
        $dsn .= ';port='.Registry::get('Config')->mysql->{$target}['port'];
        $dsn .= ';dbname='.Registry::get('Config')->mysql->{$target}['dbname'];
        $dsn .= ';charset='.Registry::get('Config')->mysql->{$target}['charset'];
        parent::__construct(
            $dsn,
            Registry::get('Config')->mysql->{$target}['username'],
            Registry::get('Config')->mysql->{$target}['passwd'],
            Registry::get('Config')->mysql->{$target}['options']->toArray()
        );
    }

    public function __destruct()
    {
        //
    }

    public static function getInstance($target = 'master', $key = 'default'): self
    {
        if (isset(self::$instance[$target][$key])) {
            return self::$instance[$target][$key];
        }
        return self::$instance[$target][$key] = new self($target);
    }

    /**
     * @param string $statement
     * @param array $parameters
     * @param int $type
* {
*       \PDO::FETCH_KEY_PAIR   返回 以第一列为KEY，第二列为VALUE的 数据结构，如果KEY重复，则自动去重且保留最后一个KEY的VALUE
*       \PDO::FETCH_UNIQUE     返回 以第一列为KEY，其余列为数组VALUE的 数据结构，如果KEY重复，则自动去重且保留最后一个KEY的数组VALUE
*       \PDO::FETCH_GROUP      返回 以第一列为KEY，其余列为二数组VALUE的 数据结构，如果KEY重复，则其值自动加入二维数组中
* }
     * @return array
     */
    public function fetchAll(string $statement, array $parameters = [], $type = \PDO::FETCH_ASSOC): array
    {
        $rs = $this->_query($statement, $parameters);
        $results = $rs->fetchAll($type);
        $rs->closeCursor();
        $rs = null;
        
        return $results;
    }

    /**
     * 返回第一行数据组成的一维数组
     *
     * @param string $statement
     * @param array $parameters            
     * @param int $type            
     * @return mixed[array|false]
     */
    public function fetch($statement, array $parameters = [], $type = \PDO::FETCH_ASSOC)
    {
        $rs = $this->_query($statement, $parameters);
        $results = $rs->fetch($type);
        $rs->closeCursor();
        $rs = null;
        
        return $results;
    }

    /**
     * 返回第一行指定索引列的值
     *
     * @param string $statement
     * @param array $parameters            
     * @param int $column            
     * @return mixed[string|false]
     */
    public function fetchColumn($statement, array $parameters = [], $column = 0)
    {
        $rs = $this->_query($statement, $parameters);
        $results = $rs->fetchColumn($column);
        $rs->closeCursor();
        $rs = null;
        
        return $results;
    }

    /**
     * @param string $statement
     * @param array $parameters
     * @return array
     */
    public function insert($statement, $parameters = []): array
    {
        return [
            'rowCount' => $this->_query($statement, $parameters, $parameters)->rowCount(),
            'lastInsertId' => $this->lastInsertId()
        ];
    }

    /**
     * @param string $statement
     * @param array $parameters
     * @return int
     */
    public function update($statement, $parameters = []): int
    {
        return $this->_query($statement, $parameters)->rowCount();
    }

    /**
     * @param string $statement
     * @param array $parameters
     * @return int
     */
    public function delete($statement, $parameters = []): int
    {
        return $this->_query($statement, $parameters)->rowCount();
    }

    /**
     * @param string $statement
     * @param array $parameters
     * @return PDOStatement
     */
    private function _query(string $statement, array $parameters = [])
    {
        $rs = $this->prepare($statement);
        if ($rs) {
            foreach ($parameters as $index => $value) {
                switch (1) {
                    case is_int($value):
                        $type = \PDO::PARAM_INT;
                        break;
                    case is_bool($value):
                        $type = \PDO::PARAM_BOOL;
                        break;
                    case is_null($value):
                        $type = \PDO::PARAM_NULL;
                        break;
                    default:
                        $type = \PDO::PARAM_STR;
                        break;
                }
                $rs->bindValue(($index + 1), $value, $type);
            }
            
            $rs->execute();
            return $rs;
        } else {
            throw new \Exception($statement);
        }
    }
}