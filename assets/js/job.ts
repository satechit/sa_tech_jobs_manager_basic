declare var ajaxurl: any;
declare var jQuery: any;
declare var intlTelInput: any;

var $ = jQuery;

jQuery(document).ready(function ($) {
    "use strict";
    $(document).on('submit', '#job_form', JF.submitJobForm)
        .on('click', '#apply_job_btn', JF.applyJobBtn)
        .on('change', '.file-input', JF.fileInput)
    ;

    var input = document.querySelector("[name=contact]");
    window.intlTelInput(input, {
        customPlaceholder: function (selectedCountryPlaceholder, selectedCountryData) {
            return "e.g. " + selectedCountryPlaceholder;
        },
    });

});

class JobFrontend {
    submitJobForm(e) {
        e.preventDefault();

        var form = document.getElementById('job_form');
        // @ts-ignore
        var form_data = new FormData(form);
        $('.satech_loader').show();
        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: form_data,
            processData: false,
            contentType: false,
            cache: false,
            error: function (e1, e2, e3) {
                $('a[title="Refresh Image"]').trigger('click');
                alert('Error: ' + e3);
            }
        }).done(function (data) {
            if (data.substr(0, 2,) === 'OK') {
                $('#job_form').trigger('reset');
                $('div.application_details').addClass('hide');
                alert('Application submitted, thank you!');
                window.location.reload();
            } else {
                $('a[title="Refresh Image"]').trigger('click');
                alert('Error: ' + data);
            }
        }).always(function () {
            $('.satech_loader').hide();
        });
    }

    applyJobBtn(e) {
        e.preventDefault();
        let DIV = jQuery('div.application_details');
        DIV.toggleClass('hide');

        setTimeout(function () {
            DIV.trigger('reset');
            DIV.find('[name=name]').trigger('focus');
        }, 100);
    }

    fileInput(e) {
        if (typeof e.target.files[0] === 'undefined') {
            $('span#file-name').text('No file selected..');
        } else {
            $('span#file-name').text(e.target.files[0].name);
        }
    }
}

var JF = new JobFrontend();