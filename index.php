<?php
//ここに「Channel Access Token」をコピペする
$accessToken = 'VU/MEpm3Wij1KodVUPcORVqQerQjXPfU955rXxUtbIrW+0umiHf8T0SOiIgN9zIuwSS6hK1pLanBHowO+VCYp6v4IGnBmfBbp1tL9oXm8ddHLfqllMzb0Vw2HUCExAp9Z9Mi+EoAEXPqg/wexnRlrQdB04t89/1O/w1cDnyilFU=';
 
//ユーザーからのメッセージ取得
$json_string = file_get_contents('php://input');
$json_object = json_decode($json_string);
 
//取得データ
$replyToken = $json_object->{"events"}[0]->{"replyToken"};        //返信用トークン
$message_type = $json_object->{"events"}[0]->{"message"}->{"type"};    //メッセージタイプ
$message_text = $json_object->{"events"}[0]->{"message"}->{"text"};    //メッセージ内容

$sendType = 0;

//メッセージタイプが「text」以外のときは何も返さず終了
if($message_type != "text") exit;


//返信メッセージ
if($message_text == "てーば"){
    $sendType = 1;
    $return_message_text = "「てばさき」じゃねーよｗｗｗ";
}elseif($message_text == "確認"){
    $sendType = 3;
}else{
    $sendType = 2;
    $return_message_text = "「" . $message_text . "」じゃねーよｗｗｗ";
    
    if($message_text == "愛"){
        
        $code = '1F37A';
        $bin = pack('H*', (str_repeat('0', 8 - strlen($code)) . $code));
        $char =  mb_convert_encoding($bin, 'UTF-8', 'UTF-32BE');
        
        $return_message_text =  $char;
    }
}

//返信実行
sending_messages($accessToken, $replyToken, $message_type, $return_message_text, $sendType);
?>
<?php
//メッセージの送信
function sending_messages($accessToken, $replyToken, $message_type, $return_message_text, $sendType){
    //---
    // 確認ダイアログタイプ
    $send_format_gialog = [
        'type' => 'template',
        'altText' => '確認ダイアログ',  // ここの文字列が、LINEの通知に表示されるはず
        'template' => [
            'type' => 'confirm',
            'text' => '送ってきたメッセージはこれで間違えないですか？',
            'actions' => [
                [
                'type' => 'message',
                'label' => '問題なし',
                'text' => '問題なし'
                ],
                [
                'type' => 'message',
                'label' => '問題あり',
                'text' => '問題あり'
                ],
            ]
        ]
    ];
    //ボタン
    $response_format_button = [
        'type' => 'template',
        'altText' => 'ボタン', 
        'template' => [
             'type' => 'buttons',
             'title' => 'タイトルです',
             'text' => '選択してね', 
            'actions' => [
                 [
                     'type' => 'uri',
                     'label' => 'googleへ移動', 
                     'uri' => 'https://google.com' 
                 ]
              ]
          ]         
    ];
    
    //レスポンスフォーマット
    $response_format_text = [
        "type" => $message_type,
        "text" => $return_message_text
    ];
 
    //ポストデータ
    $post_data1 = [
        "replyToken" => $replyToken,
        "messages" => [$response_format_text, $send_format_gialog]
    ];
    $post_data2 = [
        "replyToken" => $replyToken,
        "messages" => [$response_format_text]
    ];
    $post_data3 = [
        "replyToken" => $replyToken,
        "messages" => [$response_format_button]
    ];

    //curl実行
    $ch = curl_init("https://api.line.me/v2/bot/message/reply");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    if($sendType==1)
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data1));
    elseif($sendType==2)
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data2));
    elseif($sendType==3)
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data3));
    
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json; charser=UTF-8',
        'Authorization: Bearer ' . $accessToken
    ));
    $result = curl_exec($ch);
    curl_close($ch);
}
?>