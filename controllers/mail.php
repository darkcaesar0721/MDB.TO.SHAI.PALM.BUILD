<?php

namespace controllers;

require __DIR__ . '/../vendor/autoload.php';

require_once('Path.php');
require_once('EmailConfig.php');
require_once('EmailSetting.php');
require_once('WhatsApp.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

use Google_Client;
use Google_Service_Sheets;
use Google_Service_Sheets_ValueRange;

class Mail
{
    public $path_obj = "";
    public $email_config_obj = "";
    public $email_setting_obj = "";
    public $whatsapp_obj = "";

    public $path = '';
    public $email_config = "";
    public $email_setting = "";
    public $whatsapp = "";

    public $a_counts = [
        0 => [
            'c' => '0 Shai - W D, CA',
            'k' => 'CA W, D',
            'i' => -1,
            'v' => 0,
        ],
        1 => [
            'c' => '1 Shai KBD',
            'k' => 'KBDR',
            'i' => -1,
            'v' => 0,
        ],
        2 => [
            'c' => '2 ALIT Shai LA',
            'k' => 'LA',
            'i' => -1,
            'v' => 0,
        ],
        3 => [
            'c' => '3 ALIT Shai SD',
            'k' => 'SD',
            'i' => -1,
            'v' => 0,
        ],
        4 => [
            'c' => '4 ALIT Shai WA',
            'k' => 'WA',
            'i' => -1,
            'v' => 0,
        ],
        5 => [
            'c' => '5 ALIT Shai BAY South',
            'k' => 'BAY S.',
            'i' => -1,
            'v' => 0,
        ],
        6 => [
            'c' => '6 ALIT Shai BAY Noth',
            'k' => 'BAY N.',
            'i' => -1,
            'v' => 0,
        ],
        7 => [
            'c' => '7 ALIT Shai OR',
            'k' => 'OR',
            'i' => -1,
            'v' => 0,
        ],
        9 => [
            'c' => ' 9 ALIT Shai TX HOU',
            'k' => 'HOU',
            'i' => -1,
            'v' => 0,
        ],
        10 => [
            'c' => '10 ALIT Shai TX  DAL',
            'k' => 'DAL',
            'i' => -1,
            'v' => 0,
        ]
    ];

    public function init() {
        $this->path_obj = new \controllers\Path();
        $this->path = $this->path_obj->get(true);

        $this->email_config_obj = new \controllers\EmailConfig();
        $this->email_config = $this->email_config_obj->get(true);

        $this->email_setting_obj = new \controllers\EmailSetting();
        $this->email_setting = $this->email_setting_obj->get(true);

        $this->whatsapp_obj = new \controllers\WhatsApp();
        $this->whatsapp = $this->whatsapp_obj->get(true);

        $this->email_setting->shai1->open = false;
        $this->email_setting->shai2->open = false;
        $this->email_setting->palm1->open = false;

        $this->email_setting_obj->save($this->email_setting);
        $this->email_setting = $this->email_setting_obj->get(true);
    }

    public function set_shai1_setting()
    {
        $this->email_setting->shai1->open = true;

        if ($this->path->csv_previous_path === '') {
            echo json_encode(array('status' => 'error', 'text' => 'csv previous path is invalid.'));
            exit;
        }
        
        $folder_path = $this->path->csv_previous_path;
        $sp = explode('\\', $folder_path);
        $folder_name = $sp[count($sp) - 1];
        $file_name = '00_ALL_' . $folder_name . '_CA Window Door.csv';
        $file_path = $folder_path . '\\' . $file_name;
        if (!file_exists($file_path)) {
            echo json_encode(array('status' => 'error', 'text' => $file_name . " doesn't exist."));
            exit;
        }
        $this->email_setting->shai1->subject = $folder_name;
        $this->email_setting->shai1->files = [['path' => $file_path, 'name' => $file_name]];

        $this->email_setting_obj->save($this->email_setting);
        echo json_encode(array('status' => 'success'));
    }

    public function set_shai2_setting()
    {
        $this->email_setting->shai2->open = true;
        $this->email_setting->shai2->files = [];
        
        if ($this->path->csv_previous_path === '') {
            echo json_encode(array('status' => 'error', 'text' => 'csv previous path is invalid.'));
            exit;
        }

        $folder_path = $this->path->csv_previous_path;
        $sp = explode('\\', $folder_path);
        $folder_name = $sp[count($sp) - 1];

        $file_name1 = '01_ALL_' . $folder_name . '_KitchenBathDecksRenovate.csv';
        $file_path1 = $folder_path . '\\' . $file_name1;
        if (!file_exists($file_path1)) {
            echo json_encode(array('status' => 'error', 'text' => $file_name1 . " doesn't exist."));
            exit;
        }
        array_push($this->email_setting->shai2->files, ['path' => $file_path1, 'name' => $file_name1]);

        $file_name2 = '02_LA_' . $folder_name . '.csv';
        $file_path2 = $folder_path . '\\' . $file_name2;
        if (!file_exists($file_path2)) {
            echo json_encode(array('status' => 'error', 'text' => $file_name2 . " doesn't exist."));
            exit;
        }
        array_push($this->email_setting->shai2->files, ['path' => $file_path2, 'name' => $file_name2]);

        $file_name3 = '03_SD_' . $folder_name . '.csv';
        $file_path3 = $folder_path . '\\' . $file_name3;
        if (!file_exists($file_path3)) {
            echo json_encode(array('status' => 'error', 'text' => $file_name3 . " doesn't exist."));
            exit;
        }
        array_push($this->email_setting->shai2->files, ['path' => $file_path3, 'name' => $file_name3]);

        $file_name4 = '04_WA_' . $folder_name . '.csv';
        $file_path4 = $folder_path . '\\' . $file_name4;
        if (!file_exists($file_path4)) {
            echo json_encode(array('status' => 'error', 'text' => $file_name4 . " doesn't exist."));
            exit;
        }
        array_push($this->email_setting->shai2->files, ['path' => $file_path4, 'name' => $file_name4]);

        $file_name5 = '05_BAY_' . $folder_name . ' South.csv';
        $file_path5 = $folder_path . '\\' . $file_name5;
        if (!file_exists($file_path5)) {
            echo json_encode(array('status' => 'error', 'text' => $file_name5 . " doesn't exist."));
            exit;
        }
        array_push($this->email_setting->shai2->files, ['path' => $file_path5, 'name' => $file_name5]);

        $file_name6 = '06_BAY_' . $folder_name . ' North.csv';
        $file_path6 = $folder_path . '\\' . $file_name6;
        if (!file_exists($file_path6)) {
            echo json_encode(array('status' => 'error', 'text' => $file_name6 . " doesn't exist."));
            exit;
        }
        array_push($this->email_setting->shai2->files, ['path' => $file_path6, 'name' => $file_name6]);

        $file_name7 = '07_OR_' . $folder_name . '.csv';
        $file_path7 = $folder_path . '\\' . $file_name7;
        if (!file_exists($file_path7)) {
            echo json_encode(array('status' => 'error', 'text' => $file_name7 . " doesn't exist."));
            exit;
        }
        array_push($this->email_setting->shai2->files, ['path' => $file_path7, 'name' => $file_name7]);

        $file_name9 = '09_TX_Houston_' . $folder_name . '.csv';
        $file_path9 = $folder_path . '\\' . $file_name9;
        if (!file_exists($file_path9)) {
            echo json_encode(array('status' => 'error', 'text' => $file_name9 . " doesn't exist."));
            exit;
        }
        array_push($this->email_setting->shai2->files, ['path' => $file_path9, 'name' => $file_name9]);

        $file_name10 = '10_TX_Dallas_' . $folder_name . '.csv';
        $file_path10 = $folder_path . '\\' . $file_name10;
        if (!file_exists($file_path10)) {
            echo json_encode(array('status' => 'error', 'text' => $file_name10 . " doesn't exist."));
            exit;
        }
        array_push($this->email_setting->shai2->files, ['path' => $file_path10, 'name' => $file_name10]);

        date_default_timezone_set('America/Los_Angeles');

        $sp = explode(' ', $folder_name);
        $date = substr($sp[0], 0, 2) . '/' . substr($sp[0], 2, 2) . '/' . substr($sp[0], 4, 4);
        $time = $sp[1];

        $client = new \Google_Client();
        $client->setApplicationName('Google Sheets and PHP');
        $client->setScopes([\Google_Service_Sheets::SPREADSHEETS]);
        $client->setAccessType('offline');
        $client->setAuthConfig(__DIR__ . '/../credentials.json');
        $service = new Google_Service_Sheets($client);

        $url_array = parse_url($this->path->schedule);
        $path_array = explode("/", $url_array["path"]);

        $spreadsheetId = $path_array[3];
        $spreadSheet = $service->spreadsheets->get($spreadsheetId);
        $sheets = $spreadSheet->getSheets();

        $cur_sheet = [];
        foreach($sheets as $sheet) {
            $sheetId = $sheet['properties']['sheetId'];

            $pos = strpos($this->path->schedule, "gid=" . $sheetId);

            if($pos) {
                $cur_sheet = $sheet;
                break;
            }
        }

        $response = $service->spreadsheets_values->get($spreadsheetId, $cur_sheet['properties']['title']);
        $schedules = $response->getValues();

        foreach($schedules as $i => $v) {
            foreach($v as $j => $r) {
                foreach($this->a_counts as $k => $a_c) {
                    if ($a_c['c'] == $r) {
                        $this->a_counts[$k]['i'] = $j;
                    }
                }
            }
        }
        
        $date_info = getDate(strtotime($date));

        if ($date_info['wday'] == 4) {
            $cur_date_index = -1;
            $cur_weekday_index = -1;
            $cur_index = -1;
            $cur_row = [];

            foreach ($schedules as $i => $v) {
                foreach($v as $j => $r) {
                    if ($r == $date) {
                        $cur_date_index = $i;
                    }
                }
                if ($cur_date_index !== -1) {
                    foreach($v as $j => $r) {
                        if (strpos($r, '8AM') > 0) {
                            $cur_weekday_index = $i;
                        }
                    }
                }
                if ($cur_date_index !== -1 && $cur_date_index === $cur_weekday_index) {
                    $cur_index = $i; $cur_row = $v;
                    break;
                }
            }
            if ($cur_index !== -1) {
                foreach($this->a_counts as $i => $a_c) {

                    $this->a_counts[$i]['v'] = $cur_row[$a_c['i']];
                }
            }
            
            if ($time == "2PM" || $time == "5PM") {
                $cur_date_index = -1;
                $cur_weekday_index = -1;
                $cur_index = -1;
                $cur_row = [];
                foreach ($schedules as $i => $v) {
                    foreach($v as $j => $r) {
                        if ($r == $date) {
                            $cur_date_index = $i;
                        }
                    }
                    if ($cur_date_index !== -1) {
                        foreach($v as $j => $r) {
                            if (strpos($r, '2PM') > 0) {
                                $cur_weekday_index = $i;
                            }
                        }
                    }
                    if ($cur_date_index !== -1 && $cur_date_index === $cur_weekday_index) {
                        $cur_index = $i; $cur_row = $v;
                        break;
                    }
                }
                if ($cur_index !== -1) {
                    foreach($this->a_counts as $i => $a_c) {
                        $this->a_counts[$i]['v'] = $this->a_counts[$i]['v'] . ' ' . $cur_row[$a_c['i']];
                    }
                }
            }

            if ($time == "5PM") {
                $cur_date_index = -1;
                $cur_weekday_index = -1;
                $cur_index = -1;
                $cur_row = [];
                foreach ($schedules as $i => $v) {
                    foreach($v as $j => $r) {
                        if ($r == $date) {
                            $cur_date_index = $i;
                        }
                    }
                    if ($cur_date_index !== -1) {
                        foreach($v as $j => $r) {
                            if (strpos($r, '5PM') > 0) {
                                $cur_weekday_index = $i;
                            }
                        }
                    }
                    if ($cur_date_index !== -1 && $cur_date_index === $cur_weekday_index) {
                        $cur_index = $i; $cur_row = $v;
                        break;
                    }
                }
                if ($cur_index !== -1) {
                    foreach($this->a_counts as $i => $a_c) {
                        $this->a_counts[$i]['v'] = $this->a_counts[$i]['v'] . ' ' . $cur_row[$a_c['i']];
                    }
                }
            }
        } else {
            $cur_index = -1;
            $cur_row = [];
            foreach ($schedules as $i => $v) {
                foreach($v as $j => $r) {
                    if ($r == $date) {
                        $cur_index = $i;
                        $cur_row = $v;
                    }
                }
            }

            if ($cur_index !== -1) {
                foreach($this->a_counts as $i => $a_c) {
                    if ($time == "5PM") {
                        $this->a_counts[$i]['v'] = $cur_row[$a_c['i']];
                    } else if ($time == "2PM") {
                        $sp1 = explode(' ', $cur_row[$a_c['i']]);
                        $this->a_counts[$i]['v'] = $sp1[0] . ' ' . $sp1[1];
                    } else {
                        $sp1 = explode(' ', $cur_row[$a_c['i']]);
                        $this->a_counts[$i]['v'] = $sp1[0];
                    }
                }
            }
        }

        $this->email_setting->shai2->subject = $folder_name;
        $body = "<table border='0' cellpadding='0' cellspacing='0' width='726' style='border-collapse:collapse;width:539pt'>
                        <colgroup><col width='66' span='11' style='width:49pt'>
                        </colgroup>
                        <tbody>
                            <tr height='18' style='height:13.4pt'>
                                <td height='18' width='66' style='height:13.4pt;width:49pt;font-size:9pt;font-weight:700;font-family:Calibri,sans-serif;text-align:center;border:1pt solid silver;padding-top:1px;padding-right:1px;padding-left:1px;color:windowtext;vertical-align:bottom'>". $this->a_counts[0]['v'] ."</td>";

        for ($i = 1; $i < 11; $i++) {
            if ($i === 8) continue;
            $body .= "<td width='66' style='border-left:none;width:49pt;font-size:9pt;font-weight:700;font-family:Calibri,sans-serif;text-align:center;border-top:1pt solid silver;border-right:1pt solid silver;border-bottom:1pt solid silver;padding-top:1px;padding-right:1px;padding-left:1px;color:windowtext;vertical-align:bottom'>". $this->a_counts[$i]['v'] ."</td>";
        }

        $body .= "</tr>";
        $body .= "<tr height='18' style='height:13.4pt'>
                            <td height='18' width='66' style='height:13.4pt;border-top:none;width:49pt;font-size:9pt;font-weight:700;font-family:Calibri,sans-serif;text-align:center;border-right:1pt solid silver;border-bottom:1pt solid silver;border-left:1pt solid silver;padding-top:1px;padding-right:1px;padding-left:1px;color:windowtext;vertical-align:bottom'>". $this->a_counts[0]['k'] ."</td>";
        for ($i = 1; $i < 11; $i++) {
            if ($i === 8) continue;
            $body .= "<td width='66' style='border-top:none;border-left:none;width:49pt;font-size:9pt;font-weight:700;font-family:Calibri,sans-serif;text-align:center;border-right:1pt solid silver;border-bottom:1pt solid silver;padding-top:1px;padding-right:1px;padding-left:1px;color:windowtext;vertical-align:bottom'>". $this->a_counts[$i]['k'] ."</td>";
        }
        $body .= "</tr></tbody></table>";
        $this->email_setting->shai2->body = $body;

        $this->email_setting_obj->save($this->email_setting);
        echo json_encode(array('status' => 'success'));
    }

    public function set_palm1_setting()
    {
        $this->email_setting->palm1->open = true;

        if ($this->path->xls_previous_path === '') {
            echo json_encode(array('status' => 'error', 'text' => 'xls previous path is invalid.'));
            exit;
        }
        $folder_path = $this->path->xls_previous_path;
        $sp = explode('\\', $folder_path);
        $folder_name = $sp[count($sp) - 1];
        $file_name = $folder_name . '_PALM.xls';
        $file_path = $folder_path . '\\' . $file_name;
        if (!file_exists($file_path)) {
            echo json_encode(array('status' => 'error', 'text' => $file_name . " doesn't exist."));
            exit;
        }

        $this->email_setting->palm1->subject = $folder_name;
        $this->email_setting->palm1->files = [['path' => $file_path, 'name' => $file_name]];

        $this->email_setting_obj->save($this->email_setting);
        echo json_encode(array('status' => 'success'));
    }

    public function send()
    {
        $this->email_setting->shai1->open = false;
        $this->email_setting->shai2->open = false;
        $this->email_setting->palm1->open = false;

        $name = $_REQUEST['name'];

        foreach($_REQUEST['rows'] as $k => $v) {
            $this->email_setting->$name->$k = $v;
        }

        foreach($this->email_setting->$name->receivers as $r) {
            $mail = new PHPMailer();

            $mail->SMTPDebug = 0;
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com;';
            $mail->SMTPAuth   = true;
            $mail->Username   = $this->email_config->sender;   // Enter your gmail-id
            $mail->Password   = $this->email_config->password;     // Enter your gmail app password that you generated
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;

            $mail->setFrom($this->email_config->sender); // This mail-id will be same as your gmail-id
            $mail->addAddress($r);      // Enter your reciever email-id

            $mail->isHTML(true);

            $mail->Subject = $this->email_setting->$name->subject;
            foreach($this->email_setting->$name->files as $f) {
                $mail->AddAttachment($f->path, $f->name);
            }

            $mail->Body = $this->email_setting->$name->body;

            $mail->send();
        }

        $this->email_setting_obj->save($this->email_setting);

        if ($this->whatsapp->isWhatsApp == '' || $this->whatsapp->isWhatsApp == true || $this->whatsapp->isWhatsApp == 'true')
            $this->whatsapp_obj->send($name, $this->email_setting->$name);

        echo json_encode(array('status' => 'success'));
        exit;
    }
}