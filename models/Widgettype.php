<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ynh
 * Date: 4/26/13
 * Time: 10:26 PM
 * To change this template use File | Settings | File Templates.
 */

class Widgettype extends Model {



    public $id;
    public $name;
    public $title;

    public static function getTableName()
    {
        return "widgettypes";
    }
}