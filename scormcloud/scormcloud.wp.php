<?PHP

function scormcloud_getScormEngineService(){
	require_once('SCORMCloud_PHPLibrary/ScormEngineService.php');
	require_once('SCORMCloud_PHPLibrary/ScormEngineUtilities.php');
	
    if (scormcloud_isScormCloudNetworkManaged()){
        $appid = get_site_option('scormcloud_appid');  
        $secretkey = get_site_option('scormcloud_secretkey'); 
        $engine_url = get_site_option('scormcloud_engine_url');
            
    }else{
        $appid = get_option('scormcloud_appid');  
        $secretkey = get_option('scormcloud_secretkey'); 
        $engine_url = get_option('scormcloud_engine_url');
    }
    
    $origin = ScormEngineUtilities::getCanonicalOriginString('Rustici Software', 'WordPress', '1.0.6.6');
    
    //arbitrary number 17 is the length of 'EngineWebServices'
    if (strlen($engine_url) < 17){
        $engine_url = "http://cloud.scorm.com/EngineWebServices";
    }
    
	return new ScormEngineService($engine_url,$appid,$secretkey,$origin);
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

function scormcloud_getTableName($table) {
	return scormcloud_getDBPrefix().$table;
}

/**
 * Works similarly to $wpdb->insert/update to select a single row.
 * 
 * First arg is always table name, e.g. 'scormcloudinvitations'
 * Second arg is an associative array of column/value pairs for the WHERE clause in the query,
 * 	e.g. array('invite_id' => $inviteId)
 * If a third arg is provided, it must be an array of the same dimensions as the second arg and
 * 	provide a list of format specifiers, e.g. array('%s', '%s', '%d') 
 * 
 * Returns the resulting row in OBJECT format, or null if no row was found or the call was invalid
 * 
 */
function scormcloud_getRow() {
    global $wpdb;
    $numargs = func_num_args();
    if ($numargs < 2 || $numargs > 3) {
        return null;
    }
    
    $table = func_get_arg(0);
    
    $wheres = null;
    $formats = null;
    if ($numargs == 2) {
        $arg = func_get_arg(1);
        if (!is_array($arg)) {
            // Must be array
            return null;
        } else {
            $wheres = $arg;
        }
    } else if ($numargs == 3) {
        $arg = func_get_arg(1);
        $arg2 = func_get_arg(2);
        if (!(is_array($arg) && is_array($arg2))) {
            // In the 2-arg call format, the args must be arrays
            return null;
        }
        
        if (empty($arg) || (count($arg) != count($arg2))) {
            // Arrays must not be empty and must contain same number of args
            return null;
        }
        
        $wheres = $arg;
        $formats = $arg2;
    }
    
    $i = 0;
    $first = true;
    $whereClause = ' WHERE ';
    $whereValues = array();
    foreach ($wheres as $col => $val) {
        $format = '%s';
        if ($formats !== null) {
            $format = $formats[$i];
            $i++;
        }
        
        $comma = ', ';
        if ($first) {
            $comma = '';
            $first = false;
        }
        
        $whereClause .= $comma.$col.' = '.$format;
        $whereValues[] = $val;
    }
    
    $query = $wpdb->prepare('SELECT * FROM '.scormcloud_getTableName($table).$whereClause, $whereValues);
    return $wpdb->get_row($query, OBJECT);
}

function scormcloud_ensureArray($default, $val) {
    if (!is_array($val)) {
        return array($default => $val);
    }
    
    return $val;
}

/**
 * Convenience wrapper for scormcloud_getRow to select a single invitation
 * 
 * If the first arg is an array, it is assumed to be an associative array of column/value pairs to pass on to scormcloud_getRow. If it
 * 	is not an array, the arg is used as the value for the invite_id column, allowing for a simple scormcloud_getInvitation($inviteId)
 * If a second arg is provided, it should be an array of format specifiers for the first arg
 */
function scormcloud_getInvitation() {
    $callArgs = array('scormcloudinvitations'); // Table name
    $args = func_get_args();
    $args[0] = scormcloud_ensureArray('invite_id', $args[0]);
    array_push($callArgs, $args);
    return call_user_func_array('scormcloud_getRow', $callArgs);
}

/**
 * Convenience wrapper for scormcloud_getRow to select a single invitation registration
 * 
 * If the first arg is an array, it is assumed to be an associative array of column/value pairs to pass on to scormcloud_getRow. If it
 * 	is not an array, the arg is used as the value for the reg_id column, allowing for a simple scormcloud_getInvitationReg($regId)
 * If a second arg is provided, it should be an array of format specifiers for the first arg
 */
function scormcloud_getInvitationReg() {
    $callArgs = array('scormcloudinvitationregs'); // Table name
    $args = func_get_args();
    $args[0] = scormcloud_ensureArray('reg_id', $args[0]);
    array_push($callArgs, $args);
    return call_user_func_array('scormcloud_getRow', $args);
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