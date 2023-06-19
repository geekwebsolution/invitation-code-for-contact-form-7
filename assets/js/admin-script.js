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
});