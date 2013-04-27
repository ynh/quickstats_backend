<?php
include 'Framework.php';
include 'datasources/Datasource.php';
include 'models/Widgettype.php';
include 'models/Dashboard.php';
include 'models/Datasource.php';
include 'models/Widget.php';


function copyFields(&$from, &$to, $fields)
{
    foreach ($fields as $field) {
        if (isset($from->$field)) {
            $to->$field = $from->$field;
        }
    }
}



$nano=new NanoFramework();
$dbnew = new PDO('mysql:host=localhost;dbname=stat',
    'root',
    'secrets');

$dbnew->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$wigettypes=function(){
    return Widgettype::findAll();
};
$nano->get("/widgettypes",$wigettypes);



$datasources=function(){
    return Datasource::findAll();
};
$nano->get("/datasources",$datasources);



$dashboards=function(){
    return Dashboard::findAll();
};
$nano->get("/dashboards",$dashboards);


$dashboard_get=function($n,$params){
    $d=Dashboard::get(intval($params['id']));
    $d->loadWidgets();
    $d->_widgettypes= Widgettype::findAll();
    $d->_datasources= Datasource::findAll();

    return $d;
};
$nano->get("/dashboards/:id",$dashboard_get);



$widget_save=function($nano,$params){
    $item=Widget::get(intval($params['id']));
    $data = @json_decode($nano->postbody);
    if(isset($data->datasource_settings)){
        $data->datasource_settings=@json_encode($data->datasource_settings);
    }
    if(isset($data->settings)){
        $data->settings=@json_encode($data->settings);
    }
    copyFields($data, $item, array('dashboard_id','settings','datasource_id','id','widgettype_id','datasource_settings','title' ));
    $item->save();
    return $item;
};
$nano->put("/widget/:id",$widget_save);

$widget_get=function($nano,$params){


    return WidgetView::get(intval($params['id']));
};
$nano->get("/widget/:id",$widget_get);
$widgetclone=function($nano,$params){


    $d= Widget::get(intval($params['id']));

    $d->resave();
    return WidgetView::get($d->id);
};
$nano->get("/clonewidgets/:id",$widgetclone);


$handler=function($nano,$params){
    try{
        include_once 'datasources/'.$params['name'].'.php';
        $class=$params['name'];
        $obj= new $class();

        return $obj->query($_POST['settings']);
    }catch (Exception $ex){
        return array();
    }
};
$nano->post("/handler/:name",$handler);


$order=function($nano,$params){
    try{
        foreach($_POST['order'] as $o){
            $d= Widget::get(intval($o['id']));
            $d->order=intval($o['order']);
            $d->save();
        }
    }catch (Exception $ex){
        return array();
    }
};
$nano->post("/order",$order);


$nano->run();