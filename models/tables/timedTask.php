<?php
namespace models\tables;

class TimedTask extends FacebookAuto
{
    public $table = 'timed_task';

    protected $primaryKey = 'ttid';

    protected $fillable = [
        'ttid',  //任务ID,
        'typeKey',  //任务类型 commentHide,
        'typeId',  //任务关联ID,
        'sourceType', // 任务来源类型
        'uid',  //操作人UID,
        'username',  //操作人名称,
        'data',  //page 其他数据原始数据,
        'createdTime',  //创建时间,
        'updatedTime',  //更新日期,
        'plannedTime',  //任务计划执行时间 (如果0000-00-00 00:00:00 或者小于等于创建时间则立即执行),
        'executionTime',  //任务执行时间时刻,
        'isExecution',  //是否已经执行过[0 否 1 是],
        'executionResult',  //执行结果[0 尚未结束 1 成功执行 -1 执行失败],
        'executionTimeDuration',  //执行时长,
    ];

}
