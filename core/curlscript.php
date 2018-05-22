<?php

if (php_sapi_name() === "cli") {
    
$options = getopt("f:t:s:x:a:p:");

$from = $options['f'];
$to = $options['t'];
$text = $options['x'];
$apiurl = $options['a'];
$apistring = $options['p'];
$subject = $options['s'];

$html = file_get_contents("php://stdin");



$ch = curl_init();
                curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                curl_setopt($ch, CURLOPT_USERPWD, $apistring);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
                curl_setopt($ch, CURLOPT_URL, $apiurl);
                curl_setopt($ch, CURLOPT_POSTFIELDS, array('from' => $from,
                    'to' => $to,
                    'subject' => $subject,
                    'html' => $html,
                    'text' => $text
                    ));
                $kk = curl_exec($ch);
echo $kk;

}else{
   
    
}
?>