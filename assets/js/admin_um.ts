declare var jQuery: any;

jQuery(document).ready(function ($) {
    "use strict";

    $(document).on('click', '.atitle', function () {
        $(this).next('.acontent').slideToggle('slow');
    });
});