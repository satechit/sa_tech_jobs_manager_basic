var $ = jQuery;
jQuery(document).ready(function ($) {
    "use strict";
    var Admin_settings = (function () {
        function Admin_settings() {
        }
        Admin_settings.prototype.save_setting = function (e) {
            e.preventDefault();
            var name = $(this).attr('name');
            if (typeof name === 'undefined') {
                SATechJobsError('Control name not found.');
                return false;
            }
            $('#loader_span').removeClass('hide');
            $('.jobsP_loader').removeClass('hide');
            var old_value = $(this).attr('data-old-value');
            var THIS = $(this);
            if (THIS.prop('tagName') === 'INPUT' || THIS.prop('tagName') === 'SELECT') {
                THIS.parent().addClass('is-loading');
            }
            var Obj = $(this);
            var new_value = $(this).val();
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
                }
                else {
                    Obj.val(old_value);
                    SATechJobsError(data);
                }
            });
        };
        Admin_settings.prototype.copyShortCode = function (e) {
            e.preventDefault();
            var obj = $(this).prev();
            clipboard.writeText(obj.val());
            SATechJobsSuccess('Shortcode copied!');
        };
        return Admin_settings;
    }());
    var As = new Admin_settings();
    $(document).on('click', '#remove_data', function (e) {
        e.preventDefault();
        if (!confirm('Are you sure you want to delete all data\nand uploaded files used by this plugin?'))
            return false;
        if (!confirm("Warning!!!\n\nThis action is not reversible."))
            return false;
        $('.jobsP_loader').removeClass('hide');
        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: { action: jobsP.ajax_key, 'command': 'resetAllData' },
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
        .on('click', '.copy_btn', As.copyShortCode);
});
function openCity(evt, cityName) {
    var i, tabcontent, tablinks;
    tabcontent = document.getElementsByClassName("tabcontent");
    for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
    }
    tablinks = document.getElementsByClassName("tablinks");
    for (i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace(" active", "");
    }
    document.getElementById(cityName).style.display = "block";
    evt.currentTarget.className += " active";
}
//# sourceMappingURL=admin_settings.js.map