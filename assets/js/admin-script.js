var isSuccess;
jQuery(document).ready(function(){
    jQuery('.cf7ic-datepicker').datetimepicker({        
        format:'m/d/Y H:i',
        minDate:new Date()
    });

    jQuery(".cf7ic-copy-to-clipboard").click( function() {
        console.time('time1');
        var temp = jQuery("<input>");
        jQuery("body").append(temp);
        temp.val(jQuery('input[name="cf7ic_invitation_code"]').val()).select();
        document.execCommand("copy");
        temp.remove();
    });


    if (jQuery("body").hasClass("post-type-cf7ic_invite_codes")) {
        var $form = jQuery('#post');
        
        $form.submit(function (e) {

            if (!(document.body.dataset.isICVSuccess === 'true' || document.body.dataset.isICVSuccess === true)) {

                var form_data = $form.serializeArray();

                jQuery.ajax({
                    type: 'POST',
                    url: cf7ic_custom_call.cf7ic_ajaxurl,
                    data: {
                        'action': 'cf7ic_invitation_post_validation',
                        'cf7ic_posts_data': form_data,
                    },
                    success: function (data) {
                        data = JSON.parse(data);

                        if (data.status) {
                            jQuery('#cf7ic-error-notice').html(data.message).parent().show();
                        } else {
                            document.body.dataset.isICVSuccess = true;
                            $form.submit();
                            return;
                        }
                    }
                });

                e.preventDefault();
            }
        });
    }

});