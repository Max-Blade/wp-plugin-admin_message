<?php
class shortCodeHandler {

    public function get_message($message_id) {
        global $wpdb;

        $sql = "SELECT * FROM {$wpdb->prefix}am_stored_messages WHERE message_id = {$message_id}";

        $message = $wpdb->get_row($sql, ARRAY_A);

        return $message;
    }

    public function show_message($message_data = array()) {
        $html = ''; 
        if (!empty($message_data)) {
            am_load_scripts_and_css(true);
            $html = '<div style="z-index:9999; position:fixed; height: fit-content; width: 60%; margin: 2% 20%; top: 20px;">';
    
            foreach ($message_data as $key => $message) {
                $html .= '<div class="alert alert-info alert-dismissible message_card" style="box-shadow: 10px 5px 5px #6c6c6c82;" id="message-content-'.$key.'">';
                    $html .= '<button class="btn-close" data-message_id="'.$message['message_id'].'"></button>';
        
                    $html .= '<b>';
                        $html .= __('Message content','admin_messages') . ': ';
                    $html .= '</b>';
    
                    $html .= '<b>';
                        $html .= $message['message_content'];
                    $html .= '</b>';
                $html .= '</div>';
            }
            $html .= '</div>';
        }

        return $html;
    }

    public function check_unread_messages(){
        global $wpdb;

        $sql = "SELECT message_id FROM {$wpdb->prefix}am_stored_messages WHERE receiver_id = 0";

        $results = $wpdb->get_results($sql, ARRAY_A);
        
        return $results;
    }
}