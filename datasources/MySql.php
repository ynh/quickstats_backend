<?php
class Mysql extends DatasourceStructure{

    public function getSettings(){
        return array(array('type'=>"text",'name'=>"host","label"=>"Host","placeholder"=>"localhost"),
            array('type'=>"text",'name'=>"username","label"=>"Username","placeholder"=>"root"),
            array('type'=>"password",'name'=>"password","label"=>"Password"),
            array('type'=>"text",'name'=>"dbname","label"=>"Database Name"),
            array('type'=>"textarea",'name'=>"query","label"=>"Query"));

    }
    public function query($settings){
        $tdb = new PDO('mysql:host='.$settings['host'].';dbname='.$settings['dbname'].'',
            $settings['username'],
            $settings['password']);

        $tdb->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return DB::run($settings['query'],array(),$tdb);
    }
}