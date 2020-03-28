declare var jQuery;

jQuery(document).ready(function ($) {
    "use strict";

    $(document).on('input', '#satech_jobs_search', function (e) {
        e.preventDefault();

        let filter = $(this).val().toUpperCase();

        let obj = $('.satech_job_listings');

        $.each(obj, function (index, obj) {
            let textValue = $(this).text();
            if (textValue.toUpperCase().indexOf(filter) > -1) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });

        obj.unmark({});
        obj.mark(filter);
    }).on('click', '.job_detail_link', function (e) {
        let id = $(this).attr('data-id');

        $('div[data-id=' + id + ']').toggle();
    });
});