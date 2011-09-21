<?php

function is_network_environment()
{
    return function_exists('is_multisite') && is_multisite() && is_plugin_active_for_network('scormcloud/scormcloud.php');
}
