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
        console.timeEnd('time1');
    });

    var currentRequest = null;
    jQuery(document).on("change", "input[name='cf7ic_invitation_code']", function(e){
        e.preventDefault();
        var cf7icCode = jQuery(this).val();
        const cf7icselcectorContactForm = jQuery('#cf7ic_meta_box input[name="cf7ic_contact_forms[]"]');
        const cf7icContactSelection = jQuery('#cf7ic_meta_box input[name="cf7ic_contact_forms[]"]:checked');
       
        if(cf7icContactSelection.length == 0){
            cf7icselcectorContactForm.parent().find('.notice').show();
            return false;
        }else{
            cf7icselcectorContactForm.parent().find('.notice').hide();
        }

        const cf7icSelectedFormsId = [];
        cf7icContactSelection.each((i) => {
            cf7icSelectedFormsId.push(cf7icContactSelection[i].value);
        });
 
        currentRequest = jQuery.ajax({
            type: 'POST',
            url: cf7ic_custom_call.cf7ic_ajaxurl,
            data: {
                'action': 'cf7ic_invitation_code_validation',
                'cf7ic_code': cf7icCode,
                'cf7ic_selected_form_id': cf7icSelectedFormsId
            },
            success: function (data) {
                console.log(data);
            }
 
        });    



        
    });



});