<?php
/**
 * @auth YuYong
 * @date 2019-06-26
 *
 */

namespace sdk\libs;

use PDO;
use Katzgrau\KLogger\Logger;

/**
 * MySQL
 * Class MysqlHelper
 * @package sdk\libs
 */
class MysqlHelper
{

    /**
     * 主机地址
     * @var string
     */
    private $db_host;

    /**
     * 主机端口
     * @var string
     */
    private $db_port;

    /**
     * 用户名
     * @var string
     */
    private $db_user;

    /**
     * 密码
     * @var string
     */
    private $db_pass;

    /**
     * 编码
     * @var string
     */
    private $chart_set = 'utf8mb4';

    /**
     * mysql connection 连接实例
     * @var object
     */
    private $connection = null;

    /**
     * mysql connection 实例
     * @var object
     */
    public static $_instance = null;


    /**
     * mysql 最后连接时间，对于长连接需要考虑超时的问题
     * @var int
     */
    private $last_connect_time = 0;


    /**
     * 出现gone way之后的重试次数
     */
    private $retry_times = 0;
    private $cur_use_master = true;// true:当前使用主进行操作,false:当前使用从进行操作
    private $all_use_master = false;// true:所有的操作都使用主,false:主从分离
    private $sql_log = [];
    private $last_connect_times = [];//实例最后活跃时间 以实例名称作为键
    private $all_connects = [];//实例连接列表 以实例名称作为键


    /**
     * 构造
     */
    protected function __construct()
    {
        $this->initConnection();
        return $this->connection;
    }


    /**
     * 禁止克隆
     */
    protected function __clone()
    {
        //Me not like clones! Me smash clones!
    }


    /**
     * 获取连接实例
     * @return null|MysqlHelper
     */
    public static function getInstance()
    {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }


    /**
     * 初始化连接
     */
    private function initConnection()
    {
        if ($this->all_use_master || $this->cur_use_master) {
            $this->initMasterConnection();
        } else {
            $this->initSlaveConnection();
        }
    }


    /**
     * 主库初始化
     */
    private function initMasterConnection()
    {
        $db_id = 'master';
        if (isset($this->all_connects[$db_id])) {
            $this->connection = $this->all_connects[$db_id];
        } else {
            $servers = ConfigHelper::mysql();
            $this->setServer($servers);
            $this->doConnection();
            $this->all_connects[$db_id] = $this->connection;
            $this->last_connect_times[$db_id] = $this->last_connect_time;
        }
    }


    /**
     * 从库初始化
     */
    private function initSlaveConnection()
    {
        $db_id = 'slave';
        if (isset($this->all_connects[$db_id])) {
            $this->connection = $this->all_connects[$db_id];
        } else {
            $servers = ConfigHelper::mysql_slave();
            $this->setServer($servers);
            $this->doConnection();
            $this->all_connects[$db_id] = $this->connection;
        }
    }


    /**
     * 设置连接信息
     * @param $servers
     */
    private function setServer($servers)
    {
        $this->db_host = $servers['DB_HOST'];
        $this->db_user = $servers['DB_USERNAME'];
        $this->db_pass = $servers['DB_PASSWORD'];
        $this->db_port = $servers['DB_PORT'];
    }


    /**
     * 连接数据库
     */
    private function doConnection()
    {
        $now_time = time();
        $dsn = "mysql:host={$this->db_host};port={$this->db_port};charset={$this->chart_set}";
        $options = array(
            PDO::ATTR_TIMEOUT => 10,
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_AUTOCOMMIT => 1,
        );
        try {
            $this->connection = @new PDO($dsn, $this->db_user, $this->db_pass, $options);
            $this->last_connect_time = $now_time;
        } catch (\PDOException $e) {
            if (stripos($e->getMessage(), 'server has gone away') !== false && $this->retry_times < 1) {
                $this->doConnection();
                $this->retry_times++;
            } else {
                $logger = new Logger(LOG_PATH . 'sql');
                $logger->error(get_exception($e, 'mysql'));
                throw $e;
            }
        }
    }


    /**
     * 最后执行的SQL记录
     * @param $sql
     * @param array $input_parameters
     */
    private function addSqlLog($sql, $input_parameters = [])
    {
        if (count($this->sql_log) > 50) {
            array_shift($this->sql_log);
        }
        array_push($this->sql_log, [$sql, $input_parameters]);
    }





    /**
     *
     *
     * 以下为外部操作方法
     *
     *
     */


    /**
     * 开启所有操作都从master走
     * 一定要在执行完后运行 disableAllMaster
     */
    public function enableAllMaster()
    {
        $this->all_use_master = 1;
    }


    /**
     * 禁用所有操作都从master走，回到正常的读写分离模式
     */
    public function disableAllMaster()
    {
        $this->all_use_master = 0;
    }


    /**
     * 开始事务 注意此处会隐式调用enableAllMaster
     * @return bool
     */
    public function beginTransaction()
    {
        $this->all_use_master = 1;//开启事务的全走master
        $this->initConnection();
        $this->connection->beginTransaction();
        $this->connection->setAttribute(PDO::ATTR_AUTOCOMMIT, 0);
        return true;
    }

