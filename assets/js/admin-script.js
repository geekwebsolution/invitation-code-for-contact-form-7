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

    /**
	 * Invitation Code actions
	 */
	var cf7ic_invitation_code_actions = {
		/**
		 * Initialize actions
		 */
		init: function() {
			jQuery( '.cf7ic-field-box #cf7ic-generate-code' ).on( 'click', this.cf7ic_generate_coupon_code );
		},
        /**
		 * Generate a Invitation Code
		 */
		cf7ic_generate_coupon_code: function( e ) {
			e.preventDefault();
            var cf7icRandomCode = '';
            var cf7icLength = 6;
            var cf7icAllowedChar = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
            for ( var i = 0; i < cf7icLength; i++ ) {
				cf7icRandomCode += cf7icAllowedChar.charAt(
					Math.floor( Math.random() * cf7icAllowedChar.length )
				);
			}
            jQuery(this).parent('.cf7ic-field-box').find('input[name=cf7ic_invitation_code]').val(cf7icRandomCode);
		}
    };
	cf7ic_invitation_code_actions.init();
});