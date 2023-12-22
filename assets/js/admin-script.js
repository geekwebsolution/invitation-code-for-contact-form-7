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
    jQuery('body').on('click','#cf7ic_ImportData',function () {
        jQuery.ajax({
            url: cf7ic_custom_call.ajaxurl,
            type: "POST",
            data: {
                action: "cf7ic_export_data",
            },
            success: function(data) {
            //     console.log(data);
            //    /*
            //    * Make CSV downloadable
            //    */
            //   var downloadLink = document.createElement("a");
            //   var fileData = ['\ufeff'+data];

            //   var blobObject = new Blob(fileData,{
            //      type: "text/csv;charset=utf-8;"
            //    });

            //   var url = URL.createObjectURL(blobObject);
            //   downloadLink.href = url;
            //   downloadLink.download = "csv_export.csv";

            //   /*
            //    * Actually download CSV
            //    */
            //   document.body.appendChild(downloadLink);
            //   downloadLink.click();
            //   document.body.removeChild(downloadLink);
            },
        });
    })
});