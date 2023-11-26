<?php

namespace controllers;

class LastPhone
{
    public $file_path = "db/lastphone.json";

    public $init_data = [
        'PALM_CON_WA' => '',
        'PALM_CON_BAY' => '',
        'PALM_CON_SD' => '',
        'PALM_CON_LA' => '',
        'PALM_CON_FL' => '',
        'PALM_CON_TX' => '',
        'PALM_WA' => '',
        'PALM_BAY' => '',
        'PALM_SD' => '',
        'PALM_LA' => '',
        'PALM_FL' => '',
        'PALM_TX' => '',
        'SHAI_CA' => '',
        'SHAI_KITCHEN' => '',
        'SHAI_LA' => '',
        'SHAI_SD' => '',
        'SHAI_WA' => '',
        'SHAI_BAY_SOUTH' => '',
        'SHAI_BAY_NORTH' => '',
        'SHAI_OR' => '',
        'SHAI_TX_HOUSTON' => '',
        'SHAI_TX_DALLAS' => '',
    ];

    public $lastphone = [];

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
            return $this->lastphone;
        } else {
            echo json_encode($this->lastphone);
            exit;
        }
    }

    public function update()
    {
        $this->set();

        $rows = $_REQUEST['rows'];  

        foreach($rows as $key => $value) {
            $this->lastphone[$key] = $value;
        }

        file_put_contents($this->file_path, json_encode($this->lastphone));
        echo json_encode($this->lastphone);
        exit;
    }

    public function get_by_key($key)
    {
        $this->set();
        return $this->lastphone[$key];
    }

    public function set()
    {
        $this->lastphone = json_decode(file_get_contents($this->file_path), true);
    }

    public function save($data)
    {
        file_put_contents($this->file_path, json_encode($data));
    }
}