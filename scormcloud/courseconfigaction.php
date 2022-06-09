<?php
require_once('vendor/autoload.php');

    global $wpdb;
    if ( defined('ABSPATH') )
    require_once(ABSPATH . 'wp-load.php');
    else
    require_once('../../../wp-load.php');
    require_once(ABSPATH . 'wp-admin/admin.php');
    require_once(SCORMCLOUD_BASE.'scormcloudplugin.php');
    $ScormService = ScormCloudPlugin::get_cloud_service();
    try {
        $isValidAccount = $ScormService->isValidAccount();
    } catch (Exception $e) {
        $isValidAccount = false;
    }

    $courseId = $_GET ['courseId'];
    $json = file_get_contents('php://input');
    $configOptionsToSet = json_decode($json)->settings;

    if ($isValidAccount){
        $settingsIndSchemaList = [];
        $settingsPostSchema = new \RusticiSoftware\Cloud\V2\Model\SettingsPostSchema();
        foreach($configOptionsToSet as $config) {
            $settingsIndividualSchema = new \RusticiSoftware\Cloud\V2\Model\SettingsIndividualSchema();
            $settingsIndividualSchema->setSettingId($config->settingId);
            $settingsIndividualSchema->setValue($config->value);
            array_push($settingsIndSchemaList, $settingsIndividualSchema);
        }
        $settingsPostSchema->setSettings($settingsIndSchemaList);

        $courseService = $ScormService->getCourseService();
        $courseService->setCourseConfiguration($courseId, $settingsPostSchema);

        echo 'Save Was Successful.';
    }


?>
