<?php
if (!isset($_POST['type']) || !$_POST['type']){
    die('{"FUNC":"lotto_svr.php","TYPE":3,"STATUS":"ERROR","TEXT":"SERVER lotto_svr.php (on server): wrong post."}');
}
require_once('init.inc');

function _POST($n){
    return isset($_POST[$n]) ? $_POST[$n] : NULL;
}

if ($_POST['type'] == 'yuyue'){
    $reserveDML = new DML('`tb_mingju_reserves`');
    $reserveDML->setValue('type', $_POST['type']);
    $reserveDML->setValue("group_table", 2); 
    $reserveDML->setValue('name', _POST("name"));
    $reserveDML->setValue('tel', _POST("tel")); 
    if (_POST("service")) {
        $reserveDML->setValue('contents', '{"service":"' . rawurlencode(_POST("service")) . '"}');
    }    
    $reserveDML->setValue('bultin', _POST("bultin"));
    $reserveDML->setValue('date', date('Y-m-d'));
    $reserveDML->setValue('phpSelf', _POST('phpSelf'));
    if ($pageID = DAS::isExistedInDB("tb_vcs_pages", "pageFile = '" . substr(_POST('phpSelf'), 1) . "'", "id")) {
       $reserveDML->setValue('pageID', $pageID);
    }
    $reserveDML->setValue('url', rawurlencode($_SERVER['HTTP_REFERER']));
    $reserveDML->setValue('ip', $_SERVER['REMOTE_ADDR']);
    $reserveDML->setValue('timestamp', date('Y-m-d H:i:s'));
    $reserveDML->setValue('status', "ok");
    
    $yuyueDML = new DML("tb_expo_yuyue");
    $yuyueDML->setValue('type', 'yuyue');
    $yuyueDML->setValue('name', _POST("name"));
    $yuyueDML->setValue('tel', _POST("tel"));
    $yuyueDML->setValue('service', _POST("service"));
    $yuyueDML->setValue('bultin', _POST('bultin'));
    $yuyueDML->setValue('phpSelf', _POST('phpSelf'));
    $yuyueDML->setValue('date', date('Y-m-d'));
    $yuyueDML->setValue('url', rawurlencode($_SERVER['HTTP_REFERER']));
    $yuyueDML->setValue('ip', $_SERVER['REMOTE_ADDR']);
    $yuyueDML->setValue('timestamp', date('Y-m-d H:i:s'));
    $yuyueDML->setValue('status', "ok");
    if (isset($_SESSION['vcs']['gid']) && $_SESSION['vcs']['gid']){
        $yuyueDML->setValue('gid', $_SESSION['vcs']['gid']);
        $reserveDML->setValue('gid', $_SESSION['vcs']['gid']);
    }
    if($yuyueDML->insert() && $reserveDML->insert()){
        die('{"FUNC":"lotto_svr.php","TYPE":1,"STATUS":"OK","TEXT":"提交成功。"}'); 
    }
    die('{"FUNC":"lotto_svr.php","TYPE":3,"STATUS":"ERROR","TEXT":"SERVER lotto_svr.php (on server): error by insert."}'); 
}
if ($_POST['type'] == 'lotto'){
    if(_POST("tel") != '17700000000' && (DAS::isExistedInDB("tb_mingju_lotto", "tel = '" . _POST("tel") . "' AND status = 'ok'") || DAS::isExistedInDB("tb_mingju_reserves", "tel = '" . _POST("tel") . "' AND status = 'ok'"))){
        die('{"FUNC":"lotto_svr.php","TYPE":2,"STATUS":"WARNING","TEXT":"SERVER lotto_svr.php (on server): Tel. number is existed."}');
    }
    $presents = array(0,0,0,10,200,20,0,15);
    $sents = new Query("COUNT(*) AS `sumSent`, `present`", "`tb_mingju_lotto`", "", "`status` = 'ok' AND `present` < 8", "", "`present`");
    //$sents = new Query("COUNT(*) AS `sumSent`, `specValue` AS `present`", "`tb_mingju_reserves`", "", "`status` = 'ok' AND `group_table` = 3 AND `specValue` < 8", "", "`specValue`");
    $sents = DAS::quickQuery($sents);
    if (DAS::hasData($sents)){
        foreach ($sents['data'] as $sent){
            $presents[$sent['present']] = $presents[$sent['present']] - $sent['sumSent'];            
        }
    }
    
    array_push($presents, (1000 - array_sum($presents)));
    $tempSum = 1;
    $seed = rand(1, 1000);
    for ($i = 1; $i < 9; $i++){
        if ($presents[$i] === 0){
            continue;
        }
        else if ($seed >= $tempSum && $seed < $tempSum + $presents[$i]){
            $reserveDML = new DML('`tb_mingju_reserves`');
            $reserveDML->setValue('tel', _POST("tel")); 
            $reserveDML->setValue('bultin', _POST("bultin"));
            $reserveDML->setValue('phpSelf', _POST('phpSelf'));
            $reserveDML->setValue("group_table", 3); 
            $reserveDML->setValue('specValue', $i);
            $reserveDML->setValue('contents', '{"present":' . $i . '}');
            $reserveDML->setValue('type', $_POST['type']);
            $reserveDML->setValue('date', date('Y-m-d'));
            if ($pageID = DAS::isExistedInDB("tb_vcs_pages", "pageFile = '" . substr(_POST('phpSelf'), 1) . "'", "id")) {
               $reserveDML->setValue('pageID', $pageID);
            }
            $reserveDML->setValue('url', rawurlencode($_SERVER['HTTP_REFERER']));
            $reserveDML->setValue('ip', $_SERVER['REMOTE_ADDR']);
            $reserveDML->setValue('timestamp', date('Y-m-d H:i:s'));
            $reserveDML->setValue('status', "ok");
            
            
            $lottoDML = new DML('tb_mingju_lotto');
            $lottoDML->setValue('tel', intval(_POST("tel")));
            $lottoDML->setValue('bultin', _POST('bultin'));
            $lottoDML->setValue('phpSelf', _POST('phpSelf'));
            $lottoDML->setValue('present', $i);
            $lottoDML->setValue('pageID', DAS::isExistedInDB("tb_vcs_pages", "pageFile = '" . substr(_POST('phpSelf'), 1) . "'", "id"));
            $lottoDML->setValue('date', date('Y-m-d'));
            $lottoDML->setValue('url', rawurlencode($_SERVER['HTTP_REFERER']));
            $lottoDML->setValue('ip', $_SERVER['REMOTE_ADDR']);
            $lottoDML->setValue('timestamp', date('Y-m-d H:i:s'));
            $lottoDML->setValue('status', 'ok');
            if (isset($_SESSION['vcs']['gid']) && $_SESSION['vcs']['gid']){
                $lottoDML->setValue('gid', $_SESSION['vcs']['gid']);
                $reserveDML->setValue('gid', $_SESSION['vcs']['gid']);
            }
            if($lottoDML->insert() && $reserveDML->insert()){
                die('{"FUNC":"lotto_svr.php","TYPE":1,"STATUS":"OK","TEXT":"SERVER lotto_svr.php (on server): successfully got the present. Seed:' . $seed . '; PresentStock:' . $presents[$i] . '","DATA":' . $i . '}');
            }
            die('{"FUNC":"lotto_svr.php","TYPE":3,"STATUS":"ERROR","TEXT":"SERVER lotto_svr.php (on server): error by insert."}');             
        }
        else{
            $tempSum += $presents[$i];
        }
    }
}
die('{"FUNC":"lotto_svr.php","TYPE":3,"STATUS":"ERROR","TEXT":"SERVER lotto_svr.php (on server): invalid type:' . $_POST['type'] . '"}');  
?>