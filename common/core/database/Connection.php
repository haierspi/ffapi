<?php
namespace ff\database;

use ff\base\Component;
use Illuminate\Events\Dispatcher;
use Illuminate\Database\Capsule\Manager as IlluminateDatabaseCapsuleManager;
use Illuminate\Container\Container;
/**
 * Connection represents a connection to a database via [PDO](php.net/manual/en/book.pdo.php).
 *
 * @author haierspi<haierspi@qq.com>
 */
class Connection extends Component
{
    private $objects = [];
   
    public $config;
    public $current_key;
    public $default_key;
    public $capsule;


    /* 填充配置文件 */
    public function __construct($config)
    {
        $this->default_key = $config['default'];
        $this->config = $config;
        $this->capsule = new IlluminateDatabaseCapsuleManager();
        $this->init();
    }
    public function init()
    {
        // //加载ORM
        $config = $this->config;
        unset($config['default']);

        foreach ($config as $ck => $cv) {
            $name = $this->default_key == $ck ? 'default' : $ck;
            $this->capsule->addConnection($this->parseConfig($cv), $name);
            if (defined('SYSTEM_DEBUG_SQLLOG') && constant('SYSTEM_DEBUG_SQLLOG') == 1) {
                //enable debug log
                $this->capsule->getConnection($name)->enableQueryLog();
            }else{
                $this->capsule->getConnection($name)->disableQueryLog();
            }
        }
        $this->capsule->setEventDispatcher(new Dispatcher(new Container));
        $this->capsule->setAsGlobal();
        $this->capsule->bootEloquent();
    }

    public function getDb($connectKey = null)
    {
        return $this->capsule->getConnection($connectKey);
        
    }

    public static function microtime()
    {
        list($usec, $sec) = explode(" ", microtime());
        return ((float) $usec + (float) $sec);
    }

    public static function runtime($startTime, $endTime)
    {
        return $endTime - $startTime;
    }


    private function parseConfig($config)
    {
        $configData = $this->parseDSN($config['dsn']);
        $Rdata = array_merge($configData,$config);

        $Rdata['database'] = $Rdata['dbname'];
        $Rdata['prefix'] = $config['tablepre'];

        unset($Rdata['dsn'],$Rdata['dbname'],$Rdata['tablepre']);

        return $Rdata;
    }
    private function parseDSN($dsnStr)
    {
        $config = [];
        if (empty($dsnStr)) {return false;}
        $info = parse_url($dsnStr);
        if ($info['scheme']) {
            $config['driver'] = $info['scheme'];

            $parseArray = explode(';', $info['path']);

            foreach ($parseArray as $oneConfig) {
                if ($oneConfig) {
                    $oneSet = explode('=', $oneConfig);
                    $config[$oneSet[0]] = $oneSet[1];
                }
            }
        }
        return $config;
    }
}
