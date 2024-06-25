<?php
function logMessage($message) {
    $logFile = 'log.txt';
    $currentDateTime = date('Y-m-d H:i:s');
    $formattedMessage = "[{$currentDateTime}] - {$message}\n";
    file_put_contents($logFile, $formattedMessage, FILE_APPEND);
}
?>
<!-- log file -->