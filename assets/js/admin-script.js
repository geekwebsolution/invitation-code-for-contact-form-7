jQuery(document).ready(function(){
    jQuery('.cf7ic-datepicker').datetimepicker({
        format:'m/d/Y H:i',
        minDate:new Date()
    });

    jQuery(".cf7ic-copy-to-clipboard").click( function() {
        var temp = jQuery("<input>");
        jQuery("body").append(temp);
        temp.val(jQuery('input[name="cf7ic_invitation_code"]').val()).select();
        document.execCommand("copy");
        temp.remove();
    });
    
    /** validation for add / update invitation code submit event  */
    var cf7icinitial = true;
    if (jQuery("body").hasClass("post-type-cf7ic_invite_codes")) {
        /**
         * Validation when user add new invite code or updating code
         */
        var $form = jQuery('#post');
        $form.submit(function (e) {
            
            if (!(document.body.dataset.isICVSuccess === 'true' || document.body.dataset.isICVSuccess === true)) {
                jQuery('#publishing-action .spinner').css('visibility', 'unset');
                
                var cf7icContactForms   = jQuery("input[name='cf7ic_contact_forms[]']");
                var cf7icInvitationCode = jQuery("input[name='cf7ic_invitation_code']");
                
                
                (cf7icContactForms.is(":checked")) 
                ? cf7icContactForms.removeClass('cf7ic-error').siblings('.cf7ic-contact-forms-notice').hide() 
                : cf7icContactForms.addClass('cf7ic-error').siblings('.cf7ic-contact-forms-notice').show().html('Please fill out this field.');

                (cf7icInvitationCode.val() == '')
                ? cf7icInvitationCode.addClass('cf7ic-error').parent().siblings('.cf7ic-invitation-code-notice').show().html('Please fill out this field.') 
                : cf7icInvitationCode.removeClass('cf7ic-error').parent().siblings('.cf7ic-invitation-code-notice').hide();
                
                var form_data = $form.serializeArray();
                
                jQuery.ajax({
                    type: 'POST',
                    url: cf7ic_custom_call.cf7ic_ajaxurl,
                    data: {
                        'action': 'cf7ic_invitation_post_validation',
                        'cf7ic_posts_data': form_data,
                    },
                    success: function (data) {
                        data = JSON.parse(data) ;
                        jQuery('#publishing-action .spinner').css('visibility', 'hidden');

                        
                        if (data.status) {
                            jQuery('#cf7ic-error-notice').html(data.message).parent().show();

                            
                            // If the last status was auto-draft and the save is triggered, edit the current URL.
                            if (jQuery( '#original_post_status' ).val() === 'auto-draft' && window.history.replaceState ) {
                                if (cf7icinitial) {
                                    cf7iclocation = window.location.href;
                                    cf7icinitial = false;
                                }
                                var cf7icNewPost = ( cf7iclocation.indexOf( 'wp-post-new-reload' ) !== -1 ) ? true : false;
                                if(cf7icNewPost){
                                    window.history.replaceState( null, null, cf7iclocation );
                                }
                            }
                        } else {
                            document.body.dataset.isICVSuccess = true;
                            // $form.submit();
                            jQuery('#publish').trigger('click');
                            return;
                        }
                    }
                });

                e.preventDefault();
                
            }

        });
    }

    // when user submits the form
    jQuery('body').on("submit", "#upload_csv_form", function(event){

        var error_elm = jQuery('.ajax-error');
        var response_elm = jQuery('.ajax-response');
        error_elm.html('');
        response_elm.html('');

        event.preventDefault();

        var form_elm = jQuery(this);

        var url = form_elm.data('url');
        var action = form_elm.data('action');
        var file_input_name = jQuery('#upload_csv_form').find('input[type=file]').attr('id');
        var form_data = new FormData();

        form_data.append('action', action);

        jQuery.each(jQuery(':input:not([type=submit]):not([type=text]):not([type=select])', '#upload_csv_form' )[0].files, function(i, file){
            console.log("Debug");
            form_data.append(file_input_name, file);
        });

        response_elm.html('Loading...');

        jQuery.ajax({
            type: 'POST',
            url: url,
            data: form_data,
            processData: false,
            contentType: false,
            cache: false
        }).success(function (response) {

            console.log(response);

        });
    });

});