declare var tinyMCE;
declare var CKEDITOR;

var $ = jQuery;
var content_changed = false;
var Saved = true;

var setCleanForm = function () {
    //tinyMCE.activeEditor.isNotDirty = 1;
    CKEDITOR.instances.description.setData('');
    content_changed = false;
};

window.onbeforeunload = function () {
    if (!Saved) {
        return "Are you sure you want to leave without save?";
    }
};

jQuery(document).ready(function ($) {
    "use strict";
    $('.jobsP_loader').addClass('hide');

    jQuery(document).on('submit', '#post', function (e) {
        e.preventDefault();
        $('.jobsP_loader').removeClass('hide');
        jQuery.ajax({
            url: ajaxurl,
            method: 'POST',
            data: jQuery('#post').serializeArray(),
            error: function (e1, e2, e3) {
                $('.jobsP_loader').addClass('hide');
                SATechJobsError(e3);
            }
        }).done(function (data) {
            if (data.substr(0, 2) === 'OK') {
                setCleanForm();
                Saved = true;
                window.location.href = '?page=SAjobsF_jobs_management';
            } else {
                $('.jobsP_loader').addClass('hide');
                SATechJobsError(data);
            }
        });
    }).on('change input', '#post, #description_ifr, #post input', function () {
        content_changed = true;
        Saved = false;
    }).on('click', '.submitdelete', setCleanForm);

    CKEDITOR.replace('description', {
        //uiColor: '#cceaee',
        width: '100%',
        extraPlugins: 'autogrow',
        autoGrow_minHeight: 200,
        autoGrow_maxHeight: 600,
        autoGrow_bottomSpace: 50,
        removePlugins: 'resize,wsc,scayt',
    });
});
