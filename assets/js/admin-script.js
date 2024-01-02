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
    
    var cf7icinitial = true;
    
    if (jQuery("body").hasClass("post-type-cf7ic_invite_codes")) {
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

});