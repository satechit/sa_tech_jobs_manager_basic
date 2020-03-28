declare var ajaxurl: any;
declare var jQuery: any;
declare var tmpl: any;
declare var jobsP: any;
declare var clipboard: any;

var $ = jQuery;

jQuery(document).ready(function ($) {
    "use strict";

    class Admin_settings {
        save_setting(e) {
            e.preventDefault();

            let name = $(this).attr('name');
            if (typeof name === 'undefined') {
                SATechJobsError('Control name not found.');
                return false;
            }
            $('#loader_span').removeClass('hide');
            $('.jobsP_loader').removeClass('hide');

            let old_value = $(this).attr('data-old-value');
            let THIS = $(this);
            if (THIS.prop('tagName') === 'INPUT' || THIS.prop('tagName') === 'SELECT') {
                THIS.parent().addClass('is-loading');
            }

            let Obj = $(this);
            let new_value = $(this).val();
            $.ajax({
                url: ajaxurl,
                method: 'POST',
                data: {
                    action: jobsP.ajax_key,
                    command: 'save_option',
                    option_name: name,
                    option_value: new_value
                },
                error: function (e1, e2, e3) {
                    Obj.val(old_value);
                    SATechJobsError(e3);
                }
            }).always(function () {
                $('#loader_span').addClass('hide');
                $('.jobsP_loader').addClass('hide');
                THIS.parent().removeClass('is-loading');
            }).done(function (data) {
                if (data.substr(0, 2) === 'OK') {
                    Obj.attr('data-old-value', new_value);
                } else {
                    Obj.val(old_value);
                    SATechJobsError(data);
                }
            });
        }

        copyShortCode(e) {
            e.preventDefault();

            let obj = $(this).prev();

            clipboard.writeText(obj.val());
            SATechJobsSuccess('Shortcode copied!');
        }
    }
    let As = new Admin_settings();

    $(document).on('click', '#remove_data', function (e) {
        e.preventDefault();

        if (!confirm('Are you sure you want to delete all data\nand uploaded files used by this plugin?')) return false;
        if (!confirm("Warning!!!\n\nThis action is not reversible.")) return false;

        $('.jobsP_loader').removeClass('hide');

        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: {action: jobsP.ajax_key, 'command': 'resetAllData'},
            error: function (e1, e2, e3) {
                $('.jobsP_loader').addClass('hide');
                SATechJobsError(e3);
            }
        }).done(function (data) {
            $('.jobsP_loader').addClass('hide');
            SATechJobsSuccess("All data has been reset!");
        });
    });

    $(document).on('change', '.save_setting', As.save_setting)
        .on('click', '.copy_btn', As.copyShortCode)
    ;
});

function openCity(evt, cityName) {
    // Declare all variables
    var i, tabcontent, tablinks;

    // Get all elements with class="tabcontent" and hide them
    tabcontent = document.getElementsByClassName("tabcontent");
    for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
    }

    // Get all elements with class="tablinks" and remove the class "active"
    tablinks = document.getElementsByClassName("tablinks");
    for (i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace(" active", "");
    }

    // Show the current tab, and add an "active" class to the button that opened the tab
    document.getElementById(cityName).style.display = "block";
    evt.currentTarget.className += " active";
}