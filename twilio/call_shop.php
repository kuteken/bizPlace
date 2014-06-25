<?php
require('Services/Twilio.php');
include('../config/config.php');
include('../config/shop_list.php');

$client     = new Services_Twilio($AccountSid, $AuthToken);
$response   = new Services_Twilio_Twiml();

$data = formatData($_REQUEST);
$shop_number = $shops[0]['tel'];

if ($data) {
    $query = '?' . join($data, '&');
    $url   = $base_url . 'twilio/call_twiml.php' . $query;
    $call  = $client->account->calls->create($call_number, $shop_number, $url, array());
    // $call->sid;
    $complete_url = $base_url . 'complete.html';
    header("location: $complete_url");
} else {
    header("location: $base_url");
}

function formatData($request) {
    if (preg_match('/^0(\d|-)*$/', $request['tel'])) {
        $params['tel'] = preg_replace('/^0/', '81', $request['tel']);
    } else {
        return false;
    }

    foreach (array('smoking', 'wifi', 'power') as $condition_name) {
        if (count($request[$condition_name]) == 0) {
            return false;
        } elseif (count($request[$condition_name]) == 1) {
            $params[$condition_name] = $request[$condition_name][0];
        }
    }

    foreach ($params as $key => $val) {
        $data[$key] = "$key=$val";
    }

    return $data;
}
