<?php

return [
    //权限维度
    'permission' => [
        'scm'                                           => 'SCM',
        'scm/caiyang'                                   => '采样',
        'scm/caiyang/view{v1_0\Scm\Search GET}'    => 'scm列表浏览',
        'scm/caiyang/add'                               => 'scm添加',
        '{v1_0\privileges\users GET}'    => 'scm post',
    ],
    //内容维度
    'content' => [
        'pop' => 'pop线',
        'lw' => 'LW线',
    ],
    
];
