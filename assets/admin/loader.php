<?php
require_once 'view/am_index.php';
require_once 'view/am_create_message.php';
require_once 'view/am_edit_message.php';
require_once AM_PLUGIN_PATH . 'assets/class/shortcode_handler.php';

function am_create_menu() {
    // Index
    $current_user = wp_get_current_user();

    global $wpdb;

    if ($_GET['page'] == 'am-edit_message') {
        if (!isset($_GET['message_id'])) {
            wp_redirect(admin_url('admin.php?page=admin_messages&error='.__( 'Missing ID.', 'admin_messages' )));
            die();
        }
    }
    
    if (isset($_POST['edit_message'])) {
        $message_id = (int)$_POST['message_id'];
        $sender_id = (int)$current_user->ID;
        $receiver_id = (int)$_POST['send-to'];
        $message_content = esc_sql((string)$_POST['message-content']);
        $date_created = esc_sql((string)$_POST['date_created']);
        $last_modified = null;

        $sql = "UPDATE {$wpdb->prefix}am_stored_messages SET sender_id = '{$sender_id}', 
        receiver_id = '{$receiver_id}', 
        message_content = '{$message_content}', readed = 0 WHERE message_id = '{$message_id}'";
        
        $success = $wpdb->query($sql);
        
        if ($success) {
            wp_redirect(admin_url('admin.php?page=admin_messages'));
            die();
        } else {
            set_param_error();
        }
    }

    if (isset($_POST['create_message'])) {
        $data = [
            'message_id' => null,
            'sender_id' => (int)$current_user->ID,
            'receiver_id' => (int)$_POST['send-to'],
            'message_content' => esc_sql((string)$_POST['message-content']),
            'readed' => 0,
            'date_created' => null,
            'last_modified' => null
        ];

        $success = $wpdb->insert($wpdb->prefix . 'am_stored_messages', $data);
        
        if ($success) {
            wp_redirect(admin_url('admin.php?page=admin_messages'));
            die();
        } else {
            set_param_error();
        }
    }

    $main_title = __('Admin messages', 'admin_messages');

    // Check and display errors
    if (isset($_GET['error'])) {
        $style = 'z-index: 9999; width: 60%; margin: 2% 20%; position: fixed; background-color: #f3b9be9c;';

        echo '<div class="alert alert-danger alert-dismissible" style="'.$style.'"><i class="dashicons dashicons-warning"></i> '.$_GET['error'].'<button class="btn-close"></button></div>';
    }

    add_menu_page(
        $main_title, //Page Title
        'Admin messages', //Menu Title
        'read', //Capability
        'admin_messages', //Slug aka SEO URL
        'am_get_main_page_content', //function that generates the content of the page
        'dashicons-edit-page', // Menu Icon (Se puede usar una ruta a una imagen).
        1 // Menu Position
    );

    // Create new message
    add_submenu_page(
        null, //Parent Slug
        __('Create New Message', 'admin_messages'), //Page Title
        null, //Submenu Title
        'read', // Capability
        'am-create_new_message', //Self slug
        'am_get_new_message_page_content' // function that generates the content of the page
    );

    //Edit message
    add_submenu_page(
        null, //Parent Slug
        __('Edit Message', 'admin_messages'), //Page Title
        null, //Submenu Title
        'read', // Capability
        'am-edit_message', //Self slug
        'am_get_edit_message_page_content' // function that generates the content of the page
    );
}

add_action('admin_menu', 'am_create_menu');

function am_check_is_this_plugin($hook) {
    return AM_PLUGIN_HOOK == $hook ? true : false;
}

function am_check_style_sheet_status ($value = '') {
    $style_status = [];

    $wp_styles = wp_styles();

    $style = $wp_styles->query( $value, 'registered' );
    $style_status['registered'] = $style ? true : false;

    $style = $wp_styles->query( $value, 'enqueued');
    $style_status['enqueued'] = $style ? true : false;

    return $style_status;
}

