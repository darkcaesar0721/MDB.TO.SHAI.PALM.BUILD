<?php
error_reporting(E_ERROR);

require '../vendor/autoload.php';

$lastphone_file = '../db/lastphone.json';
$lastphone = json_decode(file_get_contents($lastphone_file), true);

$path_file = '../db/path.json';
$path = json_decode(file_get_contents($path_file));

$mdb_path = $path->mdb_path;
$xls_path = $path->xls_path;
$xls_previous_path = $path->xls_previous_path;

$folder = $path->folder_name;

$xls = $_REQUEST['data'];
$index = $_REQUEST['index'];

$xls['pre_phone'] = $lastphone[$xls['phone_key']];

try {

    # OPEN BOTH DATABASE CONNECTIONS
    $db = new PDO("odbc:Driver={Microsoft Access Driver (*.mdb, *.accdb)}; DBq=$mdb_path;Uid=;Pwd=;");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();    

    $folder_path = $xls_path . "\\" . $folder . "\\";

    if (!file_exists($folder_path)) {
        mkdir($folder_path, 0777, true);
    }

    if ($index * 1 === 0) {
        $mySpreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

        $mySpreadsheet->removeSheetByIndex(0);
    } else {
        $mySpreadsheet = $reader->load($folder_path . "\\" . $folder . '_PALM.xls');
    }

    $worksheets = [];

    $query = $xls['query'];
    $sth = $db->prepare("select * from [$query]");
    $sth->execute();

    $worksheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($mySpreadsheet, $xls['sheet']);
    $mySpreadsheet->addSheet($worksheet, $index);

    $data = [];

    if ($index * 1 === 5) {
        array_push($data, ['Date', 'Phone', 'Name', 'Address', 'City', 'State', 'Zip', 'Job Group', 'County']);
    } else {
        if ($index * 1 === 3)
            array_push($data, ['Date', 'Phone', 'Name', 'Address', 'City', 'State', 'Zip', 'Job Group', 'COUNTY.COUNTY']);
        else
            array_push($data, ['Date', 'Phone', 'Name', 'Address', 'City', 'State', 'Zip', 'Job Group', 'COUNTY']);
    }

    $tab_last_phone = '';

    $xls['count'] = 0;
    while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
        if ($row['Phone'] == $xls['pre_phone']) break;

        if (!$tab_last_phone) $tab_last_phone = $row['Phone'];

        if ($index * 1 === 5) {
            array_push($data, [$row['Date'], $row['Phone'], $row['Name'], $row['Address'], $row['City'], $row['State'], $row['Zip'], $row['Job Group'], $row['County']]);
        } else {
            if ($index * 1 === 3)
                array_push($data, [$row['Date'], $row['Phone'], $row['Name'], $row['Address'], $row['City'], $row['State'], $row['Zip'], $row['Job Group'], $row['COUNTY.COUNTY']]);
            else
                array_push($data, [$row['Date'], $row['Phone'], $row['Name'], $row['Address'], $row['City'], $row['State'], $row['Zip'], $row['Job Group'], $row['COUNTY']]);
        }

        $xls['count'] = $xls['count'] + 1;
    }

    if ($tab_last_phone) {
        $lastphone[$xls['phone_key']] = $tab_last_phone;
        file_put_contents($lastphone_file, json_encode($lastphone));
    }

    $worksheet->fromArray($data);

    $worksheet->getStyle('A1:' . 'N' . ($xls['count'] + 1))->getFont()->setBold(true)
        ->setName('Arial')
        ->setSize(8);

    $mySpreadsheet->setActiveSheetIndex(0);

    // Save to file.
    $writer = new PhpOffice\PhpSpreadsheet\Writer\Xls($mySpreadsheet);
    $writer->save($folder_path . "\\" . $folder . '_PALM.xls');

    echo json_encode(array('status' => 'success', 'tab' => $xls, 'lastphone' => $lastphone));

} catch(PDOException $e) {

    echo json_encode(array('status' => 'error', 'description' => 'mdb file path wrong'));
    exit;

}