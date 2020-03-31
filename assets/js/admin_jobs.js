var $ = jQuery;
var Category = '', Expired = '', JobType = '', SalaryType = '';
jQuery(document).ready(function ($) {
    "use strict";
    Aj.load_jobs();
    $(document).on('submit', '#jobs_form', Aj.save_job)
        .on('click', '.edit_link', Aj.openEdit)
        .on('click', '.del_link', Aj.deleteJob)
        .on('click', '.restore_link', Aj.restoreJob)
        .on('click', '.delete_file', Aj.deleteFile)
        .on('click', '#add_job_btn', Aj.openNewJob)
        .on('change', '#filter_cats', Aj.filterCats)
        .on('change', '#filter_expired', Aj.filterExpired)
        .on('change', '#filter_job_type', Aj.filterJobType)
        .on('change', '#filter_salary_type', Aj.filterSalaryType)
        .on('keydown', 'body', Aj.bodyKeyDown)
        .on('submit', 'form#search_form', Aj.search)
        .on('keydown', '#search_text', Aj.searchKey)
        .on('click', '.filter_link', Aj.filterLinks)
        .on('click', '.activate_link', Aj.ActiveDeactivate)
        .on('click', '#doaction', Aj.bulkAction)
        .on('blur', '#search', Aj.clearSearch);
});
var Admin_jobs = (function () {
    function Admin_jobs() {
    }
    Admin_jobs.prototype.load_jobs = function () {
        var Data = {
            action: jobsP.ajax_key,
            command: 'get_jobs',
            job_category_id: Category,
            expired: Expired,
            job_type: JobType,
            salary_type: SalaryType
        };
        var all_fields = Url.parseQuery();
        $.each(all_fields, function (field, value) {
            if (field == 'page')
                return;
            Data[field] = value;
        });
        var s = Url.queryString('s');
        if (typeof s !== 'undefined') {
            Data['search'] = s;
            if (s == '') {
                $('#search').val('');
                Url.updateSearchParam('s');
            }
        }
        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: Data,
            error: function (e1, e2, e3) {
                SATechJobsError(e3);
            }
        }).done(function (data) {
            if (typeof data !== 'object') {
                $('#jobs_table tbody').html("<tr><td colspan=\"9\" class=\"text-center\"><i>-- " + data + " --</i></td></td>");
            }
            else if (Object.keys(data).length === 0) {
                $('#jobs_table tbody').html(tmpl('jobs_blank_tmpl', data));
            }
            else {
                $('#jobs_table tbody').html(tmpl('jobs_tmpl', data));
                $('.displaying-num').html(Object.keys(data).length + ' items');
            }
            if (Url.queryString('is_active')) {
                $('a[data-field=clear_all]').removeClass('current');
                if (Url.queryString('is_active') == '1') {
                    $("a[data-field=is_active][data-value='1']").addClass('current');
                }
                else if (Url.queryString('is_active') == '0') {
                    $("a[data-field=is_active][data-value='0']").addClass('current');
                }
            }
            else if (Url.queryString('expired')) {
                $('a[data-field=expired]').addClass('current');
            }
            else if (Url.queryString('deleted')) {
                $('a[data-field=deleted]').addClass('current');
            }
            Aj.update_counters();
        });
    };
    Admin_jobs.prototype.update_counters = function () {
        var Data = {
            action: jobsP.ajax_key,
            command: 'get_job_counters',
        };
        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: Data,
        }).done(function (data) {
            $.each(data, function (i, v) {
                if (i == 'all') {
                    var v2 = (v < 10 ? '0' : '') + v;
                }
                $('.subsubsub span.' + i).text('(' + v + ')');
            });
        });
    };
    Admin_jobs.prototype.save_job = function (e) {
        e.preventDefault();
        var Loader = $('.jobsP_loader');
        Loader.removeClass('hide');
        $('a.close-modal').trigger('click');
        var formData = new FormData($(this)[0]);
        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            error: function (e1, e2, e3) {
                Loader.addClass('hide');
                SATechJobsError(e3);
            }
        }).done(function (data) {
            Loader.addClass('hide');
            if (data == 'OK') {
                SATechJobsSuccess('Job saved successfully!');
                Aj.load_jobs();
            }
            else {
                SATechJobsError(data);
            }
        });
    };
    Admin_jobs.prototype.openNewJob = function (e) {
        e.preventDefault();
        var Loader = $('.jobsP_loader');
        var Form = $('#jobs_form');
        Form.trigger('reset');
        Form.find('[name=id]').val(0);
        Form.find('[name=expiry_date]').val(satechjobs_date('Y-m-d', satechjobs_strtotime('+1 Month')));
        Form.modal({
            clickClose: false
        });
    };
    Admin_jobs.prototype.openEdit = function (e) {
        e.preventDefault();
        var Loader = $('.jobsP_loader');
        Loader.removeClass('hide');
        var id = $(this).attr('data-id');
        $.ajax({
            method: 'POST',
            url: ajaxurl,
            data: { action: jobsP.ajax_key, command: 'get_job', id: id },
            error: function (e1, e2, e3) {
                SATechJobsError(e3);
            }
        }).always(function () {
            Loader.addClass('hide');
        }).done(function (data) {
            if (typeof data !== 'object') {
                SATechJobsError(data);
            }
            else if (!data.id) {
                SATechJobsError('Loading data error');
            }
            else {
                var Form = $('#jobs_form');
                Form.find('[name=id]').val(data.id);
                $('#job_category_id').val(data.job_category_id);
                Form.find('[name=title]').val(data.title);
                Form.find('[name=expiry_date]').val(data.expiry_date);
                Form.find('[name=description]').val(data.description);
                Form.find('[name=file]').val('');
                Form.find('[name=is_active]').prop('checked', data.is_active == 1);
                Form.find('[name=job_type]').val(data.job_type);
                Form.find('[name=salary_type]').val(data.salary_type);
                Form.find('[name=salary]').val(data.salary);
                Form.modal({
                    clickClose: false
                });
            }
        });
    };
    ;
    Admin_jobs.prototype.restoreJob = function (e) {
        e.preventDefault();
        $(this).closest('tr').fadeOut('slow', function () {
            $(this).remove();
        });
        var id = $(this).attr('data-id');
        var Loader = $('.jobsP_loader');
        Loader.removeClass('hide');
        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: { action: jobsP.ajax_key, 'command': 'restore_job', id: id },
            error: function (e1, e2, e3) {
                SATechJobsError(e3);
            }
        }).always(function () {
            Loader.addClass('hide');
        }).done(function (data) {
            Aj.load_jobs();
        });
    };
    Admin_jobs.prototype.deleteJob = function (e) {
        e.preventDefault();
        if (!confirm('Are you sure you want to delete this job?'))
            return false;
        $(this).closest('tr').fadeOut('slow', function () {
            $(this).remove();
        });
        var id = $(this).attr('data-id');
        var Loader = $('.jobsP_loader');
        Loader.removeClass('hide');
        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: { action: jobsP.ajax_key, 'command': 'delete_job', id: id },
            error: function (e1, e2, e3) {
                SATechJobsError(e3);
            }
        }).always(function () {
            Loader.addClass('hide');
        }).done(function (data) {
            Aj.load_jobs();
        });
    };
    ;
    Admin_jobs.prototype.deleteFile = function (e) {
        e.preventDefault();
        if (!confirm('Are you sure you want to delete this file?'))
            return false;
        var path = $(this).attr('data-path');
        var T = $(this);
        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: { action: jobsP.ajax_key, 'command': 'delete_file', path: path },
            error: function (e1, e2, e3) {
                SATechJobsError(e3);
            }
        }).done(function (data) {
            if (data.substr(0, 2) === 'OK') {
                T.closest('div').fadeOut('slow', function () {
                    $(this).remove();
                });
            }
            else {
                SATechJobsError(data);
            }
        });
    };
    Admin_jobs.prototype.filterCats = function (e) {
        e.preventDefault();
        Category = $(this).val();
        Aj.load_jobs();
    };
    Admin_jobs.prototype.filterExpired = function (e) {
        e.preventDefault();
        Expired = $(this).val();
        Aj.load_jobs();
    };
    Admin_jobs.prototype.filterJobType = function (e) {
        e.preventDefault();
        JobType = $(this).val();
        Aj.load_jobs();
    };
    Admin_jobs.prototype.filterSalaryType = function (e) {
        e.preventDefault();
        SalaryType = $(this).val();
        Aj.load_jobs();
    };
    Admin_jobs.prototype.search = function (e) {
        e.preventDefault();
        var val = $(this).find('[name=s]').val();
        Url.updateSearchParam('s', val);
        Aj.load_jobs();
    };
    Admin_jobs.prototype.filterLinks = function (e) {
        e.preventDefault();
        var field = $(this).attr('data-field');
        var value = $(this).attr('data-value');
        $('.subsubsub li a').removeClass('current');
        $(this).addClass('current').trigger('blur');
        var is_clear = (field == 'clear_all');
        $('#search').val('');
        var all_fields = Url.parseQuery();
        $.each(all_fields, function (field, value) {
            if (field == 'page')
                return;
            Url.updateSearchParam(field);
        });
        if (!is_clear) {
            Url.updateSearchParam('s');
            Url.updateSearchParam(field, value);
        }
        var deleted = Url.queryString('deleted');
        if (deleted && deleted === '1') {
            $('#bulk-action-selector-top').append("<option value=\"restore\">Restore</option>");
        }
        else {
            $("#bulk-action-selector-top option[value='restore']").remove();
        }
        Aj.load_jobs();
    };
    Admin_jobs.prototype.ActiveDeactivate = function (e) {
        e.preventDefault();
        var value = $(this).attr('data-value');
        var id = $(this).attr('data-id');
        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: { action: jobsP.ajax_key, 'command': 'change_job_activation', id: id, value: value },
            error: function (e1, e2, e3) {
                SATechJobsError(e3);
            }
        }).done(function (data) {
            Aj.load_jobs();
        });
    };
    Admin_jobs.prototype.bodyKeyDown = function (e) {
        if (e.ctrlKey && (e.key == 'm' || e.key == 'M')) {
            $('#add_job_btn').trigger('click');
        }
    };
    Admin_jobs.prototype.searchKey = function (e) {
        if (e.key == 'Enter') {
            e.preventDefault();
            var val = $(this).val();
            Url.updateSearchParam('s', val);
            Aj.load_jobs();
        }
    };
    Admin_jobs.prototype.bulkAction = function (e) {
        e.preventDefault();
        var selectAllObj = $('#bulk-action-selector-top');
        if (selectAllObj.val() == '-1') {
            SATechJobsError('Select bulk action please');
            return false;
        }
        var checkedBoxes = $('#bulk-action-form');
        if (checkedBoxes.find('input[type=checkbox]:checked').length == 0) {
            SATechJobsError('Please select one or more job ads');
            return false;
        }
        $('.jobsP_loader').removeClass('hide');
        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: checkedBoxes.serialize(),
            error: function (xhr, status, error) {
                $('.jobsP_loader').addClass('hide');
                SATechJobsError('XJR: ' + xhr + '<br>Status: ' + status + 'Error: ' + error);
            }
        }).done(function (data) {
            $('.jobsP_loader').addClass('hide');
            if (data.substr(0, 2) === 'OK') {
                $('#cb-select-all-1, .checkbox').prop('checked', false);
                SATechJobsSuccess('Action performed');
                Aj.load_jobs();
            }
            else {
                SATechJobsError(data);
            }
        });
        selectAllObj.val('-1');
    };
    Admin_jobs.prototype.clearSearch = function (e) {
        var val = $.trim($(this).val());
        if (val === '') {
            Url.updateSearchParam('s');
            Aj.load_jobs();
        }
    };
    return Admin_jobs;
}());
var Aj = new Admin_jobs();
//# sourceMappingURL=admin_jobs.js.map