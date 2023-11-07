<?php

namespace controllers;

class EmailConfig
{
    public $file_path = "db/email.json";

    public $init_data = [
        "sender" => "",
        "password" => ""
    ];

    public $email_config = [];

    public function init()
    {
        if (!file_exists($this->file_path)) {
            $fp = fopen($this->file_path, 'w');
            fwrite($fp, json_encode($this->init_data));
            fclose($fp);
        }
    }

    public function get($return = false)
    {
        $this->set();
        if ($return) {
            return $this->email_config;
        } else {
            echo json_encode($this->email_config);
            exit;
        }
    }

    public function update()
    {
        $this->set();
        
        $rows = $_REQUEST['rows'];

        foreach($rows as $key => $value) {
            $this->email_config->$key = $value;
        }

        file_put_contents($this->file_path, json_encode($this->email_config));
        echo json_encode($this->email_config);
        exit;
    }

    public function get_by_key($key)
    {
        $this->set();
        return $this->email_config->$key;
    }

    public function set()
    {
        $this->email_config = json_decode(file_get_contents($this->file_path));
    }
}