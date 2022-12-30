<?php

$apiKey = 'dtVuBPgH6E5wjBAFTVe8wrVEsQZemXsCmnJ';
$offer_id = '9790'; // для каждого оффера свой айди, надо уточнять его в админке или у суппортов
$stream_hid = 'M8ztj7vO'; // не обязательное, если указано, заявка будет привязана к потоку
$apiUrl = 'http://api.cpa.tl/api/lead/send';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data_post = $_POST;

    $back_url = "http://135.181.12.179/f106260/postback";
    $back_args = [
        'subid' => $_POST['sub1'],
        'sub_id_14' => $_POST['name'],
        'sub_id_15' => $_POST['phone'],
        'status' => 'lead'
    ];
    $back_urls = $back_url.'?'.http_build_query($back_args);
    $back_curl = curl_init();
    curl_setopt_array($back_curl, array(
        CURLOPT_URL => $back_urls,
        CURLOPT_RETURNTRANSFER => true
    ));
    $back_res = curl_exec($back_curl);
    curl_close($back_curl);


    $data = array(
            'key' => $apiKey,
            'id' => microtime(true), // тут лучше вставить значение, по которому вы сможете идентифицировать свой лид; можно оставить microtime если у вас нет своей crm
            'offer_id' => $offer_id,
            'stream_hid' => $stream_hid,
            'name' => $data_post['name'],
            'phone' => $data_post['phone'],
            'comments' => $data_post['comments'],
            'country' => 'DZ', // формат ISO 3166-1 Alpha-2 - https://ru.wikipedia.org/wiki/ISO_3166-1
            'address' => $data_post['address'],
            'tz' => $data_post['timezone_int'], // очень желательно получать его с ленда, но если никак лучше оставить пустым или 3 (таймзона мск)
            'web_id' => '',
            'ip_address' => isset($_SERVER["HTTP_CF_CONNECTING_IP"]) ? $_SERVER["HTTP_CF_CONNECTING_IP"] : $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'],
            'sub1' => $_POST['sub1'],
    );

    $options = array(
            'http' => array(
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query($data),
                'ignore_errors' => true,
            )
    );

    $context = stream_context_create($options);
    $result = file_get_contents($apiUrl, false, $context);

    // $obj = json_decode($result);

    // echo $result;

    session_start();
    $_SESSION['name'] = $_POST['name'];
    $_SESSION['phone'] = $_POST['phone'];
    $_SESSION['fb_pixel'] = $_POST['fb_pixel'];

    if (null === $result) {
        // Ошибка в полученном ответе
        print("Invalid JSON");
    } else if (!empty($result->errmsg)) {
        // Ошибка в отправленном запросе
        print("Ошибка: " . $result->errmsg);
    } else {
        // print('ID заявки: ' . $obj->id);
        header('location: confirm.php');
    }
}
