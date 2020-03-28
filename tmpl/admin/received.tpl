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
            <td class="is-hidden-mobile">{%=row.received_time_formatted%}<br>(<i>{%=row.received_time_ago%} ago</i>)</td>
            <td>
                <div class="is-pulled-right is-hidden-desktop">
                    {% if (row.deleted==1) { %}
                        <a data-id="{%=row.id%}" class="restore_single_app mygreen" href="javascript:;">Restore</a>
                    {% } else { %}
                        <a data-id="{%=row.id%}" class="del_single_app myred" href="javascript:;">Trash</a>
                    {% } %}
                </div>

                <!--div class="starrr" data-rating="{%=row.rating%}" data-id="{%=row.id%}"></div-->
                <select class="barrating" data-id="{%=row.id%}" data-text="{%=row.id%}">
                    <option value=""></option>
                    {% for (i=1; i<=5; i++) { %}
                        <option {%=i==row.rating?'selected':''%} value="{%=i%}">{%=i%}</option>
                    {% } %}
                </select>
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
                            <span class=""><?php esc_attr_e('Job Ad', self::DOMAIN) ?></span>
                        </a>
                    </li>
                    <li data-target="tab3" id="emailTabLink">
                        <a>
                            <span class="icon is-small is-hidden-mobile"><i class="fa fa-envelope-o" aria-hidden="true"></i></span>
                            <span><?php esc_attr_e('Email', self::DOMAIN) ?></span>
                        </a>
                    </li>
                    {% if ( typeof row.cv.url_real != 'undefined' ) { %}
                    <li data-target="tab2">
                        <a>
                            <span class="icon is-small is-hidden-mobile"><i class="fa fa-file-o" aria-hidden="true"></i></span>
                            <span><?php esc_attr_e('Resume', self::DOMAIN) ?></span>
                        </a>
                    </li>
                    {% } %}
                    <li data-target="tab_mail_logs">
                        <a>
                            <span class="icon is-small is-hidden-mobile"><i class="fa fa-files-o" aria-hidden="true"></i></span>
                            <span class="is-hidden-mobile"><?php esc_attr_e('Application Logs', self::DOMAIN) ?></span>
                            <span class="is-hidden-desktop"><?php esc_attr_e('Logs', self::DOMAIN) ?></span>
                        </a>
                    </li>
                  </ul>
                </div>
                <div class="tab-content">
                    <div id="tab_mail_logs">
                        {% for(i in o.logs) { log = o.logs[i]; %}
                        <article class="message is-light log_article">
                            <div class="message-header">
                                <p class="log_title">{%=log.log_title%}</p>
                                <i class="fa fa-caret-down" aria-hidden="true"></i>
                            </div>
                            <div class="message-body is-clearfix hide">
                                <div class="is-pulled-right block is-hidden-mobile">
                                    <i class="fa fa-user" aria-hidden="true"></i> {%=log.sent_by_name%}<br>
                                    <i class="fa fa-clock-o" aria-hidden="true"></i> {%=log.datetime_formatted%}<br>
                                    IP: {%=log.ip%}
                                </div>
                                {%#log.log%}
                            </div>
                        </article>
                        {% } %}
                    </div>
                    <div id="tab3">
                        <div class="notification is-info hide" id="email_notification_message">
                            Application status will be changed with this email.
                        </div>
                        <form id="email_form2">
                            <input type="hidden" name="application_id" value="{%=row.id%}">
                            <input type="hidden" name="command" value="send_email">
                            <input type="hidden" name="action" value="<?php echo self::AjaxKey ?>">

                            <div class="field">
                                <label class="label" for="load_content_from_templates">
                                    <?php esc_attr_e('Select email template', self::DOMAIN) ?>
                                    <i class="i">(Only those templates will appear which are not attached to any event)</i>
                                </label>
                                <div class="control">
                                    <div class="select is-fullwidth">
                                        <select id="load_content_from_templates" class="noarrow" data-id="{%=o.row.id%}">
                                            <option value="">(select email template)</option>
                                            {% for(index in o.templates) { template = o.templates[index]; %}
                                                <option value="{%=template.id%}">{%=template.subject%}</option>
                                            {% } %}
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <label class="label" for="email_email">Email recipient</label>
                            <div class="field has-addons">
                                <div class="control is-expanded has-icons-left has-icons-right">
                                    <input class="input" type="email" id="email_email" name="email" placeholder="Text input" value="{%=row.applicant_email%}" required="required" readonly>
                                    <span class="icon is-small is-left">
                                      <i class="fa fa-at"></i>
                                    </span>
                                </div>
                                <div class="control">
                                    <button class="button button2 is-primary" id="email_btn2">Send Email</button>
                                </div>
                            </div>
                            <div class="field lh2" id="fields"></div>
                            <div class="field">
                                <label class="label" for="email_subject">Email subject</label>
                                <div class="control">
                                    <input class="input" type="text" placeholder="Email subject" name="subject" id="email_subject" required>
                                </div>
                            </div>
                            <div class="field">
                                <div class="control">
                                    <textarea class="textarea" name="content" id="email_message" required></textarea>
                                </div>
                            </div>
                        </form>
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
                                            <td class="is-hidden-mobile w220"><?php esc_attr_e('Job Title', self::DOMAIN) ?>:</td>
                                            <td><b class="is-hidden-desktop"><?php esc_attr_e('Job Title', self::DOMAIN) ?>:<br></b> {%=row.job_title%}</td>
                                        </tr>
                                        <tr>
                                            <td class="is-hidden-mobile"><?php esc_attr_e('Job Category', self::DOMAIN) ?>:</td>
                                            <td><b class="is-hidden-desktop"><?php esc_attr_e('Job Category', self::DOMAIN) ?>:<br></b> {%=row.job_category_name%}</td>
                                        </tr>
                                        {% if (row.job_row) { %}
                                            <tr>
                                                <td class="is-hidden-mobile"><?php esc_attr_e('Location', self::DOMAIN) ?>:</td>
                                                <td><b class="is-hidden-desktop"><?php esc_attr_e('Location', self::DOMAIN) ?>:<br></b> {%=row.job_row.location%}</td>
                                            </tr>
                                            <tr>
                                                <td class="is-hidden-mobile"><?php esc_attr_e('Job Status', self::DOMAIN) ?>:</td>
                                                <td><b class="is-hidden-desktop"><?php esc_attr_e('Job Status', self::DOMAIN) ?>:<br></b> {%#row.job_row.is_active==1?'<span class="tag is-success">Active</span>':'<span class="tag is-danger">InActive</span>'%}</td>
                                            </tr>
                                            <tr>
                                                <td class="is-hidden-mobile"><?php esc_attr_e('Views', self::DOMAIN) ?>:</td>
                                                <td><b class="is-hidden-desktop"><?php esc_attr_e('Views', self::DOMAIN) ?>:<br></b> {%=row.job_row.views%}</td>
                                            </tr>
                                            <tr>
                                                <td class="is-hidden-mobile"><?php esc_attr_e('Applications', self::DOMAIN) ?>:</td>
                                                <td><b class="is-hidden-desktop"><?php esc_attr_e('Applications', self::DOMAIN) ?>:<br></b> {%=row.job_applictions_counter%}</td>
                                            </tr>
                                            <tr>
                                                <td class="is-hidden-mobile"><?php esc_attr_e('Posted Date', self::DOMAIN) ?>:</td>
                                                <td><b class="is-hidden-desktop"><?php esc_attr_e('Posted Date', self::DOMAIN) ?>:<br></b> {%=row.job_row.added_date%}</td>
                                            </tr>
                                            <tr>
                                                <td class="is-hidden-mobile"><?php esc_attr_e('Last Submitted Application', self::DOMAIN) ?>:</td>
                                                <td><b class="is-hidden-desktop"><?php esc_attr_e('Last Submitted Application', self::DOMAIN) ?>:<br></b> {%=row.last_submitted_date?row.last_submitted_date:'(no date)'%}</td>
                                            </tr>
                                            <tr>
                                                <td class="is-hidden-mobile"><?php esc_attr_e('Expiry Date', self::DOMAIN) ?>:</td>
                                                <td><b class="is-hidden-desktop"><?php esc_attr_e('Expiry Date', self::DOMAIN) ?>:<br></b> {%=row.job_row.expiry_date%}</td>
                                            </tr>
                                        {% } %}
                                    </tbody>
                                </table>
                                <a class="button is-info is-small blur_out" href="admin.php?page=SAjobsF_jobs_add_page&edit={%=row.ad_id%}">
                                    <i class="fa fa-edit"></i> <?php esc_attr_e('Edit Job', self::DOMAIN) ?>
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
                                                        <td class="is-hidden-mobile"><?php esc_attr_e('Contact', self::DOMAIN) ?>:</td>
                                                        <td><a href="tel:{%=row.applicant_contact%}">{%=row.applicant_contact%}</a></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="is-hidden-mobile"><?php esc_attr_e('Application ID', self::DOMAIN) ?>:</td>
                                                        <td><span class="is-hidden-desktop"><?php esc_attr_e('Application ID', self::DOMAIN) ?>:</span> {%=row.id%}</td>
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
                            {% if ( is_image_file(row.cv.url_real) ) { %}
                                <div class="text-center mb5">
                                    <a class="button is-primary file_link2 blur_out" download title="Download" href="{%=row.cv.url_real%}" target="_blank"><i class="fa fa-download" aria-hidden="true"></i> <?php esc_attr_e('Download Resume', self::DOMAIN) ?></a>
                                </div>
                                <img src="{%=row.cv.url_real%}" class="w100p">
                            {% } else {
                                //url = "https://view.officeapps.live.com/op/view.aspx?src=" + encodeURIComponent(row.cv.url_real);
                                //url = "https://docs.google.com/viewer?embedded=true&hl=en&pid=explorer&efh=false&a=v&chrome=false&url=" + encodeURIComponent(row.cv.url_real);
                                url = "https://docs.google.com/viewerng/viewer?url=" + encodeURIComponent(row.cv.url_real) + "&embedded=true";
                                 %}
                                <div class="text-center mb5">
                                    <a class="button is-primary file_link2 blur_out" download title="Download" href="{%=row.cv.url_real%}" target="_blank"><i class="fa fa-download" aria-hidden="true"></i> <?php esc_attr_e('Download Resume', self::DOMAIN) ?></a>
                                </div>
                                <iframe id="doc_iframe" src="{%=url%}" frameborder="0"></iframe>
                                <!--object width="100%" data="{%=url%}" id="doc_object">
                                    <embed src="{%=url%}">

                                    </embed>
                                </object-->
                            {% } %}
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

                <article class="message is-small">
                  <div class="message-header">
                    <p>Actions</p>
                  </div>
                  <div class="message-body">
                    <!--div class="starrr" data-rating="{%=row.rating%}" data-id="{%=row.id%}"></div-->
                        <select class="barrating" data-id="{%=row.id%}" data-text="{%=row.id%}">
                            <option value=""></option>
                            {% for (i=1; i<=5; i++) { %}
                                <option {%=i==row.rating?'selected':''%} value="{%=i%}">{%=i%}</option>
                            {% } %}
                        </select>
                  </div>
                  <div class="message-body">
                    <select class="application_action" data-id="{%=row.id%}">
                    <option value="">(select action)</option>
                    {% for(i in Status) { %}
                        {% if (Status[i] > 1) { string = i.replace('ed', ''); string = (string=='Interview')?'Schedule Interview':string; %}
                        <option value="{%=Status[i]%}">{%=string%}</option>
                        {% } %}
                    {% } %}
                    </select>
                  </div>
                </article>

                {% note = o.notes; %}
                <nav class="panel is-small">
                    <p class="panel-heading">
                        Notes
                    </p>
                    <div class="panel-block">
                        <p class="control has-icons-left">
                            <input class="input is-loading" type="text" placeholder="Add new note" id="new_note_text">
                            <span class="icon is-left"><i class="fa fa-sticky-note-o" aria-hidden="true"></i></span>
                        </p>
                        <button type="button" data-id="{%=row.id%}" id="add_note_btn" class="button is-primary"><i class="fa fa-plus"></i></button>
                    </div>
                    <div id="notes_panel">
                    {% for (i in note) { note_row = note[i]; %}
                        {% include('notes_row_tmpl', note_row); %}
                    {% } %}
                    </div>
                </nav>
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