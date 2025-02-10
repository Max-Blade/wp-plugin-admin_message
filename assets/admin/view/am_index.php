<?php
function am_get_main_page_content() {
    am_load_scripts_and_css(true);
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
            wp_users receiver ON m.receiver_id = receiver.ID;
    ";

    $results = $wpdb->get_results($query, ARRAY_A);

    ob_start();
    
    ?>
        <div class="warp">
            <h1 class="heading-title"><?php echo get_admin_page_title(); ?></h1>
            <div class="pull-right" style="padding-right: 2%;padding-bottom: 1%;">
                <a href="admin.php?page=am-create_new_message" class="button button-primary" title="<?php esc_attr_e('New Message','admin_messages'); ?>" style="padding-top: 7px;"><i class="dashicons dashicons-plus"></i></a>
            </div>

            <div class="table-container">
                <table class="table table-striped table-hover wp-list-table widefat fixed">
                    <thead>
                        <tr>
                            <th scope="col" class="manage-column"><?php esc_html_e( 'Writer', 'admin_messages' ); ?></th>
                            <th scope="col" class="manage-column"><?php esc_html_e( 'Send To', 'admin_messages' ); ?></th>
                            <th scope="col" class="manage-column"><?php esc_html_e( 'Message Content', 'admin_messages' ); ?></th>
                            <th scope="col" class="manage-column"><?php esc_html_e( 'Date Created', 'admin_messages' ); ?></th>
                            <th scope="col" class="manage-column"><?php esc_html_e( 'Last Update', 'admin_messages' ); ?></th>
                            <th scope="col" class="manage-column"><?php esc_html_e( 'Actions', 'admin_messages' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            if(!empty($results)){ 
                                foreach ($results as $result) {
                                ?>
                                    <tr id="message-id-<?php esc_attr_e($result['message_id']); ?>">
                                        <td><?php esc_html_e($result['sender_name']); ?></td>

                                        <td>
                                            <i class ="<?php
                                                if (!(int)$result['readed']) {
                                                    echo 'dashicons dashicons-email-alt';
                                                } else {
                                                    echo 'dashicons dashicons-yes-alt';
                                                } ?>">
                                            </i>
                                            <?php esc_html_e($result['receiver_name']); ?>
                                        </td>

                                        <td><?php esc_html_e($result['message_content']); ?></td>
                                        <td><?php esc_html_e($result['date_created']); ?></td>
                                        <td><?php esc_html_e($result['last_modified']); ?></td>
                                        <td>
                                            <a class="btn btn-primary view" data-message-id="<?php esc_attr_e($result['message_id']); ?>" title="<?php esc_attr_e('View Message','admin_messages'); ?>"><i class="dashicons dashicons-visibility"></i> <?php esc_html_e('View','admin_messages'); ?></a>
                                            <a class="btn btn-info edit" href="admin.php?page=am-edit_message&message_id=<?php esc_attr_e($result['message_id']); ?>" title="<?php esc_attr_e('Edit Message','admin_messages'); ?>"><i class="dashicons dashicons-edit"></i> <?php esc_html_e('Edit', 'admin_messages'); ?></a>
                                            <a class="btn btn-danger erase" data-message-id="<?php esc_attr_e($result['message_id']); ?>" title="<?php esc_attr_e('Erase Message','admin_messages'); ?>"><i class="dashicons dashicons-trash"></i> <?php esc_html_e('Erase', 'admin_messages'); ?></a>
                                        
                                            <div class="modal" id="message-content-<?php esc_attr_e($result['message_id']); ?>">
                                                <div class="modal-content">
                                                    <span class="close pull-right">&times;</span>
                                                    <h3><?php esc_html_e('Message Content','admin_messages'); ?></h3>
                                                    <br/>
                                                    <p><?php esc_html_e($result['message_content']); ?></p>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php
                                }
                            } else {
                                ?>
                                    <tr>
                                        <td class="text-center" colspan="6" style="display: revert;"><?php esc_html_e('There are no Messages yet.', 'admin_messages'); ?></td>
                                    </tr>
                                <?php
                            }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php
    $output = ob_get_clean();
    
    echo($output);
}