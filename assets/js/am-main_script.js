jQuery(document).ready(function($){
    $('.alert-dismissible .btn-close').click(function(e){
        e.preventDefault();
        var id = $(this).data('message_id');
        
        if (id != undefined) {
            var url = ajaxData.ajaxUrl;

            var data = {
                action : 'am-read-message',
                nonce : ajaxData.readNonce,
                id : id
            };
    
            $.ajax({
                url : url,
                type : 'post',
                data : data,
                dataType : 'json',
                success: function (json) {
                    if (!json.error) {
                        console.log(json.success);
                    } else {
                        console.error(json.error);
                    }
                },
                error : function(err) {
                    console.error('Error: ' + err);
                    console.log(err);
                }
            });
        }

        $(this).parent().remove()
    });

    $('.btn.erase').click(function(e){
        e.preventDefault();

        if (confirm(wp.i18n.__('Are you sure you want to delete this message'))) {
            var $button = $(this);

            var id = $button.data('message-id');

            $button.addClass('disabled');

            var url = ajaxData.ajaxUrl;

            var data = {
                action : 'am-delete-message',
                nonce : ajaxData.deleteNonce,
                id : id
            };

            $.ajax({
                url : url,
                type : 'post',
                data : data,
                dataType : 'json',
                success: function (json) {
                    if (!json.error) {
                        $('#message-id-' + id).remove();
                    } else {
                        console.error(json.error);
                    }
                },
                complete: function() {
                    $button.removeClass('disabled');
                },
                error : function(err) {
                    console.error('Error: ' + err);
                    console.log(err);
                }
            });
        }       
    });

    $('.btn.view').click(function(e){
        e.preventDefault();

        var $button = $(this);

        var id = '#message-content-' + $button.data('message-id');

        console.log(id);

        $(id).show();
    });

    $(".modal .close").click(function(e){
        e.preventDefault();
        var $modal = $(this).parent();

        $($modal.parent()).hide();
    });
});