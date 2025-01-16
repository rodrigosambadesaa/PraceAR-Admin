<?php

// require_once('constants.php');
require_once(VIRUSTOTAL_API_KEY_FILE);

function check_virus_total($file)
{
    // Verifica con la API de VirusTotal si el archivo es malicioso
    $total = 0;
    $apiKey = VIRUSTOTAL_API_KEY;
    $url = 'https://www.virustotal.com/vtapi/v2/file/scan';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, 'apikey=' . $apiKey . '&file=' . $file);
    $result = curl_exec($ch);

    $positives = json_decode($result, true);
    if (isset($positives['positives'])) {
        $total = $positives['positives'];
    }

    if ($total > 0) {
        return true;
    } else {
        return false;
    }
}