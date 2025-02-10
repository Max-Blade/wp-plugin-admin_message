<?php
/**
 * Plugin Name: Admin Messages
 * Plugin URI: https://localhost.com
 * Description: This plugins allows you to get more close to your users, you can send them a brif message that will popup once they are online.
 * Version: 1.0.0 
 * Author: Samuel Ramos
 * Author URI: https://github.com/max_blade/
 * Text Domain: admin_messages
 * Domain Path: /languages
 */

define('AM_PLUGIN_PATH_FILE', __FILE__);
define('AM_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('AM_PLUGIN_STYLES_URL', plugin_dir_url(__FILE__). 'assets/css/');
define('AM_PLUGIN_SCRIPTS_URL', plugin_dir_url(__FILE__) . 'assets/js/');
define('AM_PLUGIN_LANG_PATH', plugin_basename(dirname(__FILE__)) . '/languages');
define('AM_PLUGIN_VERSION', '1.0.0');

function am_create_db_table() {
    global $wpdb;

    $sql = "CREATE TABLE {$wpdb->prefix}am_stored_messages (
        `message_id` INT NOT NULL AUTO_INCREMENT,
        `sender_id` INT NOT NULL,
        `receiver_id` INT NOT NULL,
        `message_content` VARCHAR(255) NULL,
        `readed` TINYINT ZEROFILL NOT NULL DEFAULT 0,
        `date_created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `last_modified` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`message_id`)
        )
        DEFAULT CHARACTER SET = utf8;
    ";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta($sql);

    $db_error = !empty($wpdb->last_error);

    if ($db_error) {
        echo('ERROR CREATING TABLE');
    }
}

function am_activate() {
    am_create_db_table();
}

function am_deactivate() {
    flush_rewrite_rules();
}

register_activation_hook(AM_PLUGIN_PATH_FILE, 'am_activate');
register_deactivation_hook(AM_PLUGIN_PATH_FILE, 'am_deactivate');

function am_set_text_domain() {
    $language_loaded = load_plugin_textdomain('admin_messages', false, AM_PLUGIN_LANG_PATH);
}

add_action('init', 'am_set_text_domain');
require_once AM_PLUGIN_PATH . '/assets/admin/loader.php';