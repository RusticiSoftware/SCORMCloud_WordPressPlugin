<?php

require_once(SCORMCLOUD_BASE.'scormcloudui.php');

class ScormCloudPlugin
{
    public static $hooks = array();
    
    public static function activate()
    {
        
    }
    
    public static function deactivate()
    {
        
    }
    
    public static function uninstall()
    {
        if (__FILE__ != WP_UNINSTALL_PLUGIN)
        {
            return;
        }
        
        
    }
    
    public static function initialize()
    {
        ScormCloudUi::initialize();
        
        add_filter('the_content', array('ScormCloudContentHandler', 'make_blog_entry'));

        add_action('save_post', array('ScormCloudContentHandler', 'update_post_invite'));
        add_action('profile_update', array('ScormCloudContentHandler', 'update_learner_info'));
    }
    
    public static function is_network_managed() {
        if (!function_exists('get_site_option')) return false;
        if (get_site_option('scormcloud_networkManaged') != null){
            return (bool)get_site_option('scormcloud_networkManaged');
        } else return false;
    }
    
    public static function get_cloud_service() {
        require_once('SCORMCloud_PHPLibrary/ScormEngineService.php');
        require_once('SCORMCloud_PHPLibrary/ScormEngineUtilities.php');
    
        if (ScormCloudPlugin::is_network_managed()){
            $appid = get_site_option('scormcloud_appid');
            $secretkey = get_site_option('scormcloud_secretkey');
            $engine_url = get_site_option('scormcloud_engine_url');
    
        }else{
            $appid = get_option('scormcloud_appid');
            $secretkey = get_option('scormcloud_secretkey');
            $engine_url = get_option('scormcloud_engine_url');
        }
    
        $origin = ScormEngineUtilities::getCanonicalOriginString('Rustici Software', 'WordPress', '1.0.7.3');
    
        //arbitrary number 17 is the length of 'EngineWebServices'
        if (strlen($engine_url) < 17){
            $engine_url = "http://cloud.scorm.com/EngineWebServices";
        }
    
        return new ScormEngineService($engine_url,$appid,$secretkey,$origin);
    }
    
    public static function remaining_registrations() {
        $ScormService = ScormCloudPlugin::get_cloud_service();
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
}