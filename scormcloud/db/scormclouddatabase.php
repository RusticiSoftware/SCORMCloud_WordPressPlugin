<?php

class ScormCloudDatabase
{   
    private static $versions = array('1.1', '13');
    
    public static function install()
    {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        global $wpdb;
        global $scormcloud_db_version;

        $scormcloud_db_version = "13";

        $table_name = $wpdb->prefix . "scormcloudinvitations";
        $sql = "CREATE TABLE " . $table_name . " (
		  		invite_id VARCHAR(50) NOT NULL,
                blog_id VARCHAR(50) NOT NULL,
                post_id VARCHAR(50) NULL,
                app_id VARCHAR(50) NOT NULL,
                course_id VARCHAR(100) NOT NULL,
                course_title text NOT NULL,
		  		header tinytext NOT NULL,
		  		description text NOT NULL,
                show_course_info tinyint(2) DEFAULT '1' NOT NULL,
		  		active tinyint(2) DEFAULT '1' NOT NULL,
		  		require_login tinyint(2) DEFAULT '0' NOT NULL,
                create_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
				);";
        dbDelta($sql);

        $table_name = $wpdb->prefix . "scormcloudinvitationregs";
        $sql = "CREATE TABLE " . $table_name . " (
		  		invite_id VARCHAR(50) NOT NULL,
                reg_id VARCHAR(50) NOT NULL,
                user_id bigint(20) unsigned NULL,
                user_email VARCHAR(50) NULL,
                update_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
		  		);";
        dbDelta($sql);

        if (self::is_network_environment()){
            $installed_ver = get_site_option('scormcloud_db_version');
        } else {
            $installed_ver = get_option('scormcloud_db_version');
        }

        if ($installed_ver == null) {
            if (self::is_network_environment()) {
                update_site_option('scormcloud_db_version', $scormcloud_db_version);
                update_site_option('scormcloud_db_prefix', $wpdb->prefix);
                update_site_option('scormcloud_networkManaged', 'true');
            }
        } else if ($installed_ver != $scormcloud_db_version ) {
             self::upgrade($installed_ver, $scormcloud_db_version);
        }
    }
    
    public static function upgrade($from, $to)
    {
        if (!in_array($from, self::$versions) || !in_array($to, self::$versions)) {
            // TODO: Log error
            return;
        }
        
        $versions = self::$versions;
        
        while (current($versions) != $from) {
            next($versions);
        }
        
        $current = str_replace('.', '', $from);
        while ($current != $to) {
            $next = str_replace('.', '', next($versions));
            if ($next === false) {
                // no good
                break;
            }
            
            call_user_func(array(__CLASS__, 'upgrade_from_'.$current.'_to_'.$next));
	    $current = $next;
        }
    }
    
    public static function update_check()
    {
        self::install();
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
    public static function get_row() {
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
    
            $separator = 'AND ';
            if ($first) {
                $separator = '';
                $first = false;
            }
    
            $whereClause .= $separator.$col.' = '.$format;
            $whereValues[] = $val;
        }
    
        $query = $wpdb->prepare('SELECT * FROM '.$table.$whereClause, $whereValues);
        return $wpdb->get_row($query, OBJECT);
    }
    
    private static function ensure_array($default, $val) {
        if (!is_array($val)) {
            return array($default => $val);
        }
    
        return $val;
    }
    
    /**
     * Convenience wrapper for ScormCloudDatabase::get_row to select a single invitation
     *
     * If the first arg is an array, it is assumed to be an associative array of column/value pairs to pass on to ScormCloudDatabase::get_row. If it
     * 	is not an array, the arg is used as the value for the invite_id column, allowing for a simple ScormCloudDatabase::get_invitation($inviteId)
     * If a second arg is provided, it should be an array of format specifiers for the first arg
     */
    public static function get_invitation() {
        $callArgs = array(self::get_invitations_table());
        $args = func_get_args();
        $args = self::ensure_array('invite_id', $args[0]);
        array_push($callArgs, $args);
        return call_user_func_array('ScormCloudDatabase::get_row', $callArgs);
    }
    
    /**
     * Convenience wrapper for ScormCloudDatabase::get_row to select a single invitation registration
     *
     * If the first arg is an array, it is assumed to be an associative array of column/value pairs to pass on to ScormCloudDatabase::get_row. If it
     * 	is not an array, the arg is used as the value for the reg_id column, allowing for a simple ScormCloudDatabase::get_invitation_reg($regId)
     * If a second arg is provided, it should be an array of format specifiers for the first arg
     */
    public static function get_invitation_reg() {
        $callArgs = array(self::get_registrations_table());
        $args = func_get_args();
        $args = self::ensure_array('reg_id', $args[0]);
        array_push($callArgs, $args);
        return call_user_func_array('ScormCloudDatabase::get_row', $callArgs);
    }
    
    public static function get_invitations_table()
    {
        return self::get_full_table_name('scormcloudinvitations');
    }
    
    public static function get_registrations_table()
    {
        return self::get_full_table_name('scormcloudinvitationregs');
    }
    
    public static function get_full_table_name($table) {
        return self::get_db_prefix().$table;
    }
    
    private static function get_db_prefix(){
        global $wpdb;
        if (ScormCloudPlugin::is_network_managed()){
            return get_site_option('scormcloud_db_prefix');
        } else {
            return $wpdb->prefix;
        }
    }
    
    private static function is_network_environment()
    {
        return function_exists('is_multisite') && is_multisite() && is_plugin_active_for_network('scormcloud/scormcloud.php');
    }
    
    private static function upgrade_from_11_to_12()
    {
        if (self::is_network_environment()) {
            $prefix = get_site_option('scormcloud_dbprefix');
            if ($prefix == null) {
                $prefix = $wpdb->prefix;
            }
            
            delete_site_option('scormcloud_dbprefix');
            update_site_option('scormcloud_db_prefix', $prefix);
            
            update_site_option('scormcloud_db_version', '12');
        } else {
            update_option('scormcloud_db_version', '12');
        }
    }
}

