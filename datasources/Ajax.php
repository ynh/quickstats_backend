<?php
function getRemoteData($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url); //api.json is displaying value from session
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_COOKIESESSION, true);
    $output = curl_exec($ch);
    curl_close($ch);
    return $output;
}
class Ajax extends DatasourceStructure{

    public function getSettings(){
        return array(array('type'=>"text",'name'=>"host","label"=>"Test","placeholder"=>"localhost"));

    }
    public function query($settings){


        echo getRemoteData($settings['url']);
    }
}