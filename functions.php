<?php
function sendLiveSMS($phoneNumber, $message) {
    // Notify.lk Credentials 
    $user_id = "31655";
    $api_key = "bmIz1PMZ0aIrPQxjXhRM";
    $sender_id = "NotifyDEMO";

    $phoneNumber = trim($phoneNumber);

$phoneNumber = preg_replace('/^0/', '94', $phoneNumber); 
    if(empty($phoneNumber)) {
        return '{"status":"error","message":"Phone number is missing"}';
    }

    
$url = "https://app.notify.lk/api/v1/send?user_id=" . $user_id . 
       "&api_key=" . $api_key . 
       "&to=" . $phoneNumber . 
       "&sender_id=" . $sender_id . 
       "&message=" . urlencode($message) .
       "&unicode=1"; 

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Localhost 

    $response = curl_exec($ch);
    curl_close($ch);

    return $response;
}
?>