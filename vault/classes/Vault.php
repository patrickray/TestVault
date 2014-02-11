<?php 
class Vault {	
    
    public $Log;
    
    public function __construct() {
        $this->Log = new KLogger ( "log.txt" , KLogger::DEBUG );
        $this->Log->LogInfo("Logging started");
        
        $this->setSettings();
        $this->getFiles();
        $this->parseCacheFiles();
        $this->setLocalValues();
        
    }
    
    public $settings = array();
    
    public $sites;
    public $values = array();
    public $cache_file_location = 'vault/cache';
    public $local_values_file = 'vault/local/local.txt';
    public $log_file = 'vault/log.txt';
    
    

    public function setSites($array) {
            $this->sites = $array;
    }
    
    public function setSettings() {
        $this->settings['cache_time'] = CACHE_TIME;
        $this->Log->LogInfo("Cache time is set to " . CACHE_TIME . ' minutes');
    }
    
    public function getFiles() {
        $cache_file_timestamp = ((file_exists($this->cache_file_location . '/' . SITE . '.txt'))) ? filemtime($this->cache_file_location . '/' . SITE . '.txt') : 0;
        $this->Log->LogInfo("Cache file " . $this->cache_file_location . '/' . SITE . '.txt is ' . number_format(time() - $cache_file_timestamp) / 60 . ' minutes old', 2);
        
        if (time() - ($this->settings['cache_time'] * 60) > $cache_file_timestamp) {
            //Get Master file
            $this->Log->LogInfo('Getting new master file');
            $file_location = VAULT_URL . SITE_GROUP;			
            $this->saveFileToCache($file_location . '/' . SITE_GROUP . '.txt');
            
            //Get Site file
            $this->Log->LogInfo('Getting new site file');
            $file_location = VAULT_URL . SITE_GROUP . '/' . SITE;			
            $this->saveFileToCache($file_location . '/' . SITE . '.txt');
        }
    }

    public function createFolder($folder) {
        mkdir('vault/cache/' . $folder);
    }
    
    public function saveFileToCache($url) {        
        $contents = file_get_contents($url);
        if($this->isJson($contents)) {
            if(file_put_contents('vault/cache/' . basename($url), $contents)) {
                $this->Log->LogInfo("JSON file saved: " . $url);
            } else {
                $this->Log->LogInfo("Error saving the file to: " . 'vault/cache/' . basename($url) . '.  Check the permissions.');
            }
        } else {
            $this->Log->LogError("Inavalid JSON at: " . $url);
        }
    }

    public function parseCacheFiles() {
        $master_file = $this->cache_file_location . '/' . SITE_GROUP . '.txt';
        if(file_exists($master_file)) {
                $this->setKeysValues($master_file);
        }
        $site_file = $this->cache_file_location . '/' . SITE . '.txt';
        if(file_exists($site_file)) {
                $this->setKeysValues($site_file);
        }

    }

    public function setKeysValues($file) {
        $contents = file_get_contents($file);
        if($this->isJson($contents)) {
            $key_value_array = array_shift(json_decode(file_get_contents($file), true));
            foreach($key_value_array as $key => $value) {
                    $this->values[$key] = $value;
            }
        }
    }

    public function setLocalValues() {
        $local_values = ((file_exists($this->local_values_file))) ? file_get_contents($this->local_values_file) : 0;
        if($this->isJson($local_values)) {
            $key_value_array = array_shift(json_decode($local_values, true));
            foreach($key_value_array as $key => $value) {
                $this->values[$key] = $value;
            }	
        }
    }

    public function show($key) {
        if(!isset($this->values[$key])) {
            $this->showError('Value: "'.$key.'" not set');
        } else {
            echo $this->values[$key];	
        }
    }

    public function isJson($string) {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    public function showError($message) {
        echo '<span style="background: #F00;">' . $message . '</span>';
    }
	
}