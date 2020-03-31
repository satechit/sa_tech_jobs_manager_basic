declare var ajaxurl: any;
declare var jQuery: any;
declare var tmpl: any;
declare var jobsP: any;
declare var Warning: any;

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
        .on('blur', '#search', Aj.clearSearch)
    ;
});

class Admin_jobs {
    load_jobs() {
        let Data = {
            action: jobsP.ajax_key,
            command: 'get_jobs',
            job_category_id: Category,
            expired: Expired,
            job_type: JobType,
            salary_type: SalaryType
        };

        let all_fields = Url.parseQuery();

        $.each(all_fields, function (field, value) {
            if (field == 'page') return;

            Data[field] = value;
        });

        let s = Url.queryString('s');
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
                $('#jobs_table tbody').html(`<tr><td colspan="9" class="text-center"><i>-- ${data} --</i></td></td>`);
            } else if (Object.keys(data).length === 0) {
                $('#jobs_table tbody').html(tmpl('jobs_blank_tmpl', data));
            } else {
                $('#jobs_table tbody').html(tmpl('jobs_tmpl', data));
                $('.displaying-num').html(Object.keys(data).length + ' items');
            }

            if (Url.queryString('is_active')) {
                $('a[data-field=clear_all]').removeClass('current');
                if (Url.queryString('is_active') == '1') {
                    $("a[data-field=is_active][data-value='1']").addClass('current');
                } else if (Url.queryString('is_active') == '0') {
                    $("a[data-field=is_active][data-value='0']").addClass('current');
                }
            } else if (Url.queryString('expired')) {
                $('a[data-field=expired]').addClass('current');
            } else if (Url.queryString('deleted')) {
                $('a[data-field=deleted]').addClass('current');
            }