    /**
     * 提交事务  注意此处会隐式调用disableAllMaster
     * @return bool
     */
    public function commit()
    {
        $this->connection->commit();
        $this->connection->setAttribute(PDO::ATTR_AUTOCOMMIT, 1);
        $this->all_use_master = 0;//提交后禁用全走master
        return true;
    }

    /**
     * 回滚事务  注意此处会隐式调用disableAllMaster
     * @param string $connection
     * @return bool
     */
    public function rollback($connection = '')
    {
        $tmp_connection = $this->connection;
        if (!empty($connection)) {
            $this->connection = $connection;
        }
        $this->connection->rollback();
        $this->connection->setAttribute(PDO::ATTR_AUTOCOMMIT, 1);
        $this->all_use_master = 0;//提交后禁用全走master
        $this->connection = $tmp_connection;
        return true;
    }


    /**
     * 执行增、删、改操作
     * @param $sql
     * @param array $input_parameters
     * @return int
     */
    public function query($sql, $input_parameters = [])
    {
        $this->cur_use_master = true;
        $this->initConnection();
        try {
            $this->addSqlLog($sql, $input_parameters);//记录SQL
            $PDOStatement = $this->connection->prepare($sql);
            $PDOStatement->execute($input_parameters);
            $effect_num = $PDOStatement->rowCount();
        } catch (\PDOException $e) {
            $this->cur_use_master = false;//还原设置
            $logger = new Logger(LOG_PATH . 'sql');
            $logger->error(get_exception($e, 'sql', ['sql' => $sql]), $input_parameters);
            throw $e;
        }
        $this->cur_use_master = false;//还原设置
        return $effect_num;
    }


    /**
     * 插入数据
     * @param $sql
     * @param array $input_parameters
     * @param bool $return_id
     * @return bool|int
     */
    public function insert($sql, $input_parameters = [], $return_id = false)
    {
        $res = $this->query($sql, $input_parameters);
        if ($return_id) {
            $res = $this->lastID();
        }
        return $res;
    }


    /**
     * 以数组形式插入一条数据
     * @param $table
     * @param array $data
     * @return bool|int
     */
    public function insertData($table, array $data)
    {
        if (!is_array($data) || empty($data)) {
            return false;
        }
        $params = $field_arr = $value_arr = [];
        foreach ($data as $key => $val) {
            $params[':_PRE_' . $key] = $val;
            $field_arr[] = $key;
            $value_arr[] = ':_PRE_' . $key;
        }
        $field_string = implode(',', $field_arr);
        $value_string = implode(',', $value_arr);
        if (empty($field_string) || empty($value_string) || empty($params)) {
            return false;
        }
        $sql = "insert into {$table} ({$field_string}) values({$value_string})";
        $id = $this->insert($sql, $params, true);
        return $id;
    }


    /**
     * 修改表数据
     * @param $sql
     * @param array $input_parameters
     * @return int
     */
    public function update($sql, $input_parameters = [])
    {
        $res = $this->query($sql, $input_parameters);
        return $res;
    }


    /**
     * @param string $table
     * @param array $data
     * @param string $where
     * @return bool|int
     */
    public function updateData($table, array $data, $where = '')
    {
        if (!is_array($data) || empty($data)) {
            return false;
        }
        $params = $field_arr = [];
        foreach ($data as $key => $val) {
            $params[':_PRE_' . $key] = $val;
            $field_arr[] = $key . '=:_PRE_' . $key;
        }
        $field_string = implode(',', $field_arr);

        if (empty($field_string) || empty($params)) {
            return false;
        }

        if (!empty($where)) {
            $where = 'where ' . $where;
        }

        $sql = "update {$table} set {$field_string} {$where}";
        $res = $this->update($sql, $params);
        return $res;
    }


    /**
     * 执行查询操作
     * @param $sql
     * @param array $input_parameters
     * @return mixed
     */
    private function query_s($sql, $input_parameters = [])
    {
        $this->cur_use_master = false;//查询默认使用从库
        $this->initConnection();
        try {
            $PDOStatement = $this->connection->prepare($sql);
            $rows = $PDOStatement->execute($input_parameters);
            $res = $PDOStatement->fetch(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            $logger = new Logger(LOG_PATH . 'sql');
            $logger->error(get_exception($e, 'sql', ['sql' => $sql]), $input_parameters);
            throw $e;
        }
        return $res;
    }


    /**
     * 获取单条记录
     * @param $sql
     * @param array $input_parameters
     * @return mixed
     */
    public function getOne($sql, $input_parameters = [])
    {
        if (!preg_match("/limit/i", $sql)) {
            $sql = preg_replace("/[,;]$/i", '', trim($sql)) . " limit 1 ";
        }
        $res = $this->query_s($sql, $input_parameters);
        return $res;
    }


    /**
     * 查询全部数据，返回结果数组
     * @param $sql
     * @param array $input_parameters
     * @return mixed
     */
    public function getAll($sql, $input_parameters = [])
    {
        $res = $this->query_s($sql, $input_parameters);
        return $res;
    }


    /**
     * 最后插入行的ID或序列值
     * 如果使用事务，则应在提交之前使用lastInsertId，否则会返回0
     * @return bool
     */
    public function lastID()
    {
        $this->cur_use_master = true;//last id使用主库
        $this->initConnection();
        $id = $this->connection->lastInsertId();
        $this->cur_use_master = false;//还原
        if ($id) {
            return $id;
        } else {
            return false;
        }
    }

}
