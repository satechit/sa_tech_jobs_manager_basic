<?php

use SAJobsF\Jobs\ReceivedJobs;


$Apps = new ReceivedJobs();
try {
	$cats = $Apps->get_job_categories();
} catch ( Exception $e ) {
	$cats = [];
}
$array = wp_upload_dir();
?>
	<script>
		var Status = <?php echo json_encode( ReceivedJobs::Status ) ?>;
	</script>
	<div class="wrap">
		<h1 class="wp-heading-inline">Job Applications</h1>
		<hr class="wp-header-end">

		<ul class='subsubsub'>
			<li class='all'>
				<a data-field="clear_all" data-value="" href='?page=<?php echo $_REQUEST['page'] ?>' class="current filter_link">
					All <span class="count all">(0)</span>
				</a> |
			</li>
			<?php foreach($Apps::Status as $key=>$status) { ?>
			<li class='status<?php echo $status ?>'>
				<a class="filter_link" data-field="status" data-value="<?php echo $status ?>" href='?page=<?php echo $_REQUEST['page'] ?>&status=<?php echo $status ?>'>
					<?php echo $key == 'Interview' ? 'Interview Scheduled' : $key ?>
					<span class="count status_<?php echo $status ?>">(0)</span>
				</a> |
			</li>
			<?php } ?>
			<li class='status<?php echo $status ?>'>
				<a class="filter_link" data-field="deleted" data-value="1" href='?page=<?php echo $_REQUEST['page'] ?>&deleted=1'>
					Trash
					<span class="count deleted">(0)</span>
				</a> |
			</li>
		</ul>

		<form class="search-form search-plugins is-hidden-mobile" method="get" id="search_form">
			<p class="search-box">
				<label class="screen-reader-text" for="search">Search in job applications:</label>
				<input type="search" id="search" class="wp-filter-search" name="s" value="<?php echo $_GET['s'] ?? '' ?>" placeholder="Search in job applications..." />
				<input type="submit" id="search-submit" class="button hide-if-js" value="Search Installed Plugins" />
			</p>
		</form>

		<form action="" id="bulk-action-form" onsubmit="return false;">
			<input type="hidden" name="action" value="<?php echo self::AjaxKey ?>">
			<input type="hidden" name="command" value="received_bulk_action">
			<div class="tablenav top">
				<div class="alignleft actions bulkactions">
					<label for="bulk-action-selector-top" class="screen-reader-text">Select bulk action</label>
					<select name="bulk-action" id="bulk-action-selector-top">
						<option value="-1">Bulk Actions</option>
						<option value="delete">Delete</option>
						<option value="restore">Restore</option>
					</select>
					<input type="submit" id="doaction" class="button action" value="Apply">
				</div>
				<div class="tablenav-pages one-page">
					<span class="displaying-num">&nbsp;</span>
					<span class="pagination-links">
					<span data-page="1" id="first_page_link" class="tablenav-pages-navspan button disabled" aria-hidden="true">«</span>
					<span data-page="0" id="prev_page_link" class="tablenav-pages-navspan button disabled" aria-hidden="true">‹</span>
					<span class="paging-input">
						<label for="current-page-selector" class="screen-reader-text">Current Page</label>
						<input class="current-page" id="current-page-selector" type="text" name1="paged" value="1" size="1" aria-describedby="table-paging">
						<span class="tablenav-paging-text"> of <span class="total-pages">1</span></span>
					</span>
					<span data-page="0" id="next_page_link" class="tablenav-pages-navspan button disabled" aria-hidden="true">›</span>
					<span data-page="0" id="last_page_link" class="tablenav-pages-navspan button disabled" aria-hidden="true">»</span>
				</span>
				</div>
				<br class="clear">
			</div>

			<h2 class="screen-reader-text">Categories list</h2>

			<input type="search" id="search_text" class="wp-filter-search is-hidden-desktop" name="s" value="<?php echo $_GET['s'] ?? '' ?>" placeholder="Search in job applications..." />

			<table class="wp-list-table widefat plugins" id="received_table">
				<thead>
				<tr>
					<td id="cb" class="manage-column column-cb check-column is-hidden-mobile">
						<label class="screen-reader-text" for="cb-select-all-1">Select All</label><input id="cb-select-all-1" type="checkbox">
					</td>
					<th scope="col" class="manage-column column-primary column-applicant w10"><span class="is-hidden-desktop">Job Applications</span><span class="is-hidden-mobile">ID</span></th>
					<th scope="col" class="manage-column column-primary column-name">Applicant</th>
					<th scope="col" class="manage-column column-primary column-status">Status</th>
					<th scope="col" class="manage-column column-primary column-applied">Applied for</th>
					<th scope="col" class="manage-column column-primary column-applied">Applied on</th>
				</tr>
				</thead>
				<tbody id="the-list">
				<tr>
					<th scope="row" class="check-column is-hidden-mobile"></th>
					<td class="text-center" colspan="6">
						<i class="fa fa-spin fa-spinner"></i> Loading ....
					</td>
				</tr>
				</tbody>
			</table>
		</form>
	</div>
<?php include_once( __DIR__ . "/../../tmpl/admin/received.tpl" ); ?>