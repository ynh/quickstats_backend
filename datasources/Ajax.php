<?php
class Ajax extends DatasourceStructure{

    public function getSettings(){
        return array(  array('type'=>"text",'name'=>"url","label"=>"Url","placeholder"=>"http://...."));

    }
}