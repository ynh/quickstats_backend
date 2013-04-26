<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ynh
 * Date: 4/26/13
 * Time: 10:26 PM
 * To change this template use File | Settings | File Templates.
 */

class Widget extends Model {



    public $id;
    public $wigettype_id;
    public $datasource_id;
    public $dashboard_id;
    public $datasource_settings;
    public $settings;

    public static function getTableName()
    {
        return "widgets";
    }
}