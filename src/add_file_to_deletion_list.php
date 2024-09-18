<?php
if (isset($_POST['file'])) {
    $filePath = $_POST['file'];
    $listFile = realpath(__DIR__ . '/../files_to_delete.txt');
    
    if (!empty($filePath)) {
        touch($filePath);

        $currentContent = file_get_contents($listFile);

        if (strpos($currentContent, $filePath) === false) {
            $currentDate = date('Y-m-d H:i:s');
            $entry = $filePath . ',' . $currentDate . PHP_EOL;
            file_put_contents($listFile, $entry, FILE_APPEND | LOCK_EX);
        }
    }
}
?>
