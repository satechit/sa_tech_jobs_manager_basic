<script type="text/x-tmpl" id="cats_blank_tmpl">
	<tr>
        <th scope="row" class="check-column is-hidden-mobile"></th>
        <td colspan="3" class="text-center"><i>-- <?php esc_attr_e('No job category added yet', 'jobsp-domain') ?> --</i></td>
	</tr>
</script>
<script type="text/x-tmpl" id="cats_tmpl">
	{% for (x in o) { row = o[x]; %}
	<tr class="{%=row.is_active==1?'active':'inactive'%}" data-slug="" data-plugin="">
		<th scope="row" class="check-column is-hidden-mobile">
			<input type="checkbox" class="checkbox" name="checked[]" value="{%=row.id%}">
		</th>
		<td class="category plugin-title">
			<strong>{%=row.category%}</strong>
			<div class="row-actions visible">
				<a data-id="{%=row.id%}" class="edit_link" href="#"><?php esc_attr_e('Edit', 'jobsp-domain') ?></a>
				{% if (row.job_count==0) { %}
				    |
				    <a data-id="{%=row.id%}" class="del_link myred" href="#"><?php esc_attr_e('Delete', 'jobsp-domain') ?></a>
				{% } %}
			</div>
		</td>
		<td class="column-active is_active is-hidden-mobile">{%=row.is_active==1?'Yes':'No'%}</td>
		<td class="is-hidden-mobile"><i>{%=row.job_count%} jobs attached</i></td>
		<td class="is-hidden-desktop"></td>
	</tr>
	{% } %}
</script>