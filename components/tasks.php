<?php

require '../utilities/functions.php';
$App = new webxspark_admin;
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest')) {
    session_start();
    //creating a new tasks folder to store data if the folder doesn't exists
    if (!is_dir('../tasks')) {
        $App->createDir(['tasks'], '../');
    }
    $data = [];
    $errors = '';
    function validateCloudfile()
    {
        $App = new webxspark_admin;
        $ip = $App::fetch_ip();

        if (file_exists("../tasks/{$ip}.json")) {} else {
            $data['data'] = [];
            $content = json_encode($data, JSON_PRETTY_PRINT);
            $fp = fopen("../tasks/{$ip}.json", "wb");
            fwrite($fp, $content);
            fclose($fp);
        }
        return "{$ip}.json";
    }
    if ($_REQUEST['action']) {
        $action = htmlspecialchars($_REQUEST['action']);
        $jsonFile = validateCloudfile();
        $jsonArr = $App->getJsonArray('../tasks/' . $jsonFile);
        if ($action === "insert") {
            $content = ($_REQUEST['content']);
            $content['task'] = $App->encrypt_str(htmlspecialchars($content['task']));
            $jsonArr['data'][$App->generate_random_strings(5)] = $content;
            if ($App->update_file(json_encode($jsonArr, JSON_PRETTY_PRINT), '../tasks/' . $jsonFile)) {
                $data = ["status" => 200];
            } else {
                $data = ["error" => "Unable to update the list. Please try again later!"];
            }
        }
        if ($action === "delete") {
            $task = $App->encrypt_str(htmlspecialchars($_REQUEST['task']));
            //linear-searching for particular task in database
            foreach ($jsonArr as $key => $val) {
                foreach ($val as $k => $v) {
                    if ($task === $v['task']) {
                        unset($jsonArr[$key][$k]);
                    }
                }
            }
            if ($App->update_file(json_encode($jsonArr, JSON_PRETTY_PRINT), '../tasks/' . $jsonFile)) {
                $data = ["status" => 200];
            } else {
                $data = ["error" => "Unable to update the list. Please try again later!"];
            }
        }
        if($action === "fetchAll"){
            foreach($jsonArr as $key => $val){
                foreach($val as $k => $v){
                    $jsonArr[$key][$k]['task'] = $App->decrypt_str($v['task']);
                }
            }
            $data = $jsonArr;
        }
        if ($action === "update") {
            $task = $App->encrypt_str(($_REQUEST['task']));
            //linear-searching for particular task in database
            foreach ($jsonArr as $key => $val) {
                foreach ($val as $k => $v) {
                    if ($task === $v['task']) {
                        $v['isCompleted'] = htmlspecialchars($_REQUEST['isCompleted']);
                        $jsonArr[$key][$k]['isCompleted'] = htmlspecialchars($_REQUEST['isCompleted']);
                    }
                }
            }
            if ($App->update_file(json_encode($jsonArr, JSON_PRETTY_PRINT), '../tasks/' . $jsonFile)) {
                $data = ["status" => 200];
            } else {
                $data = ["error" => "Unable to update the list. Please try again later!"];
            }
        }
        if($action === "wipe"){
            unlink('../tasks/'.$jsonFile);
            $data = ['status' => 200];
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
