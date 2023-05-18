<?php
require_once('../vendor/autoload.php');

$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
$reader->setReadDataOnly(true);
$spreadsheet = $reader->load($_FILES["file"]["tmp_name"]);

$worksheet = $spreadsheet->getActiveSheet();

$array = [];
foreach ($worksheet->getRowIterator() as $rowKey => $row) {

    if(!$worksheet->getCellByColumnAndRow(1, $rowKey)->getValue()) continue;

    $cellIterator = $row->getCellIterator();
    //$cellIterator->setIterateOnlyExistingCells(true); //не работает

    $cells = [];
    foreach ($cellIterator as $cell) {
        $cells[] = $cell->getValue();
    }
    $array[] = $cells;
}

echo json_encode($array);
