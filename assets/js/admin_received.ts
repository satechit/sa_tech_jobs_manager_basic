declare var ajaxurl: any;
declare var jQuery: any;
declare var tmpl: any;
declare var jobsP: any;
declare var Success: any;
declare var Status: any;
declare var CKEDITOR: any;

var $ = jQuery;
let Page = 1;
var Category = '';
var changeStatuswithEmail: any = null;

jQuery(document).ready(function ($) {
    "use strict";
    AR.load_data();

    $(document).on('click', '.tablenav-pages-navspan', AR.change_page)
        .on('change', '#current-page-selector', AR.change_page_number)
        .on('click', '#doaction', AR.bulkAction)
        .on('click', '.view_button', AR.viewJob)
        .on('change', '#filter_cats', AR.changeCategory)
        .on('click', '.del_single_app', AR.delSingleApp)
        .on('click', '.restore_single_app', AR.restoreSingleApp)
        .on('click', '#add_note_btn', AR.addNote)
        .on('click', '.del_note_btn', AR.delNote)
        .on('click', '.email_to', AR.openEmail)
        .on('submit', '#email_form, #email_form2', AR.sendEmail)
        .on('click', '.cancel_button', AR.cancelButton)
        .on('submit', 'form#search_form', AR.search)
        .on('keydown', '#search_text', AR.searchKey)
        .on('click', '.filter_link', AR.filterLinks)
        .on('blur', '#search', AR.clearSearch)
        .on('keydown', '#new_note_text', function (e) {
            if (e.key == 'Enter') {
                e.preventDefault();
                $('#add_note_btn').trigger('click');
            }
        })
        .on('keydown', '#current-page-selector', function (e) {
            if (e.key == 'Enter') {
                e.preventDefault();
                Page = $(this).val();
                AR.load_data();
            }
        })
        .on('click', '.log_article .message-header', AR.openCloseLog)
        .on('click', 'a.file_link, a.file_link2', function () {
            $(this).trigger('blur');
        })
        .on('click', '.blur_out', function () {
            $(this).trigger('blur');
        })
    ;
});

class Admin_received {
    constructor() {

    }

    update_counter() {
        $.ajax({
            url: ajaxurl,
            data: {action: jobsP.ajax_key, command: 'get_application_counter_all_types'},
            method: 'POST',
        }).done(function (data) {
            if (data.total) {
                $('span.count.all').text('(' + data.total + ')');
            }

            $.each(Status, function (status_label, status_number) {
                if (data['status_' + status_number]) {
                    $('span.count.status_' + status_number).text('(' + data['status_' + status_number] + ')');
                }
            });

            if (data.deleted) {
                $('span.count.deleted').text('(' + data.deleted + ')');
            }
        });
    }

    load_data() {
        let Data = {action: jobsP.ajax_key, command: 'get_received_jobs', page: Page, job_category_name: Category};

        let all_fields = Url.parseQuery();
        $.each(all_fields, function (field, value) {
            if (field == 'page') {
                return;
            } else if (field == 's') {
                Data['search'] = value;
            } else {
                Data[field] = value;
            }
        });

        $.ajax({
            url: ajaxurl,
            data: Data,
            method: 'POST',
            error: function (e1, e2, e3) {
                SATechJobsError(e3);
            }
        }).done(function (data) {
            $('#received_table tbody').html(tmpl('table_tmpl', data));

            if (data.total_pages > 1) {
                $('div.tablenav-pages').removeClass('one-page');
            } else {
                $('div.tablenav-pages').addClass('one-page');
            }
            $('#current-page-selector').val(data.page);
            $('span.total-pages').text(data.total_pages);
            $('.displaying-num').text(data.total_records + ' items');
            if (data.page > 1) {
                $('#prev_page_link, #first_page_link').removeClass('disabled');
            } else {
                $('#prev_page_link, #first_page_link').addClass('disabled');
            }
            if (data.page < data.total_pages) {
                $('#next_page_link, #last_page_link').removeClass('disabled');
            } else {
                $('#next_page_link, #last_page_link').addClass('disabled');
            }
            $('#prev_page_link').attr('data-page', data.page - 1);
            $('#next_page_link').attr('data-page', data.page + 1);
            $('#last_page_link').attr('data-page', data.total_pages);

            let deleted = Url.queryString('deleted');
            // if (deleted && deleted === '1') {
            //     $('#bulk-action-selector-top').append(`<option value="restore">Restore</option>`);
            // } else {
            //     $("#bulk-action-selector-top option[value='restore']").remove();
            // }

            if (Url.queryString('status')) {
                $('a[data-field=clear_all]').removeClass('current');
                $('a.filter_link[data-value=' + Url.queryString('status') + ']').addClass('current');
            }

            AR.update_counter();
        });
    }

    change_page(e) {
        e.preventDefault();

        if ($(this).hasClass('disabled')) {
            return false;
        }

        Page = $(this).attr('data-page');
        AR.load_data();
    }

