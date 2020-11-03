$(document).ready(function(){
    
    if (docCookies.getItem("verify-all-message")) {

        var msg = docCookies.getItem("verify-all-message");
        docCookies.removeItem("verify-all-message", '/');
        console.log(docCookies.getItem("verify-all-message"));
        Swal.fire({
            title: 'Verify All',
            text: msg,
            icon: 'success',
            showCancelButton: false,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ok'
        }).then((result) => {
            if (result.isConfirmed) {

            }
        });

    }

    $('div#fixed-box-btn').addClass('in');

    $('#verify-all-form button').click(function(e){
        
        e.preventDefault();

        Swal.fire({
            title: 'Verify All',
            html: "By clicking on <b>Continue</b> you will enter in the verification mode.<br/><b>Are you sure you want to continue?</b>",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Continue'
        }).then((result) => {
            if (result.isConfirmed) {
                window.onbeforeunload = function () { }; // Rimuovo ulteriore conferma, ti ho già chiesto prima...
                Swal.fire({
                    title: 'Please Wait !',
                    html: 'Checking for fields verifiable',// add html attribute if you want or remove
                    allowOutsideClick: false,
                    onBeforeOpen: () => {
                        Swal.showLoading()
                    },
                });
                $('#verify-all-form').submit();
            }
        });
        
    });

    $('.btn-verify-all-submit').click(function(e){

        e.preventDefault();

        if ($('.check-field:checked').length > 0){
            Swal.fire({
                title: 'Are you sure?',
                text: "Do you want to set as verified the fields selected?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Please Wait !',
                        html: 'Data updating',// add html attribute if you want or remove
                        allowOutsideClick: false,
                        onBeforeOpen: () => {
                            Swal.showLoading()
                        },
                    });
                    $('#list-fields-verify-form').submit();
                }
            });
        }else{
            Swal.fire({
                title: 'Verify All',
                text: "Select at least one field to verify!",
                icon: 'warning',
                showCancelButton: false,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ok'
            }).then((result) => {
                
            });
        }

    });

    // Check or Uncheck all
    $('#check-all').change(function(){

        if($(this).is(':checked')){
            $('.check-all-section').prop('checked', true).change();
            //$('.check-field').prop('checked', true);
        }else{
            $('.check-all-section').prop('checked', false).change();
            //$('.check-field').prop('checked', false);
        }

    });

    // Check or Uncheck section
    $('.check-all-section').change(function(){

        var section = $(this).attr('data-section-ref');
        var checkedSection = $('.check-all-section:checked').length;
        var totSection = $('.check-all-section').length;
        if($(this).is(':checked')){
            $('[data-section="' + section +'"]').prop('checked',true);
        }else{
            $('[data-section="' + section +'"]').prop('checked',false);
        }

        if (checkedSection != totSection){
            $('#check-all').prop('checked',false);
        }

        if (checkedSection == totSection) {
            $('#check-all').prop('checked', true);
        }

    });

    // Checked on row click
    $('tr.row-detail, tr.row-section, tr.row-all').click(function(){

        if ($(this).find('.check-field').is(':checked')){
            $(this).find('.check-field').prop('checked', false).change();
        }else{
            $(this).find('.check-field').prop('checked', true).change();
        }
    });
    
    $('.check-field').click(function(e){
        e.stopPropagation();
    });

    $('tr.row-detail .check-field').change(function(){
        
        var section = $(this).attr('data-section');
        var checked = $('[data-section="' + section + '"]:checked').length;
        var totSection = $('[data-section="' + section + '"]').length;
        if (checked == 0){
            $('[data-section-ref="' + section + '"]').prop('checked', false);
            $('#check-all').prop('checked', false);
        }else{
            if (checked == totSection){
                $('[data-section-ref="' + section + '"]').prop('checked', true).change();
            }else{
                $('[data-section-ref="' + section + '"]').prop('checked', false);
                $('#check-all').prop('checked', false);
            }
        }

    });

});