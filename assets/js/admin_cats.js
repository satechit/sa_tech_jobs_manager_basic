var $ = jQuery;
var Admin_cats = (function () {
    function Admin_cats() {
    }
    Admin_cats.prototype.load_cats = function () {
        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: { action: jobsP.ajax_key, 'command': 'get_cats' }
        }).done(function (data) {
            var l = Object.keys(data).length;
            var dn = $('.displaying-num');
            dn.html("" + (l == 1 ? "1 item" : l + " items"));
            if (l == 0) {
                $('#cats_table tbody').html(tmpl('cats_blank_tmpl', data));
                dn.html('0 item');
            }
            else {
                $('#cats_table tbody').html(tmpl('cats_tmpl', data));
            }
        });
    };
    Admin_cats.prototype.open_new_cat = function (e) {
        e.preventDefault();
        $('#cat_form').trigger('reset');
        $('#cat_form input[name=id]').val(0);
        $('#cat_form input[name=category]').val('');
        $('#cat_form input[name=is_active]').prop('checked', true);
        $('#form-modal').addClass('is-active');
    };
    Admin_cats.prototype.save_cat = function (e) {
        e.preventDefault();
        $('#form-modal').removeClass('is-active');
        $('.jobsP_loader').removeClass('hide');
        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: $('#cat_form').serialize(),
            error: function (e1, e2, e3) {
                $('.jobsP_loader').addClass('hide');
                $('body').trigger('click');
                SATechJobsError(e3);
            }
        }).done(function (data) {
            $('.jobsP_loader').addClass('hide');
            $('body').trigger('click');
            if (data == 'OK') {
                $('a.close-modal').trigger('click');
                Ac.load_cats();
            }
            else {
                SATechJobsError(data);
            }
        });
    };
    Admin_cats.prototype.del_cat = function (e) {
        e.preventDefault();
        if (!confirm('Are you sure you want to delete this category?'))
            return false;
        var id = $(this).attr('data-id');
        $('.jobsP_loader').removeClass('hide');
        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: { action: jobsP.ajax_key, 'command': 'delOneCat', id: id },
            error: function (e1, e2, e3) {
                $('.jobsP_loader').addClass('hide');
                SATechJobsError(e3);
            }
        }).done(function (data) {
            $('.jobsP_loader').addClass('hide');
            Ac.load_cats();
        });
    };
    Admin_cats.prototype.open_edit_cat = function (e) {
        e.preventDefault();
        var id = $(this).attr('data-id');
        $('.jobsP_loader').removeClass('hide');
        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: { action: jobsP.ajax_key, 'command': 'getOneCat', id: id },
            error: function (e1, e2, e3) {
                $('.jobsP_loader').addClass('hide');
                SATechJobsError(e3);
            }
        }).done(function (data) {
            $('.jobsP_loader').addClass('hide');
            if (typeof data !== 'object') {
                SATechJobsError(data);
            }
            else if (!data.id) {
                SATechJobsError('Cannot get data');
            }
            else {
                $('#cat_form [name=id]').val(data.id);
                $('#cat_form [name=category]').val(data.category);
                $('#cat_form [name=is_active]').prop('checked', data.is_active == 1);
                $('#form-modal').addClass('is-active');
            }
        });
    };
    Admin_cats.prototype.bulkAction = function (e) {
        e.preventDefault();
        var TopObj = $('#bulk-action-selector-top');
        if (TopObj.val() == '-1') {
            SATechJobsError('Select bulk action please');
            return false;
        }
        var Form = $('#bulk-action-form');
        if (Form.find('input[type=checkbox]:checked').length == 0) {
            SATechJobsError('Please select one or more categories');
            return false;
        }
        $('.jobsP_loader').removeClass('hide');
        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: Form.serialize(),
            error: function (e1, e2, e3) {
                $('.jobsP_loader').addClass('hide');
                SATechJobsError(e3);
            }
        }).done(function (data) {
            $('.jobsP_loader').addClass('hide');
            if (data.substr(0, 2) === 'OK') {
                $('#cb-select-all-1, .checkbox').prop('checked', false);
                SATechJobsSuccess('Action performed');
                Ac.load_cats();
            }
            else {
                SATechJobsError(data);
            }
        });
        TopObj.val('-1');
    };
    Admin_cats.prototype.bodyKeyDown = function (e) {
        if (e.ctrlKey && (e.key == 'm' || e.key == 'M')) {
            $('#add_cat_btn').trigger('click');
        }
    };
    return Admin_cats;
}());
var Ac = new Admin_cats();
jQuery(document).ready(function ($) {
    "use strict";
    Ac.load_cats();
    $(document).on('click', '#add_cat_btn', Ac.open_new_cat)
        .on('submit', '#cat_form', Ac.save_cat)
        .on('click', '.del_link', Ac.del_cat)
        .on('click', '.edit_link', Ac.open_edit_cat)
        .on('click', '#doaction', Ac.bulkAction)
        .on('keydown', 'body', Ac.bodyKeyDown);
    if (Url.queryString("add") == 'job') {
        $('#add_cat_btn').trigger('click');
        Url.updateSearchParam("add");
    }
});
//# sourceMappingURL=admin_cats.js.map