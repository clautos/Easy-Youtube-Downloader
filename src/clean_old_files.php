<?php
$listFile = realpath(__DIR__ . '/../files_to_delete.txt');

if (file_exists($listFile)) {
    $lines = file($listFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    $remainingFiles = [];
    
    foreach ($lines as $line) {
        list($filePath, $dateString) = explode(',', $line, 2);
        
        if (file_exists($filePath)) {
            $fileModificationTime = filemtime($filePath);
            $currentTime = time();
            $entryDate = strtotime($dateString);

            if (($currentTime - $entryDate) > 1500) {
                unlink($filePath);
            } else {
                $remainingFiles[] = $line;
            }
        }
    }
    
    file_put_contents($listFile, implode(PHP_EOL, $remainingFiles) . PHP_EOL);
}
?>
