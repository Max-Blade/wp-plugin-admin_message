<?php
function am_get_edit_message_page_content() {
    global $wpdb;

    $current_user = wp_get_current_user();

    am_load_scripts_and_css(true);

    $sql = "SELECT ID, user_nicename FROM {$wpdb->prefix}users WHERE ";

    $sql .= "id != {$current_user->ID}";

    $users = $wpdb->get_results($sql, ARRAY_A);

    $message_id = (int)$_GET['message_id'];

    $sql = "SELECT * FROM {$wpdb->prefix}am_stored_messages WHERE message_id = {$message_id}";

    $messege_data = $wpdb->get_results($sql, ARRAY_A);
    $messege_data = $messege_data[0];
    ob_start();
    ?>
        <div class="wrap">
            <h1 class="heading-title"><?php echo get_admin_page_title(); ?></h1>
            <form action="" method="post">
                <input type="hidden" name="message_id" value="<?php esc_attr_e($message_id); ?>"/>
                <input type="hidden" name="sender_id" value="<?php esc_attr_e($messege_data['sender_id']); ?>"/>
                <input type="hidden" name="receiver_id" value="<?php esc_attr_e($messege_data['receiver_id']); ?>"/>
                <input type="hidden" name="date_created" value="<?php esc_attr_e($messege_data['date_created']); ?>"/>
                <div class="form-control col-sm-10 form-container">
                    <label for="send-to"><?php esc_html_e( 'Send To:', 'admin_messages' ); ?></label>
                    <select name="send-to" id="send-to" required>
                    <option value="0"><?php esc_html_e('Send to all frontend users', 'admin_messages'); ?></option>
                        <?php
                            foreach ($users as $user) {
                                $option = '<option value="'.$user['ID'].'"';
                                
                                if ( $user['ID'] == $messege_data['receiver_id']) {
                                    $option .= ' selected';
                                }

                                $option .='>'.$user['user_nicename'].'</option>'; 

                                echo ($option);
                            }
                        ?>
                    </select>
                </div>
                <div class="form-control col-sm-10 form-container">
                    <label for="message-content" id="label-message"><?php esc_html_e( 'Message:', 'admin_messages' ); ?></label>
                    <textarea name="message-content" id="message-content" class="large-text" rows="5" maxlength="255" required><?php echo ($messege_data['message_content']); ?></textarea>
                </div>
                <div class="pull-right">
                    <button type="submit" name="edit_message" class="button button-primary edit-message-btn"><?php esc_html_e( 'Edit', 'admin_messages' ); ?></button>
                </div>
            </form>
        </div>
    <?php

    $output = ob_get_clean();

    echo($output);
}