            Aj.update_counters();
        });
    }

    update_counters() {
        let Data = {
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
                    let v2 = (v < 10 ? '0' : '') + v;
                    //$('span.jobsp_badge').text(v2);
                }
                $('.subsubsub span.' + i).text('(' + v + ')');
            });
        });
    }

    save_job(e) {
        e.preventDefault();

        let Loader = $('.jobsP_loader');
        Loader.removeClass('hide');

        $('a.close-modal').trigger('click');
        let formData = new FormData($(this)[0]);
        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: formData,
            //async: false,
            cache: false,
            contentType: false,
            //enctype: 'multipart/form-data',
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
            } else {
                SATechJobsError(data);
            }
        });
    }

    openNewJob(e) {
        e.preventDefault();

        let Loader = $('.jobsP_loader');

        let Form = $('#jobs_form');

        Form.trigger('reset');
        Form.find('[name=id]').val(0);
        Form.find('[name=expiry_date]').val(satechjobs_date('Y-m-d', satechjobs_strtotime('+1 Month')));
        Form.modal({
            clickClose: false
        });
    }

    openEdit(e) {
        e.preventDefault();

        let Loader = $('.jobsP_loader');
        Loader.removeClass('hide');

        let id = $(this).attr('data-id');

        $.ajax({
            method: 'POST',
            url: ajaxurl,
            data: {action: jobsP.ajax_key, command: 'get_job', id: id},
            error: function (e1, e2, e3) {
                SATechJobsError(e3);
            }
        }).always(function () {
            Loader.addClass('hide');
        }).done(function (data) {
            if (typeof data !== 'object') {
                SATechJobsError(data);
            } else if (!data.id) {
                SATechJobsError('Loading data error');
            } else {
                let Form = $('#jobs_form');

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

    restoreJob(e) {
        e.preventDefault();

        $(this).closest('tr').fadeOut('slow', function () {
            $(this).remove();
        });

        let id = $(this).attr('data-id');

        let Loader = $('.jobsP_loader');
        Loader.removeClass('hide');

        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: {action: jobsP.ajax_key, 'command': 'restore_job', id: id},
            error: function (e1, e2, e3) {
                SATechJobsError(e3);
            }
        }).always(function () {
            Loader.addClass('hide');
        }).done(function (data) {
            Aj.load_jobs();
        });
    }

    deleteJob(e) {
        e.preventDefault();

        if (!confirm('Are you sure you want to delete this job?')) return false;

        $(this).closest('tr').fadeOut('slow', function () {
            $(this).remove();
        });

        let id = $(this).attr('data-id');

        let Loader = $('.jobsP_loader');
        Loader.removeClass('hide');

        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: {action: jobsP.ajax_key, 'command': 'delete_job', id: id},
            error: function (e1, e2, e3) {
                SATechJobsError(e3);
            }
        }).always(function () {
            Loader.addClass('hide');
        }).done(function (data) {
            Aj.load_jobs();
        });
    };

    deleteFile(e) {
        e.preventDefault();

        if (!confirm('Are you sure you want to delete this file?')) return false;

        let path = $(this).attr('data-path');
        let T = $(this);

        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: {action: jobsP.ajax_key, 'command': 'delete_file', path: path},
            error: function (e1, e2, e3) {
                SATechJobsError(e3);
            }
        }).done(function (data) {

            if (data.substr(0, 2) === 'OK') {
                T.closest('div').fadeOut('slow', function () {
                    $(this).remove();
                });
            } else {
                SATechJobsError(data);
            }
        });
    }

    filterCats(e) {
        e.preventDefault();

        Category = $(this).val();
        Aj.load_jobs();
    }

    filterExpired(e) {
        e.preventDefault();

        Expired = $(this).val();
        Aj.load_jobs();
    }

    filterJobType(e) {
        e.preventDefault();

        JobType = $(this).val();
        Aj.load_jobs();
    }

    filterSalaryType(e) {
        e.preventDefault();

        SalaryType = $(this).val();
        Aj.load_jobs();
    }

    search(e) {
        e.preventDefault();

        let val = $(this).find('[name=s]').val();
        Url.updateSearchParam('s', val);

        Aj.load_jobs();
    }

    filterLinks(e) {
        e.preventDefault();

        let field = $(this).attr('data-field');
        let value = $(this).attr('data-value');
        $('.subsubsub li a').removeClass('current');
        $(this).addClass('current').trigger('blur');

        let is_clear = (field == 'clear_all');
        $('#search').val('');

        let all_fields = Url.parseQuery();
        $.each(all_fields, function (field, value) {
            if (field == 'page') return;
            Url.updateSearchParam(field);
        });

        if (!is_clear) {
            Url.updateSearchParam('s');
            Url.updateSearchParam(field, value);
        }

        let deleted = Url.queryString('deleted');
        if (deleted && deleted === '1') {
            $('#bulk-action-selector-top').append(`<option value="restore">Restore</option>`);
        } else {
            $("#bulk-action-selector-top option[value='restore']").remove();
        }

        Aj.load_jobs();
    }

    ActiveDeactivate(e) {
        e.preventDefault();

        let value = $(this).attr('data-value');
        let id = $(this).attr('data-id');

        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: {action: jobsP.ajax_key, 'command': 'change_job_activation', id: id, value: value},
            error: function (e1, e2, e3) {
                SATechJobsError(e3);
            }
        }).done(function (data) {
            Aj.load_jobs();
        });
    }

    bodyKeyDown(e) {
        if (e.ctrlKey && (e.key == 'm' || e.key == 'M')) {
            $('#add_job_btn').trigger('click');
        }
    }

    searchKey(e) {
        if (e.key == 'Enter') {
            e.preventDefault();
            let val = $(this).val();
            Url.updateSearchParam('s', val);

            Aj.load_jobs();
        }
    }

    bulkAction(e) {
        e.preventDefault();
        let selectAllObj = $('#bulk-action-selector-top');
        if (selectAllObj.val() == '-1') {
            SATechJobsError('Select bulk action please');
            return false;
        }

        let checkedBoxes = $('#bulk-action-form');
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
            } else {
                SATechJobsError(data);
            }
        });

        selectAllObj.val('-1');
    }

    clearSearch(e) {
        let val = $.trim($(this).val());

        if (val === '') {
            Url.updateSearchParam('s');
            Aj.load_jobs();
        }
    }
}

var Aj = new Admin_jobs();