function am_load_scripts_and_css($enqueue_scripts = false) {
    if ($enqueue_scripts) {
        wp_enqueue_style( 'font-awesome', AM_PLUGIN_STYLES_URL . 'font-awesome/css/fontawesome.min.css');

        wp_enqueue_style('bootstrap_css', AM_PLUGIN_STYLES_URL . 'bootstrap/css/bootstrap.min.css');
        wp_enqueue_script('bootstrap_js', AM_PLUGIN_STYLES_URL . 'bootstrap/js/bootstrap.min.js', array('jquery'));

        wp_enqueue_style('am-main_style', AM_PLUGIN_STYLES_URL . 'am-main_style.css');
        wp_enqueue_script('am-main_script', AM_PLUGIN_SCRIPTS_URL . 'am-main_script.js');

        wp_localize_script('am-main_script', 'ajaxData', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'deleteNonce' => wp_create_nonce('am-delete-message'),
            'readNonce' => wp_create_nonce('am-read-message'),
            'is_admin' => is_admin()
        ));
    }    
}

function delete_message() {
    if (!wp_verify_nonce($_POST['nonce'], 'am-delete-message')) {
        die(__('You Dont have permission to this ajax', 'admin_messages'));
    }

    global $wpdb;

    try {
        $id = $_POST['id'];

        $result = $wpdb->delete($wpdb->prefix . 'am_stored_messages',array('message_id' => $id));

        if ($result === false) {
            throw new Exception(__('Failed to delete message', 'admin_messages'));
        }

        return json_encode(array('success' => true));
    } catch (\Throwable $th) {
        $error = $th->getMessage();
        return json_encode(array('error' => $error));
    }
}

add_action('wp_ajax_am-delete-message', 'delete_message');

function read_message() {
    if (!wp_verify_nonce($_POST['nonce'], 'am-read-message')) {
        die(__('You Dont have permission to this ajax', 'admin_messages'));
    }

    global $wpdb;

    try {
        $id = $_POST['id'];

        $sql = "UPDATE {$wpdb->prefix}am_stored_messages SET  `readed` = '1' WHERE (message_id = {$id})";

        $success = (bool)$wpdb->query($sql);

        if ($success) {
            return json_encode(array('success' => 'true'));
        } else {
            return json_encode(array('success' => 'false'));
        }
        
    } catch (\Throwable $th) {
        $error = $th->getMessage();
        return json_encode(array('error' => $error));
    }
}

add_action('wp_ajax_am-read-message','read_message');

function set_param_error() {
    $_GET['error'] = __( 'There was something wrong.', 'admin_messages' );
}

function am_show_message(){
    $current_user = wp_get_current_user()->ID;

    global $wpdb;

    $query = "
        SELECT
            m.message_id,
            m.message_content,
            m.date_created,
            m.last_modified,
            m.readed,
            sender.user_nicename AS sender_name,
            receiver.user_nicename AS receiver_name  
        FROM 
            {$wpdb->prefix}am_stored_messages m
        JOIN
            wp_users sender ON m.sender_id = sender.ID
        JOIN
            wp_users receiver ON m.receiver_id = receiver.ID
        WHERE
            m.receiver_id = {$current_user} AND m.readed = 0;
    ";

    $message_data = $wpdb->get_results($query, ARRAY_A);

    if (!empty($message_data)) {
        am_load_scripts_and_css(true);
        $html = '<div style="z-index:9999; position:fixed; height: fit-content; width: 60%; margin: 2% 20%; top: 20px;">';

        foreach ($message_data as $key => $message) {
            $html .= '<div class="alert alert-info alert-dismissible message_card" style="box-shadow: 10px 5px 5px #6c6c6c82;" id="message-content-'.$key.'">';
                $html .= '<button class="btn-close" data-message_id="'.$message['message_id'].'"></button>';
                $html .= '<b>';
                    $html .= __('You have a message from','admin_messages') . ': ';
                    $html .= $message['sender_name'];
                $html .= '</b>';

                $html .= '<hr/>';
                $html .= '<b>';
                    $html .= __('Message content','admin_messages') . ': ';
                $html .= '</b>';

                $html .= '<b>';
                    $html .= $message['message_content'];
                $html .= '</b>';
            $html .= '</div>';
        }
        $html .= '</div>';

        $html .= '<script>
        
        </script>';

        echo $html;
    }
}

add_action('admin_footer','am_show_message');

function make_shortcode($atts) { // Atts var contains the params send into the shortcode.
    $short_code_handler = new shortCodeHandler();

    $messages = $short_code_handler->check_unread_messages();

    $message_data = array();

    foreach ($messages as $message_id) {
        $message_id = $message_id['message_id'];

        $message_data[] = $short_code_handler->get_message($message_id);    
    }

    echo $short_code_handler->show_message($message_data);
}

add_action('wp_head','make_shortcode');