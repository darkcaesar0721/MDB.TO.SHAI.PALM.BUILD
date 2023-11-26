<?php
error_reporting(E_ERROR);

$lastphone_file = '../db/lastphone.json';
$lastphone = json_decode(file_get_contents($lastphone_file), true);

$path_file = '../db/path.json';
$path = json_decode(file_get_contents($path_file));

$main_file = '../db/main.json';
$mains = json_decode(file_get_contents($main_file), true);
$shais = $mains['shais'];

$mdb_path = $path->mdb_path;
$shai_path = $path->shai_path;

$folder = $path->folder_name;

$shai = $_REQUEST['shai'];
$index = $_REQUEST['index'];

if ($index == 0) $shais = [];

$xls['pre_phone'] = $lastphone[$xls['key']];

try {
    # OPEN BOTH DATABASE CONNECTIONS
    $db = new PDO("odbc:Driver={Microsoft Access Driver (*.mdb, *.accdb)}; DBq=$mdb_path;Uid=;Pwd=;");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($shai['isDownload'] == 'true') {
        $folder_path = $shai_path . "\\" . $folder . "\\";

        if (!file_exists($folder_path)) {
            mkdir($folder_path, 0777, true);
        }
    
        $fp = fopen($folder_path . "\\" . $shai['file'], 'w');
    }

    $query = $shai['query'];
    $sth = $db->prepare("select * from [$query]");
    $sth->execute();

    $shai_rows = [];

    $shai_row = [];
    foreach ($shai['columns'] as $column) {
        $shai_row[] = $column;
    }
    $shai_rows[] = $shai_row;

    if ($shai['isDownload'] == 'true') {
        $dup_rows = [];
        foreach($palm['duplicatedShaiKeys'] as $key) {
            $dupShai = [];
            foreach($shais as $s) {
                if ($s['key'] == $key) $dupShai = $s;
            }

            $shai['schedule_count'] += $dupShai['count'];
            foreach($dupShai['rows'] as $row) {
                fputcsv($fp, $row);
            }
            if (count($dupShai['rows']) !== 0) fputcsv($fp, ['', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '']);
        }

        fputcsv($fp, $shai_row);
    }

    $shai_last_phone = '';
    $shai['count'] = 0;
    while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
        if ($row['Phone'] === $shai['pre_phone']) break;

        if (!$shai_last_phone) $shai_last_phone = $row['Phone'];

        $shai_row = [];
        foreach ($shai['columns'] as $column) {
            $shai_row[] = $row[$column];
        }
        $shai_rows[] = $shai_row;

        if ($shai['isDownload'] == 'true') {
            fputcsv($fp, $shai_row);
        }

        $shai['count'] = $shai['count'] + 1;
    }
    $shai['schedule_count'] += $shai['count'];
    $shai['rows'] = $shai_rows;

    if ($shai_last_phone) {
        $lastphone[$shai['key']] = $shai_last_phone;
        file_put_contents($lastphone_file, json_encode($lastphone));

        $shais[] = $shai;
        file_put_contents($main_file, json_encode(['palms' => $mains['palms'], 'shais' => $shais]));
    }

    if ($shai['isDownload'] == 'true') {
        fclose($fp);
    }

    echo json_encode(array('status' => 'success', 'shai' => $shai, 'lastphone' => $lastphone));

} catch(PDOException $e) {
    echo json_encode(array('status' => 'error', 'description' => 'mdb file path wrong'));
    exit;
}