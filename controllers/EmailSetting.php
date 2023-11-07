<?php

namespace controllers;

class EmailSetting
{
    public $file_path = "db/emailSetting.json";

    public $init_data = [
        'shai1' => [
            'open' => false,
            'subject' => '',
            'receivers' => [''],
            'files' => [],
            'body' => "   ",
            'isWhatsApp' => false,
            'whatsapp_message' => '',
            'whatsapp_people' => [''],
            'whatsapp_groups' => [''],
        ],
        'shai2' => [
            'open' => false,
            'subject' => '',
            'receivers' => [''],
            'files' => [],
            'body' => "   ",
            'isWhatsApp' => false,
            'whatsapp_message' => '',
            'whatsapp_people' => [''],
            'whatsapp_groups' => [''],
        ],
        'palm1' => [
            'open' => false,
            'subject' => '',
            'receivers' => [''],
            'files' => [],
            'body' => "   ",
            'isWhatsApp' => false,
            'whatsapp_message' => '',
            'whatsapp_people' => [''],
            'whatsapp_groups' => [''],
        ],
    ];

    public $email_setting;

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
            return $this->email_setting;
        } else {
            echo json_encode($this->email_setting);
            exit;
        }
    }

    public function update()
    {
        $this->set();

        $this->email_setting->shai1->open = false;
        $this->email_setting->shai2->open = false;
        $this->email_setting->palm1->open = false;

        $key = $_REQUEST['key'];

        foreach($_REQUEST['rows'] as $k => $v) {
            $this->email_setting->$key->$k = $v;
        }

        file_put_contents($this->file_path, json_encode($this->email_setting));
        echo json_encode($this->email_setting);
        exit;
    }

    public function get_by_key($key)
    {
        $this->set();
        return $this->email_setting->$key;
    }

    public function set()
    {
        $this->email_setting = json_decode(file_get_contents($this->file_path));
    }

    public function save($data)
    {
        file_put_contents($this->file_path, json_encode($data));
    }
}