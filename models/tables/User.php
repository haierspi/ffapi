<?php
/**
 * Created by PhpStorm.
 * Author: XuanRan
 * Date: 2019-09-11
 * Time: 16:51
 */

namespace models\tables;

use ff\database\Model;

class User extends Model
{

    public $table = 'admin_user';

    
    public function getAttributesByUid($uid)
    {
        $result = $this->where('id', $uid)->limit(1)->first();
        return $result->attributes;
    }
}