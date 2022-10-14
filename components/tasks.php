<?php
require '../utilities/functions.php';
$App = new webxspark_admin;
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest')) {
    session_start();
    $data = [];
    $errors = '';
    if ($_REQUEST['action']) {
        $action = htmlspecialchars($_REQUEST['action']);
        if($action === "validate"){
            $reqData = ($_REQUEST['data']);
            $data['resp'] = $reqData;
            $ip = $App::fetch_ip();
            if(is_dir("../tasks/{$ip}.json")){
                
            }
        }
    }
    header('Content-type: application/json');
    echo json_encode($data, JSON_PRETTY_PRINT);
} else {
    $data_resObj = array(
        'code'   => 403,
        'message' => 'Requests from referer <empty> are blocked.',
        'domain'  => 'app.webxspark.com',
        'reason'  => 'forbidden',
        'status'  => 'PERMISSION_DENIED',
    );
    $data['error'] = $data_resObj;
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_PRETTY_PRINT);
}
