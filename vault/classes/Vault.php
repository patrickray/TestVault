<?php 
class Vault {	
    
    public $Log;
    
    public function __construct($vault_files) {
        $this->Log = new KLogger ( "log.txt" , KLogger::DEBUG );
        $this->Log->LogInfo("Logging started");
        
        $this->vault_files = $vault_files;
        
        $this->setSettings();
        $this->createCacheFolder();
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
    public $vault_files = array();    
    

    public function setSites($array) {
        $this->sites = $array;
    }
    
    public function createCacheFolder() {
        if(!file_exists($this->cache_file_location)) {
            $this->Log->LogInfo("Cache folder doesn't exists, attempting to create folder at " . $this->cache_file_location);
            if(mkdir($this->cache_file_location)) {
                $this->Log->LogInfo("Cache folder created at " . $this->cache_file_location);
            } else {
                $this->Log->LogInfo("Cache folder could not be created, check permissions");
            }
        }
    }
    
    public function setSettings() {
        $this->settings['cache_time'] = CACHE_TIME;
        $this->Log->LogInfo("Cache time is set to " . CACHE_TIME . ' minutes');
    }
    
    public function getFiles() {        
        foreach($this->vault_files as $vault_files_key => $vault_file) {
            $temp_file = basename($vault_file);
            $cache_file_timestamp = ((file_exists($this->cache_file_location . '/' . $temp_file))) ? filemtime($this->cache_file_location . '/' . $temp_file) : 0;
            $this->Log->LogInfo("Cache file " . $this->cache_file_location . '/' . $temp_file . ' is ' . number_format(time() - $cache_file_timestamp) / 60 . ' minutes old', 2);
            
            if (time() - ($this->settings['cache_time'] * 60) > $cache_file_timestamp) {
                //Get the file
                $this->Log->LogInfo('Getting file: ' . VAULT_URL . $vault_file);
                $file_location = VAULT_URL . $vault_file;			
                $this->saveFileToCache($file_location);
            }
        }        
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
        foreach (glob($this->cache_file_location . "/*") as $filename) {        
            $this->setKeysValues($filename);
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