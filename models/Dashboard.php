<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ynh
 * Date: 4/26/13
 * Time: 10:26 PM
 * To change this template use File | Settings | File Templates.
 */

class Dashboard extends Model {



    public $id;
    public $title;
    public $_widgets;

    public static function getTableName()
    {
        return "dashboards";
    }

    public function loadWidgets(){
        $this->_widgets=WidgetView::findAll(array("dashboard_id"=>$this->id),array("`order` asc"));

    }
}