<?php
error_reporting(E_ERROR);

$lastphone_file = '../db/lastphone.json';
$lastphone = json_decode(file_get_contents($lastphone_file), true);

$path_file = '../db/path.json';
$path = json_decode(file_get_contents($path_file));

$mdb_path = $path->mdb_path;
$csv_path = $path->csv_path;
$csv_previous_path = $path->csv_previous_path;

$folder = $path->folder_name;

$csv = $_REQUEST['data'];
$index = $_REQUEST['index'];

$xls['pre_phone'] = $lastphone[$xls['phone_key']];

try {
    # OPEN BOTH DATABASE CONNECTIONS
    $db = new PDO("odbc:Driver={Microsoft Access Driver (*.mdb, *.accdb)}; DBq=$mdb_path;Uid=;Pwd=;");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $folder_path = $csv_path . "\\" . $folder . "\\";

    if (!file_exists($folder_path)) {
        mkdir($folder_path, 0777, true);
    }

    $query = $csv['query'];
    $sth = $db->prepare("select * from [$query]");
    $sth->execute();

    $fp = fopen($folder_path . "\\" . $csv['file'], 'w');

    if (strpos($csv['file'], "10") === 0 || strpos($csv['file'], "09") === 0 || strpos($csv['file'], "08") === 0 || strpos($csv['file'], "05") === 0) {
        fputcsv($fp, array('Name', 'Address', 'City', 'State', 'Zip', 'Phone', 'Job Group1'));
    } else if (strpos($csv['file'], "06") === 0) {
        fputcsv($fp, array('Name', 'Address', 'City', 'State', 'Zip', 'Phone', 'Job Group1', 'County1'));
    } else if (strpos($csv['file'], "01") === 0) {
        fputcsv($fp, array('Name', 'Address', 'City', 'State', 'Zip', 'Phone', 'Job Group', 'County1'));
    } else {
        fputcsv($fp, array('Name', 'Address', 'City', 'State', 'Zip', 'Phone', 'Job Group'));
    }

    $csv_last_phone = '';
    $csv['count'] = 0;
    while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
        if ($row['Phone'] === $csv['pre_phone']) break;

        if (!$csv_last_phone) $csv_last_phone = $row['Phone'];

        if (strpos($csv['file'], "10") === 0 || strpos($csv['file'], "09") === 0 || strpos($csv['file'], "08") === 0 || strpos($csv['file'], "05") === 0) {
            fputcsv($fp, array($row['Name'], $row['Address'], $row['City'], $row['State'], $row['Zip'], $row['Phone'], $row['Job Group1']));
        } else if (strpos($csv['file'], "06") === 0) {
            fputcsv($fp, array($row['Name'], $row['Address'], $row['City'], $row['State'], $row['Zip'], $row['Phone'], $row['Job Group1'], $row['County1']));
        } else if (strpos($csv['file'], "01") === 0) {
            fputcsv($fp, array($row['Name'], $row['Address'], $row['City'], $row['State'], $row['Zip'], $row['Phone'], $row['Job Group'], $row['County1']));
        } else {
            fputcsv($fp, array($row['Name'], $row['Address'], $row['City'], $row['State'], $row['Zip'], $row['Phone'], $row['Job Group']));
        }

        $csv['count'] = $csv['count'] + 1;
    }

    if ($csv_last_phone) {
        $lastphone[$xls['phone_key']] = $csv_last_phone;
        file_put_contents($lastphone_file, json_encode($lastphone));
    }

    fclose($fp);

    echo json_encode(array('status' => 'success', 'csv' => $csv, 'lastphone' => $lastphone));

} catch(PDOException $e) {
    echo json_encode(array('status' => 'error', 'description' => 'mdb file path wrong'));
    exit;
}