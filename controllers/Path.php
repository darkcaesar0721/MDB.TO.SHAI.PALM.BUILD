<?php

namespace controllers;

class Path
{
    public $file_path = "db/path.json";

    public $init_data = [];

    public $path = [];

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
            return $this->path;
        } else {
            echo json_encode($this->path);
            exit;
        }
    }

    public function update()
    {
        $this->set();

        $rows = $_REQUEST['rows'];

        foreach($rows as $key => $value) {
            $this->path->$key = $value;
        }

        file_put_contents($this->file_path, json_encode($this->path));
        echo json_encode($this->path);
        exit;
    }

    public function get_by_key($key)
    {
        $this->set();
        return $this->path->$key;
    }

    public function set()
    {
        $this->path = json_decode(file_get_contents($this->file_path));
    }

    public function save($data)
    {
        file_put_contents($this->file_path, json_encode($data));
    }
}