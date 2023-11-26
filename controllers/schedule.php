<?php

namespace controllers;

error_reporting(E_ERROR);

require __DIR__ . '/../vendor/autoload.php';

require_once('Path.php');

use Google_Client;
use Google_Service_Sheets;
use Google_Service_Sheets_ValueRange;

class Schedule
{
    public function init()
    {
        
    }

    public function upload()
    {
        $path_obj = new \controllers\Path();
        $path = $path_obj->get(true);

        $folder = $path->folder_name;
        $time = $path->download_time;
        $download_type = $path->download_way;

        $sp = explode(' ', $folder);
        $date = substr($sp[0], 0, 2) . '/' . substr($sp[0], 2, 2) . '/' . substr($sp[0], 4, 4);
        $date_info = getDate(strtotime($date));

        $main_file = 'db/main.json';
        $mains = json_decode(file_get_contents($main_file), true);

        $shais = ($download_type === 'all' || $download_type === 'shai') ? $mains['shais'] : [];
        $palms = ($download_type === 'all' || $download_type === 'palm') ? $mains['palms'] : [];

        $client = new \Google_Client();
        $client->setApplicationName('Google Sheets and PHP');
        $client->setScopes([\Google_Service_Sheets::SPREADSHEETS]);
        $client->setAccessType('offline');
        $client->setAuthConfig(__DIR__ . '/../credentials.json');
        $service = new Google_Service_Sheets($client);

        $url_array = parse_url($path->schedule);
        $path_array = explode("/", $url_array["path"]);

        $spreadsheetId = $path_array[3];
        $spreadSheet = $service->spreadsheets->get($spreadsheetId);
        $sheets = $spreadSheet->getSheets();

        $cur_sheet = [];
        foreach($sheets as $sheet) {
            $sheetId = $sheet['properties']['sheetId'];

            $pos = strpos($path->schedule, "gid=" . $sheetId);

            if($pos) {
                $cur_sheet = $sheet;
                break;
            }
        }

        $response = $service->spreadsheets_values->get($spreadsheetId, $cur_sheet['properties']['title']);
        $schedules = $response->getValues();

        $cur_schedule_index = -1;
        $cur_schedule = [];

        foreach($schedules as $i => $v) {
            foreach($v as $j => $r) {
                if (strtotime($date) == strtotime(date($r))) {
                    $cur_schedule_index = $i;
                    $cur_schedule = $v;
                }

                foreach($shais as $k => $shai) {
                    if ($shai['schedule'] == $r) {
                        $shais[$k]['schedule_index'] = $j;
                    }
                }

                foreach($palms as $k => $palm) {
                    if ($palm['schedule'] == $r) {
                        $palms[$k]['schedule_index'] = $j;
                    }
                }
            }
        }

        $rows = [];
        foreach ($shais as $shai) {
            if ($shai['isUploadCount'] == 'true' || $shai['isUploadCount'] == true) {
                $rows[] = $shai;   
            }
        }

        foreach ($palms as $palm) {
            if ($palm['isUploadCount'] == 'true' || $shai['isUploadCount'] == true) {
                $rows[] = $palm;   
            }
        }

        if ($date_info['wday'] == 4) {
            $name = $date_info['weekday'] . ' ' . $time;

            $cur_schedule = [];
            $cur_schedule_index = -1;

            foreach($schedules as $i => $v) {
                $cur_date_index = -1;
                $cur_name_index = -1;

                foreach($v as $j => $r) {
                    if (strtotime($date) == strtotime(date($r))) {
                        $cur_date_index = $i;
                    }

                    if ($name == $r) {
                        $cur_name_index = $i;
                    }
                }

                if ($cur_date_index != -1 && $cur_date_index == $cur_name_index) {
                    $cur_schedule = $v;
                    $cur_schedule_index = $i;
                }
            }

            $row = ['', $date, $name];

            for ($i = 3; $i < 100; $i++) {
                $ext = false;
                foreach($rows as $c_index => $c){
                    if ($i == $c['schedule_index']) {
                        if ($cur_schedule_index !== -1) {
                            if ($cur_schedule[$i]) {
                                if (strpos($cur_schedule[$i], '+') !== false) {
                                    $row[] = $cur_schedule[$i] . '+' . $c['schedule_count'];
                                } else {
                                    if ($cur_schedule[$i] == ' ' || $cur_schedule[$i] == '') {
                                        $row[] = $c['schedule_count'];
                                    } else {
                                        $exp = explode(" ", $cur_schedule[$i]);
                                        if (count($exp) > 2) {
                                            $row[] = $cur_schedule[$i] . ' ' . $c['schedule_count'];
                                        } else {
                                            if ((int)$exp[0] < 13) {
                                                $row[] = $cur_schedule[$i] . '+' . $c['schedule_count'];
                                            } else {
                                                $row[] = $cur_schedule[$i] . ' ' . $c['schedule_count'];
                                            }
                                        }
                                    }
                                }
                            } else {
                                $row[] = $c['schedule_count'];
                            }
                        } else {
                            $row[] = $c['schedule_count'];
                        }
                        $ext = true;
                    }
                }

                if (!$ext) {
                    if (!$cur_schedule[$i]) array_push($row, ' ');
                    else $row[] = $cur_schedule[$i];
                }
            }
        } else {
            $row = ['', $date, $date_info['weekday']];

            for ($i = 3; $i < 100; $i++) {
                $ext = false;
                foreach($rows as $c_index => $c){
                    if ($i == $c['schedule_index']) {
                        if ($cur_schedule_index !== -1) {
                            if ($cur_schedule[$i]) {
                                if (strpos($cur_schedule[$i], '+') !== false) {
                                    $row[] = $cur_schedule[$i] . '+' . $c['schedule_count'];
                                } else {
                                    if ($cur_schedule[$i] == ' ' || $cur_schedule[$i] == '') {
                                        $row[] = $c['schedule_count'];
                                    } else {
                                        $exp = explode(" ", $cur_schedule[$i]);
                                        if (count($exp) > 2) {
                                            $row[] = $cur_schedule[$i] . ' ' . $c['schedule_count'];
                                        } else {
                                            if ((int)$exp[0] < 13) {
                                                $row[] = $cur_schedule[$i] . '+' . $c['schedule_count'];
                                            } else {
                                                $row[] = $cur_schedule[$i] . ' ' . $c['schedule_count'];
                                            }
                                        }
                                    }
                                }
                            } else {
                                $row[] = $c['schedule_count'];
                            }

                        } else {
                            $row[] = $c['schedule_count'];
                        }
                        $ext = true;
                    }
                }

                if (!$ext) {
                    if (!$cur_schedule[$i]) $row[] = ' ';
                    else $row[] = $cur_schedule[$i];
                }
            }
        }

        $body = new Google_Service_Sheets_ValueRange([
            'values' => [$row]
        ]);
        
        $params = [
            'valueInputOption' => 'USER_ENTERED'
        ];

        if ($cur_schedule_index == -1) {
            $update_range = $cur_sheet['properties']['title']. '!' . 'A' . (count($schedules) + 1) . ':' . 'ZZ' . (count($schedules) + 1);
        } else {
            $update_range = $cur_sheet['properties']['title'] . '!' . 'A' . ($cur_schedule_index + 1) . ':' . 'ZZ' . ($cur_schedule_index + 1);
        }

        $update_sheet = $service->spreadsheets_values->update($spreadsheetId, $update_range, $body, $params);

        if ($time === '8AM') {
            $path->download_time = '2PM';
            $path->folder_name = $sp[0] . ' 2PM';
        }

        if ($path->is5pmIgnore == "true") {
            if ($time === '2PM') {
                $date = date("mdY", strtotime('+1 day', strtotime($date)));
                $path->download_time = '8AM';
                $path->folder_name = $date . ' 8AM';
            }
        } else {
            if ($time === '5PM') {
                $date = date("mdY", strtotime('+1 day', strtotime($date)));
                $path->download_time = '8AM';
                $path->folder_name = $date . ' 8AM';
            } else if ($time === '2PM') {
                $path->download_time = '5PM';
                $path->folder_name = $sp[0] . ' 5PM';
            }
        }
            
        $path_obj->save($path);

        echo json_encode(array('status' => 'success'));
        exit;
    }
}



