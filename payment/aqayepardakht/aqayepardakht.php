<?php
ini_set('error_log', 'error_log');
$Pathfiles = dirname(dirname(__DIR__ ));
require_once $Pathfiles.'/config.php';
require_once $Pathfiles.'/functions.php';
require_once $Pathfiles.'/text.php';
$amount =     htmlspecialchars($_GET['price'], ENT_QUOTES, 'UTF-8');;
$invoice_id = htmlspecialchars($_GET['order_id'], ENT_QUOTES, 'UTF-8');;
$PaySetting = select("PaySetting", "ValuePay", "NamePay", "merchant_id_aqayepardakht","select")['ValuePay'];
$checkprice = select("Payment_report", "price", "id_order", $invoice_id,"select")['price'];
// Send Parameter
if($checkprice !=$amount){
    echo $textbotlang['users']['moeny']['invalidprice'];
    return;
}
$data = [
    'pin'    => $PaySetting,
    'amount'    => $amount,
    'callback' => $domainhosts."/payment/aqayepardakht/back.php",
    'invoice_id' => $invoice_id,
];

$data = json_encode($data);
$ch = curl_init('https://panel.aqayepardakht.ir/api/v2/create');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLINFO_HEADER_OUT, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen($data))
);
$result = curl_exec($ch);
curl_close($ch);
$result = json_decode($result);
if ($result->status == "success") {
    header('Location: https://panel.aqayepardakht.ir/startpay/' . $result->transid);
} else {
    $status_pay = [
        '-1' => "amount不能为空",
        '-2' => "网关PIN码不能为空", 
        '-3' => "callback不能为空",
        '-4' => "amount必须为数字",
        '-5' => "amount必须在1,000到100,000,000美元之间",
        '-6' => "网关PIN码错误",
        '-7' => "transid不能为空", 
        '-8' => "未找到相关交易",
        '-9' => "网关PIN码与交易网关不匹配",
        '-10' => "金额与交易金额不匹配",
        '-11' => "网关等待确认或已禁用",
        '-12' => "该收款人无法提交请求",
        '-13' => "卡号必须是16位连续数字",
        '-14' => "网关正在其他网站上使用"

    ][$result->code];
    echo $status_pay;
}

