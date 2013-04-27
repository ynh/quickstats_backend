<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ynh
 * Date: 4/26/13
 * Time: 10:26 PM
 * To change this template use File | Settings | File Templates.
 */

class Datasource extends Model {



    public $id;
    public $name;
    public $handler;
    public function onLoad(){

        include_once 'datasources/'.$this->handler.'.php';
        $class=$this->handler;
        $obj= new $class();


        $this->settings=$obj->getSettings();

    }
    public static function getTableName()
    {
        return "datasources";
    }
}