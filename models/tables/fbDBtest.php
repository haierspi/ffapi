<?php
namespace models\tables;

use \ff\database\db;
use \ff\database\Model;

class fbDBtest extends Model
{
    protected $connection = 'fbadauto';
    //protected $table = 'fb';
    public $table = 'fb';

    public function getOne()
    {

        //ORM 
        /*
            use \ff\database\Model;
        */
        $omsUser = $this->select('*')
            ->limit(1)
            ->first()->toArray();

        //db queries
        /*
            use \ff\database\db;
        */
       $omsUser2 = DB::connection("fbadauto")->table('fb')->first();


       echo '<pre>';
       var_dump( code );
       echo '</pre>';
       exit;
       

        return $omsUser;
    }

}
