<?php

namespace sdk\libs;

use PDO;

/**
 * MySQL
 * Class Mysql
 * @package vendor\libs
 */
class Mysql {

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
    private $chartset = 'utf8mb4';

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
     * mysql 主最后连接时间，对于长连接需要考虑超时的问题
     * @var int
     */
    private $last_connect_time_m = 0;
    
    
    /**
     * mysql 从最后连接时间，对于长连接需要考虑超时的问题
     * @var int
     */
    private $last_connect_time_s = 0;

    /**
     * 出现gone way之后的重试次数
     */
    private $retry_times = 0;
    private $cur_use_master = true;// true:当前使用主进行操作,false:当前使用从进行操作
    private $all_use_master = false;// true:所有的操作都使用主,false:主从分离
    private $sql_log = array();
    private $db_mode = 0;// 0 表示普通模式(当前实例，默认所有业务共有的)  1 日表(商户日表实例)  2 日分表（按商户把日表分割到不同的实例）
    private $last_connect_times = array();//实例最后活跃时间 以实例名称作为键
    private $all_connects = array();//实例连接列表 以实例名称作为键


    /**
     * 构造
     */
    protected function __construct() {
        $this->initConnection();
        return $this->connection;
    }


    /**
     * 禁止克隆
     */
    protected function __clone() {
        //Me not like clones! Me smash clones!
    }


    /**
     * 获取连接实例
     * @return null|Mysql
     */
    public static function getInstance() {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }


    /**
     * 初始化连接
     */
    private function initConnection(){
        if($this->all_use_master || $this->cur_use_master){
            $this->initMasterConnection();
        } else {
            $this->initSlaveConnection();
        }
    }


    /**
     * 主库初始化
     */
    private function initMasterConnection(){
        $db_id = 'master';
        if(isset($this->all_connects[$db_id])){
            $this->connection = $this->all_connects[$db_id];
        }else{
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
    private function initSlaveConnection(){
        $db_id = 'slave';
        if(isset($this->all_connects[$db_id])){
            $this->connection = $this->all_connects[$db_id];
        }else{
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
    private function setServer($servers){
        $this->db_host = $servers['DB_HOST'];
        $this->db_user = $servers['DB_USERNAME'];
        $this->db_pass = $servers['DB_PASSWORD'];
        $this->db_port = $servers['DB_PORT'];
    }


    /**
     * 连接数据库
     */
    private function doConnection(){
        $now_time = time();
        $dsn = "mysql:host={$this->db_host};port={$this->db_port};charset={$this->chartset}";
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
                throw $e;
            }
        }
    }



    /*
     *
     *
     *
     *
     *
     *
     *
     *
     *
     */

    /**
     * 执行增、删、改操作
     * @param $sql
     * @param array $input_parameters
     * @return int
     */
    public function query($sql,$input_parameters = []) {
        $this->cur_use_master = true;
        $this->initConnection();
        try {
            //$this->addSqlLog($sql, $input_parameters);
            $PDOStatement = $this->connection->prepare($sql);
            $PDOStatement->execute($input_parameters);
            $effect_num = $PDOStatement->rowCount();
        } catch (\PDOException $e) {
            $this->cur_use_master = false;//还原设置
            throw $e;
        }
        $this->cur_use_master = false;//还原设置
        return $effect_num;
    }


    public function insert($sql,$input_parameters = [],$return_id = false){
        $res = $this->query($sql,$input_parameters);
        if($return_id){
            $this->lastID();
        }
    }


    /**
     * 执行查询操作
     * @param $sql
     * @param array $input_parameters
     * @return mixed
     */
    private function query_s($sql,$input_parameters = []) {
        $this->cur_use_master = false;//查询默认使用从库
        $this->initConnection();
        try {
            $PDOStatement = $this->connection->prepare($sql);
            $rows = $PDOStatement->execute($input_parameters);
            $res = $PDOStatement->fetch(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
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
    public function getOne($sql, $input_parameters = []) {
        if (!preg_match("/limit/i", $sql)) {
            $sql = preg_replace("/[,;]$/i", '', trim($sql)) . " limit 1 ";
        }
        $res = $this->query_s($sql,$input_parameters);
        return $res;
    }


    /**
     * 查询全部数据，返回结果数组
     * @param $sql
     * @param array $input_parameters
     * @return mixed
     */
    public function getAll($sql,$input_parameters = []) {
        $res = $this->query_s($sql,$input_parameters);
        return $res;
    }


    /**
     * 最后插入行的ID或序列值
     * 如果使用事务，则应在提交之前使用lastInsertId，否则会返回0
     * @return bool
     */
    public function lastID() {
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
