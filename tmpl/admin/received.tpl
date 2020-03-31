<script type="text/x-tmpl" id="table_tmpl">
    {% if (o.rows.length == 0) { %}
        <tr>
            <td colspan="7" class="center"><i>-- No application found --</i></td>
        </tr>
    {% } else { %}
        {% for (x in o.rows) { row = o.rows[x]; %}
        {% is_attached = satechjobs_count(row.cv) + satechjobs_count(row.other_files) > 0; %}
        <tr class="{%=(row.status==Status['New'])?'active':'inactive'%}" data-slug="" data-plugin="" data-id="{%=row.id%}">
            <th scope="row" class="check-column is-hidden-mobile">
                <input type="checkbox" class="checkbox" name="checked[]" value="{%=row.id%}">
            </th>
            <td>
                <span class="tag {%=row.status==0?'is-link':row.status==1?'':row.status==11?'is-danger':row.status==10?'is-primary':row.status==12?'is-info':'is-success'%} is-hidden-desktop is-pulled-right">
                    {%=row.status_text=='Interview'?'Interview Scheduled':row.status_text%}
                </span>
                <span class="is-hidden-desktop">ID: </span> {%=row.id%}
            </td>
            <td class="plugin-title">
                <div class="image is-64x64 is-hidden-mobile display-contents">
                    <img src="{%=row.avatar%}" class="is-rounded p0 mr15">
                </div>
                {%#is_attached?"<i title='This application has attachments' class='fa fa-paperclip'></i> ":""%}
                <b>
                    {% if (row.deleted==1) { %}
                        {%=row.applicant_name%}</b><br>
                    {% } else { %}
                        <a href="javascript:;" data-id="{%=row.id%}" class="view_button">{%=row.applicant_name%}</a></b><br>
                    {% } %}
                (<i>{%=row.applicant_email%}</i>)<br>
                <i class="fa fa-mobile"></i> <a href="tel:{%=row.applicant_contact%}">{%=row.applicant_contact%}</a>

                <div class="row-actions is-hidden-mobile mt10">
                    {% if (row.deleted==1) { %}
                        <a data-id="{%=row.id%}" class="restore_single_app mygreen" href="javascript:;">Restore</a>
                    {% } else { %}
                        <a href="javascript:;" data-id="{%=row.id%}" class="view_button">View</a>
                        |
                        <a data-id="{%=row.id%}" class="del_single_app myred" href="javascript:;">Trash</a>
                        |
                        <a href="javascript:;" class="email_to" data-id="{%=row.id%}">Send Email</a>
                    {% } %}
                </div>
            </td>
            <td class="is-hidden-mobile">
                <span class="tag {%=row.status==0?'is-link':row.status==1?'':row.status==11?'is-danger':row.status==10?'is-primary':row.status==12?'is-info':'is-success'%}">
                    {%=row.status_text=='Interview'?'Interview Scheduled':row.status_text%}
                </span>
            </td>
            <td>
                <b>{%=row.job_category_name%}</b><br>
                (<a title="Apply job link" href="{%=row.apply_job_link%}" target="_blank"><i>{%=row.job_title%}</i></a>)
            </td>
            <td class="is-hidden-mobile">{%=row.received_time_formatted%}<br>(<i>{%=row.received_time_ago%} ago</i>)
                <div class="is-pulled-right is-hidden-desktop">
                    {% if (row.deleted==1) { %}
                        <a data-id="{%=row.id%}" class="restore_single_app mygreen" href="javascript:;">Restore</a>
                    {% } else { %}
                        <a data-id="{%=row.id%}" class="del_single_app myred" href="javascript:;">Trash</a>
                    {% } %}
                </div>
            </td>
        </tr>
        {% } %}
    {% } %}
</script>
<script type="text/x-tmpl" id="receive_job_tmpl">
{% row = o.row; %}
<div>
    <div class="p10">
        <div class="is-pulled-right">
            <button type="button" class="delete is-large cancel_button"></button>
        </div>
        <h2 class="fancybox_heading mt10">
            {%=row.job_title%} <small>({%=row.job_category_name%})</small>
        </h2>
        <div>
            Submitted on {%=row.received_time2%} (<i>{%=row.received_time_ago%}</i> ago) from IP {%=row.received_ip%}
        </div>

        <div class="columns mt15">
            <div class="column is-9">
                <div class="tabs is-boxed">
                  <ul>
                    <li data-target="tab1" class="is-active">
                        <a>
                            <span class="icon is-small is-hidden-mobile"><i class="fa fa-user-o" aria-hidden="true"></i></span>
                            <span class="is-hidden-mobile">Application</span>
                            <span class="is-hidden-tablet">App</span>
                        </a>
                    </li>
                    <li data-target="tabJobAd" class="">
                        <a>
                            <span class="icon is-small is-hidden-mobile"><i class="fa fa-address-card-o" aria-hidden="true"></i></span>
                            <span class="">Job Ad</span>
                        </a>
                    </li>
                    <li data-target="tab3" id="emailTabLink">
                        <a>
                            <span class="icon is-small is-hidden-mobile"><i class="fa fa-envelope-o" aria-hidden="true"></i></span>
                            <span>Email</span>
                        </a>
                    </li>
                    {% if ( typeof row.cv.url_real != 'undefined' ) { %}
                    <li data-target="tab2">
                        <a>
                            <span class="icon is-small is-hidden-mobile"><i class="fa fa-file-o" aria-hidden="true"></i></span>
                            <span>Resume</span>
                        </a>
                    </li>
                    {% } %}
                  </ul>
                </div>
                <div class="tab-content">
                    <div id="tab3">
                        <div class="notification is-warning" id="email_notification_message">
                            This featuer is available only in Premium version.
                            <p style="padding-top:20px;">
                            Click <a href="https://codecanyon.net/item/sa-tech-jobs-manager-for-wordpress/25987776">here</a> to buy premium version
                            </p>
                        </div>
                    </div>
                    <div id="tabJobAd">
                        <div class="card">
                            <div class="card-content">
                                <table class="table is-striped is-bordered">
                                    <thead>
                                        <tr>
                                            <th colspan="2">
                                                Job Ad Info
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="is-hidden-mobile w220">Job Title:</td>
                                            <td><b class="is-hidden-desktop">Job Title:<br></b> {%=row.job_title%}</td>
                                        </tr>
                                        <tr>
                                            <td class="is-hidden-mobile">Job Category:</td>
                                            <td><b class="is-hidden-desktop">Job Category:<br></b> {%=row.job_category_name%}</td>
                                        </tr>
                                        {% if (row.job_row) { %}
                                            <tr>
                                                <td class="is-hidden-mobile">Location:</td>
                                                <td><b class="is-hidden-desktop">Location:<br></b> {%=row.job_row.location%}</td>
                                            </tr>
                                            <tr>
                                                <td class="is-hidden-mobile">Job Status:</td>
                                                <td><b class="is-hidden-desktop">Job Status:<br></b> {%#row.job_row.is_active==1?'<span class="tag is-success">Active</span>':'<span class="tag is-danger">InActive</span>'%}</td>
                                            </tr>
                                            <tr>
                                                <td class="is-hidden-mobile">Views:</td>
                                                <td><b class="is-hidden-desktop">Views:<br></b> {%=row.job_row.views%}</td>
                                            </tr>
                                            <tr>
                                                <td class="is-hidden-mobile">Applications:</td>
                                                <td><b class="is-hidden-desktop">Applications:<br></b> {%=row.job_applictions_counter%}</td>
                                            </tr>
                                            <tr>
                                                <td class="is-hidden-mobile">Posted Date:</td>
                                                <td><b class="is-hidden-desktop">Posted Date:<br></b> {%=row.job_row.added_date%}</td>
                                            </tr>
                                            <tr>
                                                <td class="is-hidden-mobile">Last Submitted Application:</td>
                                                <td><b class="is-hidden-desktop">Last Submitted Application:<br></b> {%=row.last_submitted_date?row.last_submitted_date:'(no date)'%}</td>
                                            </tr>
                                            <tr>
                                                <td class="is-hidden-mobile">Expiry Date:</td>
                                                <td><b class="is-hidden-desktop">Expiry Date:<br></b> {%=row.job_row.expiry_date%}</td>
                                            </tr>
                                        {% } %}
                                    </tbody>
                                </table>
                                <a class="button is-info is-small blur_out" href="admin.php?page=SAjobsF_jobs_add_page&edit={%=row.ad_id%}">
                                    <i class="fa fa-edit"></i> Edit Job
                                </a>
                            </div>
                        </div>
                    </div>
                    <div id="tab1" class="active">
                        <div class="card">
                            <div class="card-content">
                                <div class="columns">
                                    <div class="column is-2 is-hidden-mobile">
                                        <img src="{%=row.avatar%}" alt="Image" class="w100p">
                                    </div>
                                    <div class="column is-10">
                                        <div class="content">
                                            <table class="table is-striped is-bordered">
                                                <thead><tr><th colspan="2">Applicant</th></tr></thead>
                                                <tbody>
                                                    <tr>
                                                        <td class="is-hidden-mobile w200">Name:</td>
                                                        <td><b>{%=row.applicant_name%}</b></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="is-hidden-mobile">Email:</td>
                                                        <td>{%=row.applicant_email%}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="is-hidden-mobile">Contact:</td>
                                                        <td><a href="tel:{%=row.applicant_contact%}">{%=row.applicant_contact%}</a></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="is-hidden-mobile">Application ID:</td>
                                                        <td><span class="is-hidden-desktop">Application ID:</span> {%=row.id%}</td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="2"><b>Cover Letter:</b><br>
                                                            {%#satechjobs_nl2br(row.applicant_message)%}
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>

                                            <p>
                                                {% if ( row.other_files.length > 0 ) { %}
                                                    {% for(index in row.other_files) { %}
                                                        <div class="display-inline-block">
                                                            <div class="buttons has-addons mt0">
                                                                <a class="button is-primary blur_out" download title="Download" href="{%=row.other_files[index].url%}" target="_blank"><i class="fa fa-download" aria-hidden="true"></i></a>
                                                                <a title="{%=row.other_files[index].name%}" class="button is-link file_link" href="{%=row.other_files[index].url%}" target="_blank">{%=row.other_files[index].name%}</a>
                                                            </div>
                                                        </div>
                                                    {% } %}
                                                {% } %}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="tab2">
                        {% if ( typeof row.cv.url_real != 'undefined' ) { %}
                            <div class="text-center mb5">
                                <a class="button is-primary file_link2 blur_out" download title="Download" href="{%=row.cv.url_real%}" target="_blank"><i class="fa fa-download" aria-hidden="true"></i> Download Resume</a>
                            </div>
                            <img src="{%=row.cv.url_real%}" class="w100p">
                        {% } %}
                    </div>
                </div>

            </div>
            <div class="column is-3">
                <article class="message is-small {%=row.status==0?'is-link':row.status==1?'':row.status==11?'is-danger':row.status==10?'is-primary':row.status==12?'is-info':'is-success'%}">
                  <div class="message-header">
                    <p>Application Status: <span class="fsi15">{%=row.status_text%}</span></p>
                  </div>
                  <div class="message-body">
                    Submitted: <b>{%=row.received_time_ago%} ago</b>
                  </div>
                </article>

            </div>
        </div>
    </div>
</div>
</script>
<script type="text/x-tmpl" id="notes_row_tmpl">
    <a class="panel-block is-primary">
        <span class="panel-icon"><button type="button" data-id="{%=o.id%}" class="delete is-small is-danger del_note_btn" aria-label="delete"></button></span>
        {%=o.comment%}
    </a>
</script>
<script type="text/x-tmpl" id="email_tmpl">
    <form id="email_form">
        <input type="hidden" name="application_id" value="{%=o.id%}">
        <a class="delete is-medium is-pulled-right cancel_button"></a>
        <input type="hidden" name="command" value="send_email">
        <input type="hidden" name="action" value="<?php echo self::AjaxKey ?>">

        <div class="field">
            <label class="label">Email address:</label>
            <div class="control">
                <input type="email" name="email" placeholder="Valid email address" value="{%=o.email%}" class="input" required="required">
            </div>
        </div>

        <div class="field">
            <label class="label">Email subject:</label>
            <div class="control">
                <input type="text" name="subject" placeholder="Email subject" class="input" required="required">
            </div>
        </div>

        <textarea name="content" class="textarea" rows="10" placeholder="Email message" required="required"></textarea>

        <div class="field is-grouped is-pulled-right mt10">
            <div class="control">
                <button type="button" class="button is-link is-light cancel_button">Cancel</button>
            </div>
            <div class="control">
                <button class="button is-link" id="send_email_btn">Send Email</button>
            </div>
        </div>
    </form>
</script>