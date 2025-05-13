<?php
function parseTaskFile($contents) {
    $lines = explode("\n", trim($contents));
    if (empty($lines)) return null;
    
    $data = str_getcsv($lines[0]); // Assuming no header row
    
    return [
        'id' => $data[0],
        'GroupName' => $data[1],
        'FileName' => "",
        'DateCreated' => $data[3],
        'DateUpdated' => $data[4],
        'SecondsToComplete' => $data[5],
        'Title' => $data[6],
        'Description' => $data[7],
        'TaskNum' => $data[8],
        'Exists' => 0,    
    ] ;
}
?>