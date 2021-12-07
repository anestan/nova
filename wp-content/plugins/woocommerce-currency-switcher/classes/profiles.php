<?php
if (!defined('ABSPATH'))
    die('No direct access allowed');


class WOOCS_Profile{
    
    public $settings_key="";
    public function __construct($key) {
        $this->settings_key=$key;
    }
    public function set_data($data){
        update_option($this->settings_key,$data);
    }
    public function get_data(){
        $data=get_option($this->settings_key,array());
       // var_dump($data);
        return $data;
    }
    public function update_date($value,$key){
        $data=$this->get_data();
        $data[$key]=array(); 
        
        $data[$key]=$value;
        $this->set_data($data);
    }
    public function delete_data($key){
        $data=$this->get_data();
        if(isset($data[$key])){
            unset($data[$key]);
        }
        $this->set_data($data);
    }

    public function add_data($value){
        $key=uniqid("woocs_profile_");
        $this->update_date($value, $key);
        return $key;
    }
    
             
}

