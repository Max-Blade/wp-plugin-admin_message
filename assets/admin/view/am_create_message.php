<?php  
function am_get_new_message_page_content() {
    global $wpdb;

    $current_user = wp_get_current_user();

    am_load_scripts_and_css(true);

    $sql = "SELECT ID, user_nicename FROM {$wpdb->prefix}users WHERE ";

    $sql .= "id != {$current_user->ID}";

    $users = $wpdb->get_results($sql, ARRAY_A);


    ob_start();
    ?>
        <div class="wrap">
            <h1 class="heading-title"><?php echo get_admin_page_title(); ?></h1>
            <form action="" method="post">
                <div class="form-control col-sm-10 form-container">
                    <label for="send-to"><?php esc_html_e( 'Send To:', 'admin_messages' ); ?></label>
                    <select name="send-to" id="send-to" required>
                        <option value=""></option>
                        <?php
                            foreach ($users as $user) {
                                echo '<option value="'.$user['ID'].'">'.$user['user_nicename'].'</option>';
                            }
                        ?>
                    </select>
                </div>
                <div class="form-control col-sm-10 form-container">
                    <label for="message-content" id="label-message"><?php esc_html_e( 'Message:', 'admin_messages' ); ?></label>
                    <textarea name="message-content" id="message-content" class="large-text" rows="5" maxlength="255" required></textarea>
                </div>
                <div class="pull-right">
                    <button type="submit" name="create_message" class="button button-primary create-message-btn"><?php esc_html_e( 'Create', 'admin_messages' ); ?></button>
                </div>
            </form>
        </div>
    <?php

    $output = ob_get_clean();

    echo($output);
}

?>