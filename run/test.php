<?php

//定义进程数量
define('FORK_NUMS', 5);

//用于保存进程pid

$pipePath =  "pipe";
if (!file_exists($pipePath)) {
    if (!posix_mkfifo($pipePath, 0666)) {
        exit('make pipe false!' . PHP_EOL);
    }
}

$dataCount = 0;
for ($i = 0; $i < 1000; ++$i) {
    $pid = pcntl_fork();

    if ($pid > 0) {
        echo "父进程ID : ".posix_getpid(). " {$i} => {$dataCount} \r\n";
        
        $fp = fopen($pipePath, 'r');
        $dataCount = fread($fp, 20);


        pcntl_wait($status);
        if ($dataCount) {
            break;
        }

        
    }
    //子进程
    elseif ($pid == 0) {
        
        $dataCount = $dataCount +100;

        $fp = fopen($pipePath, 'w');
        fwrite($fp, $dataCount);

        echo "子进程ID : " . posix_getpid() . " {$i} => {$dataCount} \r\n";
        sleep(1);
        exit;
    } else {
        echo "fork fail\n";
    }

}
