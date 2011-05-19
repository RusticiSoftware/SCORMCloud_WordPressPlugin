<?PHP

function scormcloud_getScormEngineService(){
	require_once('SCORMAPI/ScormEngineService.php');
	
    if (scormcloud_isScormCloudNetworkManaged()){
        $appid = get_site_option('scormcloud_appid');  
        $secretkey = get_site_option('scormcloud_secretkey'); 
        $engine_url = get_site_option('scormcloud_engine_url');
            
    }else{
        $appid = get_option('scormcloud_appid');  
        $secretkey = get_option('scormcloud_secretkey'); 
        $engine_url = get_option('scormcloud_engine_url');
    }
    
    //arbitrary number 17 is the length of 'EngineWebServices'
    if (strlen($engine_url) < 17){
        $engine_url = "http://cloud.scorm.com/EngineWebServices";
    }
    
	return new ScormEngineService($engine_url,$appid,$secretkey);
}


function scormcloud_getCourseLaunchTime($regid){
	
	$ScormService = scormcloud_getScormEngineService();
	$regService = $ScormService->getRegistrationService();
	
	$resultArray = $regService->GetLaunchHistory($regid);
	
	if (count($resultArray) > 0){
		return cloud_convertTimeToInt($resultArray[0]->getLaunchTime());
	}
}


//input format 2009-08-11T19:01:50.081+0000 
function scormcloud_convertTimeToInt($str){
	//echo 'hour: '.substr($str,11,2).'<br/>';
	//echo 'minute: '.substr($str,14,2).'<br/>';
	return mktime(substr($str,11,2),substr($str,14,2),substr($str,17,2),substr($str,5,2),substr($str,8,2), substr($str,0,4));
}

function scormcloud_isScormCloudNetworkManaged(){
    if (!function_exists('get_site_option')) return false;
    if (get_site_option('scormcloud_networkManaged') != null){
        return (bool)get_site_option('scormcloud_networkManaged');
    } else return false;
}

function scormcloud_getDBPrefix(){
    global $wpdb;
    if (scormcloud_isScormCloudNetworkManaged()){
        return get_site_option('scormcloud_dbprefix');
    } else {
        return $wpdb->prefix;
    }
}

function scormcloud_regsRemaining(){
    $ScormService = scormcloud_getScormEngineService();
	$acctService = $ScormService->getAccountService();
    $response = $acctService->GetAccountInfo();
    $respXml = simplexml_load_string($response);
    
    if ($respXml->account->accounttype != 'trial' && $respXml->account->strictlimit == 'false'){
        return 1;
    } else {
        $regLimit = (int)$respXml->account->reglimit;
        $regUsage = (int)$respXml->account->usage->regcount;
        //error_log('limit: '.$regLimit.'   usage: '.$regUsage);
        return $regLimit - $regUsage;
    }
}


?>