    change_page_number(e) {
        e.preventDefault();

        let page = Number($(this).val());
        // @ts-ignore
        if (Number.isNaN(page)) {
            page = 1;
        }
        let last_page = Number($('#last_page_link').attr('data-page'));
        if (page > last_page) page = last_page;
        $('#current-page-selector').val(page);
        Page = page;
        AR.load_data();
    }

    bulkAction(e) {
        e.preventDefault();
        let bulkSelector = $('#bulk-action-selector-top');

        if (bulkSelector.val() == '-1') {
            SATechJobsError('Select bulk action please');
            return false;
        }

        let bulkForm = $('#bulk-action-form');
        if (bulkForm.find('input[type=checkbox]:checked').length == 0) {
            SATechJobsError('Please select one or more jobs');
            return false;
        }

        let newAction = bulkSelector.val();

        $('.jobsP_loader').removeClass('hide');
        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: bulkForm.serialize(),
            error: function (e1, e2, e3) {
                $('.jobsP_loader').addClass('hide');
                SATechJobsError(e3);
            }
        }).done(function (data) {
            $('.jobsP_loader').addClass('hide');
            if (data.substr(0, 2) === 'OK') {
                $('#cb-select-all-1, .checkbox').prop('checked', false);
                if (newAction == 'restore') {
                    SATechJobsSuccess('Selected jobs restored');
                } else if (newAction == 'delete') {
                    SATechJobsSuccess('Selected jobs deleted');
                }
                AR.load_data();
            } else {
                SATechJobsError(data);
            }
        });

        bulkSelector.val('-1');
    }

    viewJob(e) {
        e.preventDefault();

        changeStatuswithEmail = null;
        $('#email_notification_message').addClass('hide');

        let id = $(this).attr('data-id');
        $('.jobsP_loader').removeClass('hide');

        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: {action: jobsP.ajax_key, command: 'get_received_job', id: id},
            error: function (e1, e2, e3) {
                SATechJobsError(e3);
            }
        }).always(function () {
            $('.jobsP_loader').addClass('hide');
        }).done(function (data) {
            $('tr[data-id=' + id + ']').removeClass('active');
            let instance = $.fancybox.open(tmpl('receive_job_tmpl', data), {
                type: 'html',
                touch: false,
                fullScreen: true,
                modal: true,
                width: '100%',
                afterShow: function () {
                    setTimeout(function () {
                    }, 50);
                }
            });
            AR.load_data();
        });
    }

    changeStatusAjax(id, new_status, interviewEmail = false) {
        $('.jobsP_loader').removeClass('hide');
        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: {
                action: jobsP.ajax_key,
                command: 'change_job_status',
                id: id,
                status: new_status,
                interview_email: interviewEmail
            },
            error: function (e1, e2, e3) {
                SATechJobsError(e3);
            }
        }).always(function () {
            $('.jobsP_loader').addClass('hide');
        }).done(function (data) {
            if (data.substr(0, 2) === 'OK') {
                AR.load_data();
            } else {
                SATechJobsError(data);
            }
        });
    }

    changeCategory(e) {
        e.preventDefault();

        Category = $(this).val();
        if (Category == '') {
            Url.updateSearchParam('category');
        } else {
            Url.updateSearchParam('category', Category);
        }

        Page = 1;
        AR.load_data();
    }

    restoreSingleApp(e) {
        e.preventDefault();

        let id = $(this).attr('data-id');
        let Loader = $('.jobsP_loader');
        Loader.removeClass('hide');
        $.ajax({
            url: ajaxurl,
            data: {action: jobsP.ajax_key, command: 'restore_single_app', id: id},
            method: 'POST',
            error: function (e1, e2, e3) {
                Loader.addClass('hide');
                SATechJobsError(e3);
            }
        }).done(function (data) {
            Loader.addClass('hide');
            if (data.substr(0, 2) === 'OK') {
                AR.load_data();
            } else {
                SATechJobsError(data);
            }
        });
    }

    delSingleApp(e) {
        e.preventDefault();

        if (!confirm("Are you sure you want to delete this job application?")) return false;

        let id = $(this).attr('data-id');
        let Loader = $('.jobsP_loader');
        Loader.removeClass('hide');
        $.ajax({
            url: ajaxurl,
            data: {action: jobsP.ajax_key, command: 'delete_single_app', id: id},
            method: 'POST',
            error: function (e1, e2, e3) {
                Loader.addClass('hide');
                SATechJobsError(e3);
            }
        }).done(function (data) {
            Loader.addClass('hide');
            if (data.substr(0, 2) === 'OK') {
                AR.load_data();
            } else {
                SATechJobsError(data);
            }
        });
    }

    addNote(e) {
        e.preventDefault();

        let new_comment = $.trim($('#new_note_text').val());
        if (new_comment) {
            let Loader = $('.jobsP_loader');
            let id = $(this).attr('data-id');
            Loader.removeClass('hide');
            $.ajax({
                url: ajaxurl,
                data: {action: jobsP.ajax_key, command: 'saveNote', comment: new_comment, application_id: id},
                method: 'POST',
                error: function (e1, e2, e3) {
                    Loader.addClass('hide');
                    SATechJobsError(e3);
                }
            }).done(function (data) {
                Loader.addClass('hide');
                $('#new_note_text').val('');
                if (data.substr(0, 2) === 'OK') {
                    $('#notes_panel').prepend(tmpl('notes_row_tmpl', {
                        id: data.substr(2),
                        comment: new_comment
                    }));
                } else {
                    SATechJobsError(data);
                }
            });
        } else {
            $('#new_comment').trigger('focus');
            SATechJobsError('Please type new comment.');
        }
    }

    delNote(e) {
        e.preventDefault();

        //if (!confirm('Are you sure you want to delete this comment?')) return false;

        let id = $(this).attr('data-id');
        let Loader = $('.jobsP_loader');
        let obj = $(this);
        Loader.removeClass('hide');
        $.ajax({
            url: ajaxurl,
            data: {action: jobsP.ajax_key, command: 'delNote', id: id},
            method: 'POST',
            error: function (e1, e2, e3) {
                Loader.addClass('hide');
                SATechJobsError(e3);
            }
        }).done(function (data) {
            Loader.addClass('hide');
            if (data.substr(0, 2) === 'OK') {
                obj.closest('a').remove();
            } else {
                SATechJobsError(data);
            }
        });
    }

    openEmail(e) {
        e.preventDefault();

        let id = $(this).attr('data-id');
        let Loader = $('.jobsP_loader');
        Loader.removeClass('hide');

        $.ajax({
            url: ajaxurl,
            data: {action: jobsP.ajax_key, command: 'openEmail', id: id},
            method: 'POST',
            error: function (e1, e2, e3) {
                Loader.addClass('hide');
                SATechJobsError(e3);
            }
        }).done(function (data) {
            Loader.addClass('hide');
            if (data.email) {
                data.id = id;
                $.fancybox.open(tmpl('email_tmpl', data), {
                    caption: 'Send email',
                    type: 'html',
                    touch: false,
                    titleShow: true,
                    titlePosition: 'over',
                    modal: true,
                    minWidth: '90%',
                    minHeight: '90%',
                    afterShow: function () {
                        $('#email_form [name=email]').prop('readonly', true);
                        $('#email_form [name=subject]').trigger('focus');
                        $('#email_form [name=content]').richText({
                            heading: false,
                            useParagraph: true
                        });
                    }
                });
            } else if (data.error) {
                SATechJobsError(data.error)
            } else {
                SATechJobsError('No applicant email found.');
            }
        });
    }

    sendEmail(e) {
        e.preventDefault();

        let Loader = $('.jobsP_loader');
        let id = $(this).attr('id');
        $('#send_email_btn, #email_btn2').trigger('blur');

        Loader.removeClass('hide');
        let dataForm = $('#' + id).serializeArray();
        if (changeStatuswithEmail) {
            dataForm.push({
                name: 'new_status',
                value: changeStatuswithEmail.new_status
            });
        }

        $.ajax({
            url: ajaxurl,
            data: dataForm,
            method: 'POST',
            error: function (e1, e2, e3) {
                Loader.addClass('hide');
                SATechJobsError(e3);
            }
        }).done(function (data) {
            Loader.addClass('hide');
            changeStatuswithEmail = null;
            AR.load_data();
            if (data.substr(0, 2) === 'OK') {
                $.fancybox.close();
                SATechJobsSuccess('Email sent successfully!');
            } else {
                SATechJobsError(data);
            }
        });
    }

    sendEmail2(e) {
        e.preventDefault();

        console.error($('#email_form2').serializeArray());
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

        AR.load_data();
    }

    search(e) {
        e.preventDefault();

        Page = 1;
        let val = $(this).find('[name=s]').val();
        Url.updateSearchParam('s', val);

        AR.load_data();
    }

    searchKey(e) {
        if (e.key == 'Enter') {
            e.preventDefault();
            let val = $(this).val();
            Url.updateSearchParam('s', val);

            AR.load_data();
        }
    }

    clearSearch() {
        let val = $.trim($(this).val());
        Page = 1;

        if (val === '') {
            Url.updateSearchParam('s');
            AR.load_data();
        }
    }

    cancelButton(e) {
        e.preventDefault();
        $.fancybox.close();
    }

    openCloseLog(e) {
        e.preventDefault();
        let This = $(this);

        if (This.next('.message-body').is(':visible')) {
            This.next('.message-body').addClass('hide');
            This.find('i').removeClass('fa-caret-up').addClass('fa-caret-down');
        } else {
            $('.log_article .message-body').each(function () {
                if ($(this).is(':visible')) {
                    $(this).addClass('hide');
                }
            });
            $('.log_article .message-header i').removeClass('fa-caret-up').addClass('fa-caret-down');

            This.next('.message-body').removeClass('hide');
            This.find('i').removeClass('fa-caret-down').addClass('fa-caret-up');
        }
    }
}

var AR = new Admin_received();