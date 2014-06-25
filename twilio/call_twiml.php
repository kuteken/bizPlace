<?php
require('Services/Twilio.php');
include('../config/config.php');
include('../config/shop_list.php');

$condition = '';
$message = array(
    'contact'   => 'こんにちは。ビズプレイスです。
        %CONDITION%お席を探しているお客様がいらっしゃいます。
        お客様にお電話をお繋ぎしてよろしければ1を押してください。',
    'failed'    => 'お繋ぎしてもよい場合は1を、空き席がない場合はそのままお切りください'
);

$client     = new Services_Twilio($AccountSid, $AuthToken);
$response   = new Services_Twilio_Twiml();

if (isset($_REQUEST['Digits']) && isset($_REQUEST['tel'])) {
    $digits = $_REQUEST['Digits'];

    $customer_number = '+' . $_REQUEST['tel'];

    $shop = $shops[0];

    $map_url = 'http://bit.ly/OkbY5B';
    $sns_message = $shop['name'] . "への地図\n" . $map_url;
    switch ($digits) {
        case '1':
            $response->say('ありがとうございます。お電話をお繋ぎします。', array('language' => 'ja-JP'));
            $response->dial($customer_number);
            $client->account->messages->sendMessage($sms_number, $customer_number, $sns_message);
            break;
        default:
            $gather = $response->gather(array('numDigits' => 1, 'timeout' => '10'));
            $gather->say($message['failed'], array('language' => 'ja-JP'));
            break;
    }
} else {
    $condition = getConditionForMessage($_REQUEST);
    $message['contact'] = preg_replace('/%CONDITION%/', $condition, $message['contact']);
    $gather = $response->gather(array('numDigits' => 1, 'timeout' => '10'));
    $gather->say($message['contact'], array('language' => 'ja-JP'));
}

header ("Content-Type: text/xml; charset=utf-8");
print $response;

function getConditionForMessage($request) {
    $condition = '';
    foreach (array('smoking' => '喫煙', 'wifi' => 'ワイファイ使用', 'power' => 'コンセント使用') as $condition_name => $condition_text) {
        if (isset($request[$condition_name])) {
            if ($request[$condition_name] == 'yes') {
                $condition .= $condition_text . '可能、';
            } elseif ($request[$condition_name] == 'no') {
                $condition .= $condition_text . '不可、';
            }
        }
    }
    if (strlen($condition)) {
        $condition .= 'の、';
    }

    return $condition;
}
