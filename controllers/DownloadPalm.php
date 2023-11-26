<?php
error_reporting(E_ERROR);

require '../vendor/autoload.php';

$lastphone_file = '../db/lastphone.json';
$lastphone = json_decode(file_get_contents($lastphone_file), true);

$path_file = '../db/path.json';
$path = json_decode(file_get_contents($path_file));

$main_file = '../db/main.json';
$mains = json_decode(file_get_contents($main_file), true);
$palms = $mains['palms'];

$mdb_path = $path->mdb_path;
$palm_path = $path->palm_path;

$folder = $path->folder_name;

$palm = $_REQUEST['palm'];
$index = $_REQUEST['index'];

if ($index == 0) $palms = [];

$palm['pre_phone'] = $lastphone[$palm['key']];

try {

    # OPEN BOTH DATABASE CONNECTIONS
    $db = new PDO("odbc:Driver={Microsoft Access Driver (*.mdb, *.accdb)}; DBq=$mdb_path;Uid=;Pwd=;");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $query = $palm['query'];
    $sth = $db->prepare("select * from [$query]");
    $sth->execute();

    $palm_rows = [];

    if ($palm['isDownload'] != 'true') {
        $palm_rows[] = ['Date', 'Phone', 'Name', 'Address', 'City', 'State', 'Zip', 'Job Group', 'County'];
    }

    $palm_last_phone = '';

    $palm['count'] = 0;
    while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
        if ($row['Phone'] == $palm['pre_phone']) break;

        if (!$palm_last_phone) $palm_last_phone = $row['Phone'];

        $palm_row = [];
        foreach($palm['columns'] as $column) {
            $palm_row[] = $row[$column];
        }

        $palm_rows[] = $palm_row;

        $palm['count'] = $palm['count'] + 1;
    }
    $palm['schedule_count'] = $palm['count'];
    $palm['rows'] = $palm_rows;

    if ($palm_last_phone) {
        $lastphone[$palm['key']] = $palm_last_phone;
        file_put_contents($lastphone_file, json_encode($lastphone));
    }

    if ($palm['isDownload'] == 'true') {
        $rows = [];
        foreach($palm['duplicatedPalmKeys'] as $key) {
            $dupPalm = [];
            foreach($palms as $p) {
                if ($p['key'] == $key) $dupPalm = $p;
            }
            
            $palm['schedule_count'] += $dupPalm['count'];
            foreach($dupPalm['rows'] as $row) {
                $rows[] = $row;
            }
            // if (count($dupPalm['rows']) !== 0) $rows[] = ['', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''];
        }

        foreach($palm_rows as $row) {
            $rows[] = $row;
        }

        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();    

        $folder_path = $palm_path . "\\" . $folder . "\\";

        if (!file_exists($folder_path)) {
            mkdir($folder_path, 0777, true);
        }

        if ($palm['isDownload'] == 'true' && $palm['isCreateFile'] == 'true') {
            $mySpreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

            $mySpreadsheet->removeSheetByIndex(0);
        } else {
            $mySpreadsheet = $reader->load($folder_path . "\\" . $folder . '_PALM.xls');
        }

        $worksheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($mySpreadsheet, $palm['sheet']);
        $mySpreadsheet->addSheet($worksheet, $index);
    
        $worksheet->fromArray($rows);
    
        $worksheet->getStyle('A1:' . 'N' . ($palm['schedule_count'] + 3))->getFont()->setBold(true)
            ->setName('Arial')
            ->setSize(8);
    
        $mySpreadsheet->setActiveSheetIndex(0);
    
        // Save to file.
        $writer = new PhpOffice\PhpSpreadsheet\Writer\Xls($mySpreadsheet);
        $writer->save($folder_path . "\\" . $folder . '_PALM.xls');
    }

    $palms[] = $palm;
    file_put_contents($main_file, json_encode(['palms' => $palms, 'shais' => $mains['shais']]));

    echo json_encode(array('status' => 'success', 'palm' => $palm, 'lastphone' => $lastphone));

} catch(PDOException $e) {

    echo json_encode(array('status' => 'error', 'description' => 'mdb file path wrong'));
    exit;

}