jQuery(document).ready(function ($) {
    "use strict";
    $(document).on('input', '#satech_jobs_search', function (e) {
        e.preventDefault();
        var filter = $(this).val().toUpperCase();
        var obj = $('.satech_job_listings');
        $.each(obj, function (index, obj) {
            var textValue = $(this).text();
            if (textValue.toUpperCase().indexOf(filter) > -1) {
                $(this).show();
            }
            else {
                $(this).hide();
            }
        });
        obj.unmark({});
        obj.mark(filter);
    }).on('click', '.job_detail_link', function (e) {
        var id = $(this).attr('data-id');
        $('div[data-id=' + id + ']').toggle();
    });
});
//# sourceMappingURL=shortcodes.js.map