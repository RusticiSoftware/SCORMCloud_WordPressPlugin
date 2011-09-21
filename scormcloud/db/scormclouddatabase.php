<?php

require_once(SCORMCLOUD_BASE.'utils.php');

class ScormCloudDatabase
{   
    public static function install()
    {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        global $wpdb;
        global $scormcloud_db_version;

        $scormcloud_db_version = "1.1";

        $table_name = $wpdb->prefix . "scormcloudinvitations";
        $sql = "CREATE TABLE " . $table_name . " (
		  		invite_id VARCHAR(50) NOT NULL,
                blog_id VARCHAR(50) NOT NULL,
                post_id VARCHAR(50) NOT NULL,
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

        if (is_network_environment()){
            $installed_ver = get_site_option( "scormcloud_db_version" );
        } else {
            $installed_ver = get_option( "scormcloud_db_version" );
        }

        if ($installed_ver != $scormcloud_db_version ) {
             
            if (is_network_environment()){
                update_site_option( "scormcloud_db_version", $scormcloud_db_version );
                update_site_option( "scormcloud_dbprefix", $wpdb->prefix);
                update_site_option( "scormcloud_networkManaged", 'true');
            } else {
                update_option( "scormcloud_db_version", $scormcloud_db_version );
            }


        }
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
    
    private static function ensure_arry($default, $val) {
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
        return call_user_func_array('ScormCloudDatabase::get_row', $args);
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
            return get_site_option('scormcloud_dbprefix');
        } else {
            return $wpdb->prefix;
        }
    }
}