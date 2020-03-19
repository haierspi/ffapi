<?php

namespace ff\database;

use ff\base\Component;

class sqlsrvConnection extends Component
{
    public static $config = array();
    public static $link = array();
    public static $defaultKey = ''; //默认连接KEY
    public static $curLink;
    public static $curKey = ''; //当前连接KEY
    private static $connected = false;

    public function __construct()
    {
        if (func_num_args() > 0) {
            $config = func_get_arg(0);
            self::$defaultKey = $config['default'];
            self::$config = $config;
        }
    }

    public function connect($confkey = '')
    {

        $confkey = $confkey ? $confkey : self::$defaultKey;

        if (!isset(self::$link[$confkey])) {
            $config = self::$config[$confkey];
            if (extension_loaded('pdo_sqlsrv')) {
                self::$link[$confkey] = $this->pdo_connect($config['host'], $config['port'], $config['database'], $config['username'], $config['password']);
            } else {
                self::$link[$confkey] = $this->mssql_connect($config['host'], $config['port'], $config['database'], $config['username'], $config['password']);
            }
        }

        self::$curLink = self::$link[$confkey];
        self::$curKey = $confkey;
        self::$connected = true;
        return self::$curLink;
    }

    public function mssql_connect($DB_HOST, $DB_PORT, $DB_NAME, $DB_USER, $DB_PWD)
    {
        // Connect to MSSQL
        $link = mssql_connect("{$DB_HOST}:{$DB_PORT}", $DB_USER, $DB_PWD);
        if (!$link || !mssql_select_db($DB_NAME, $link)) {
            die('Error connecting to SQL Server');
        }
        return $link;
    }

    public function pdo_connect($DB_HOST, $DB_PORT, $DB_NAME, $DB_USER, $DB_PWD)
    {
        try {
            $link = new \PDO("sqlsrv:server={$DB_HOST},{$DB_PORT};Database={$DB_NAME}", $DB_USER, $DB_PWD);
            $link->setAttribute(\PDO::SQLSRV_ATTR_DIRECT_QUERY, \PDO::SQLSRV_ENCODING_UTF8);
        } catch (\PDOException $e) {
            die($e->getMessage()."Error connecting to SQL Server");
        }
        return $link;
    }

    public function execStoredProcedure($sql){
        $data = [];
        $result = $this->query($sql);
        if(extension_loaded('pdo_sqlsrv')){
            for ($i = 0; $i < 30; $i++){
                $result->nextRowset();
                if($result->columnCount()){
                    break;
                }
            }
        }

        //取得所有的表名
        while($row = $this->fetch($result)){
            $data[] = $row;
        }
        return $data;
    }

    public function query($sql)
    {
        //初始化连接
        if (!self::$connected) {
            $this->connect();
        }
        if (extension_loaded('pdo_sqlsrv')) {
            $queryResource = self::$curLink->query($sql);

            if (!$queryResource) {
                echo '<pre>';
                var_dump(self::$curLink->errorInfo());
                echo '</pre>';
                echo '<pre>';
                foreach (debug_backtrace() as $n => $one) {
                    echo "{$n}. {$one['file']}  {$one['class']} {$one['function']} {$one['line']}   \n";
                }
                echo '</pre>';
                exit;

                exit;

            } else {
                return $queryResource;
            }

        } else {

            return mssql_query($sql, self::$curLink);
        }
    }

    public function fetchAll ($queryRes)
    {
        if (extension_loaded('pdo_sqlsrv')) {
            return $queryRes->fetchAll (\PDO::FETCH_ASSOC);
        } else {
            return mssql_fetch_assoc($queryRes);
        }
    }


    
    public function fetchColumnBySql($sql,$column_number = 0)
    {
        $queryRes = $this->query($sql);
        if (extension_loaded('pdo_sqlsrv')) {
            return $queryRes->fetchColumn($column_number);
        } else {
            return mssql_fetch_assoc($queryRes);
        }
    }



    public function fetchBySql($sql)
    {
        $queryRes = $this->query($sql);
        if (extension_loaded('pdo_sqlsrv')) {
            return $queryRes->fetch(\PDO::FETCH_ASSOC);
        } else {
            return mssql_fetch_assoc($queryRes);
        }
    }

    public function fetch($queryRes)
    {
        if (extension_loaded('pdo_sqlsrv')) {
            return $queryRes->fetch(\PDO::FETCH_ASSOC);
        } else {
            return mssql_fetch_assoc($queryRes);
        }
    }

    public function beginTransaction()
    {
        if (extension_loaded('pdo_sqlsrv')) {
            return self::$curLink->beginTransaction();
        } else {
            return $this->query("BEGIN TRANSACTION crSave");
        }
    }

    public function rollback()
    {
        if (extension_loaded('pdo_sqlsrv')) {
            return self::$curLink->rollback();
        } else {
            return $this->query("ROLLBACK TRANSACTION crSave");
        }
    }

    public function commit()
    {
        if (extension_loaded('pdo_sqlsrv')) {
            return self::$curLink->commit();
        } else {
            return $this->query("COMMIT TRANSACTION crSave");
        }
    }

    public function convertToUtf8($string)
    {
        if (!empty($string)) {
            $fileType = mb_detect_encoding($string, array('UTF-8', 'GBK', 'LATIN1', 'BIG5'));
            if ($fileType != 'UTF-8') {
                $string = mb_convert_encoding($string, 'utf-8', $fileType);
            }
        }
        return $string;
    }

    public function convert($string)
    {
        if (extension_loaded('pdo_sqlsrv')) {
            return $string;
        } else {
            return mb_convert_encoding($string, 'GBK', 'UTF-8');
        }
    }

    public function insert($table, $data)
    {
        $sql = $this->implode($data);
        $cmd = 'INSERT INTO';
        return $this->query("$cmd $table $sql");
    }

    public function update($table, $data, $condition)
    {

        $sql = $this->updateImplode($data);
        if (empty($sql)) {
            return false;
        }
        $cmd = "UPDATE ";

        $res = $this->query("$cmd $table SET $sql WHERE $condition");
        return $res;
    }

    public function updateImplode($array, $glue = ',')
    {
        $fileds = [];
        foreach ($array as $k => $v) {

            if (is_null($v)) {
                $value = ' NULL';
            } elseif (is_int($v) || is_float($v)) {
                $value = $v;
            } elseif (is_string($v)) {
                if (substr($v, 0, 1) == '@') {
                    $value = $v;
                } else {
                    $value = "'{$v}'";
                }
            }
            $fileds[] = "{$k} = {$value}";
        }

        return join(',', $fileds);
    }

    public function implode($array, $glue = ',')
    {
        $filedsSql = $valuesSql = '';
        $fileds = $values = [];
        foreach ($array as $k => $v) {
            $fileds[] = $k;
            if (is_null($v)) {
                $value = ' NULL';
            } elseif (is_int($v) || is_float($v)) {
                $value = $v;
            } elseif (is_string($v)) {
                if (substr($v, 0, 1) == '@') {
                    $value = $v;
                } else {
                    $value = "'{$v}'";
                }

            }
            $values[] = $value;
        }
        $filedsSql = join(', ', $fileds);
        $valuesSql = join(', ', $values);

        return "({$filedsSql}) VALUES ({$valuesSql})";
    }
}
