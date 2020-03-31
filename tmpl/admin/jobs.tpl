<script type="text/x-tmpl" id="jobs_blank_tmpl">
	<tr>
        <td colspan="10" class="text-center"><i>-- No job found --</i></td>
	</tr>

</script>
<script type="text/x-tmpl" id="jobs_tmpl">
	{% for (x in o) { row = o[x]; %}
	<tr class="{%=row.is_active==1 && row.deleted!=1?'active':'inactive'%}" data-slug="" data-plugin="">
		<th scope="row" class="check-column is-hidden-mobile">
			<label class="screen-reader-text" for="checkbox_48e7f3675d3325447ce18fa7b69f57e8">Select Classic Editor</label>
			<input type="checkbox" class="checkbox" name="checked[]" value="{%=row.id%}">
		</th>
		<td class="job plugin-title expired{%=row.expired%}">
            {% if (Object.keys(row.files).length>0) { %}<i class="fa fa-paperclip"></i>{% } %}
            {% if (row.deleted == 1) { %}
                <b>{%=row.title%}</b>
            {% } else { %}
                <b><a href="?page=SAjobsF_jobs_add_page&edit={%=row.id%}" class="edit_link2">{%=row.title%}</a></b>
            {% } %}
            <br>ID: {%=row.id%}

            <div class="row-actions is-hidden-mobile mt10">
				{% if (row.deleted == 1) { %}
				    <a data-id="{%=row.id%}" class="restore_link mygreen" href="#">Restore</a>
				{% } else { %}
                    <a href="?page=SAjobsF_jobs_add_page&edit={%=row.id%}" class="edit_link2">Edit</a>
                    |
                    {% if (row.is_active==1) { %}
                        <a data-id="{%=row.id%}" href="javascript:;" class="activate_link myred" data-value="0">Deactivate</a>
                    {% } else { %}
                        <a data-id="{%=row.id%}" href="javascript:;" class="activate_link" data-value="1">Activate</a>
                    {% } %}
                    |
					<a data-id="{%=row.id%}" class="del_link myred" href="#">Trash</a>
                {% } %}
			</div>
		</td>
		<td class="is-hidden-mobile">
	        <a target="_blank" title="View apply job page" href="{%=row.apply_link%}">{%=row.category%}</a>
            {% if (row.job_type!='') { %}
                <br>(<i>{%=row.job_type%}</i>)
            {% } else { %}
                <br>-
            {% } %}
        </td>
        <td class="is-hidden-mobile">
            {% if (row.salary != '') { %}
			    {%=row.currency_symbol%}{%=row.salary%}<br>
			{% } %}
			{%=row.salary_type%}

			{% if (row.salary == '' && row.salary_type == '') { %}<i>Not specified</i>{% } %}
        </td>
        <td class="is-hidden-mobile">{%=row.expiry_date_formatted%}
            {%#row.expired==1?'<br><span class="expired">Expired</span>':''%}
        </td>
        <td class="is-hidden-mobile">{%=row.views%}</td>
        <td class="is-hidden-mobile"><a href="?page=SAjobsF_jobs_received&ad_id={%=row.id%}">{%=row.applications%}</a></td>
        <td class="is-hidden-mobile">{%=row.is_active==1?'Yes':'No'%}</td>
        <td class="is-hidden-mobile">{%=row.added_time_formatted%}</td>
        <td>
            <div class="is-pulled-right is-hidden-desktop">
                {% if (row.deleted == 1) { %}
				    <a data-id="{%=row.id%}" class="restore_link mygreen" href="#">Restore</a>
				{% } else { %}
					<a data-id="{%=row.id%}" class="del_link myred" href="#">Trash</a>
                {% } %}
            </div>
            {% if (row.location!='') { %}<i class="fa fa-map-marker" aria-hidden="true"></i> {%=row.location%}{% } else { %}-{% } %}
        </td>
	</tr>
	{% } %}

</script>
<script type="text/x-tmpl" id="logs_tmpl">
    <i class="fa fa-times fa-2x fancybox_close" onclick="$.fancybox.close();"></i>
    <b class="fancybox_title">Job Ad Logs</b>
    <table class="table minw350">
    <tbody>
    {% for(i in o) { log = o[i]; %}
        <tr>
            <td>
                <div class="log_author">
                    <i class="fa fa-user" aria-hidden="true"></i> {%=log.sent_by_name%}<br>
                    <i class="fa fa-clock-o" aria-hidden="true"></i> {%=log.datetime_formatted%}<br>
                    IP: {%=log.ip%}
                </div>
                {%#log.log%}
            </td>
        </tr>
    {% } %}
    </tbody>
    </table>
</script>