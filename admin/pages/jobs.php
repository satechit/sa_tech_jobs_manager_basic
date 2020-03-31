<?php

use SAJobsF\Jobs\Category;
use SAJobsF\Jobs\Currency;
use SAJobsF\Jobs\Jobs;

$Jobs = new Jobs();

?>
	<div class="wrap">
		<h1 class="wp-heading-inline">Jobs List</h1>
		<a href="?page=SAjobsF_jobs_add_page&add" title="Press Ctrl + M" id="add_job_btn2" class="page-title-action">Add New Job</a>
		<hr class="wp-header-end">

		<h2 class='screen-reader-text'>Filter plugins list</h2>
		<ul class='subsubsub'>
			<li class='all'>
				<a data-field="clear_all" data-value="" href='?page=<?php echo $_REQUEST['page'] ?>' class="current filter_link">
					All <span class="count all">(<?php echo $Jobs->count_job_ads() ?>)</span>
				</a> |
			</li>
			<li class='active'>
				<a class="filter_link" data-field="is_active" data-value="1" href='?page=<?php echo $_REQUEST['page'] ?>&is_active=1'>
					Active <span class="count active">(<?php echo $Jobs->count_job_ads( 'is_active=1' ) ?>)</span>
				</a> |
			</li>
			<li class='inactive'>
				<a data-field="is_active" data-value="0" href='?page=<?php echo $_REQUEST['page'] ?>&is_active=0' class="filter_link">
					Inactive <span class="count inactive">(<?php echo $Jobs->count_job_ads( 'is_active=0' ) ?>)</span>
				</a> |
			</li>
			<li class='expired'>
				<a data-field="expired" data-value="1" href='?page=<?php echo $_REQUEST['page'] ?>&is_active=0' class="filter_link">
					Expired <span class="count expired">(<?php echo $Jobs->count_job_ads( 'expired=1' ) ?>)</span>
				</a> |
			</li>
			<li class='expired'>
				<a data-field="deleted" data-value="1" href='?page=<?php echo $_REQUEST['page'] ?>&is_active=0' class="filter_link">
					Trash <span class="count trash">(<?php echo $Jobs->count_job_ads( 'deleted=1' ) ?>)</span>
				</a>
			</li>
		</ul>
		<form class="search-form search-plugins is-hidden-mobile" method="get" id="search_form">
			<p class="search-box">
				<label class="screen-reader-text" for="search">Search Installed Plugins:</label>
				<input type="search" id="search" class="wp-filter-search" name="s" value="" placeholder="Search in job ads ..." />
				<input type="submit" id="search-submit" class="button hide-if-js" value="Search Installed Plugins" />
			</p>
		</form>

		<form action="" id="bulk-action-form">
			<input type="hidden" name="action" value="<?php echo self::AjaxKey ?>">
			<input type="hidden" name="command" value="bulk-job-ads">
			<div class="tablenav top">
				<div class="alignleft actions bulkactions">
					<label for="bulk-action-selector-top" class="screen-reader-text">Select bulk action</label>
					<select name="bulk-action" id="bulk-action-selector-top">
						<option value="-1">Bulk Actions</option>
						<option value="activate">Activate</option>
						<option value="deactivate">Deactivate</option>
						<option value="delete">Delete</option>
					</select>
					<input type="submit" id="doaction" class="button action" value="Apply">
				</div>
				<div class="tablenav-pages one-page">
					<span class="displaying-num"></span>
					<span class="pagination-links">
					<span class="tablenav-pages-navspan button disabled" aria-hidden="true">«</span>
					<span class="tablenav-pages-navspan button disabled" aria-hidden="true">‹</span>
					<span class="paging-input">
						<label for="current-page-selector" class="screen-reader-text">Current Page</label>
						<input class="current-page" id="current-page-selector" type="text" name="paged" value="1" size="1" aria-describedby="table-paging">
						<span class="tablenav-paging-text"> of <span class="total-pages">1</span></span>
					</span>
					<span class="tablenav-pages-navspan button disabled" aria-hidden="true">›</span>
					<span class="tablenav-pages-navspan button disabled" aria-hidden="true">»</span>
				</span>
				</div>
				<br class="clear">
			</div>
			<h2 class="screen-reader-text">Categories list</h2>

			<input type="search" id="search_text" class="wp-filter-search is-hidden-desktop" name="s" value="" placeholder="Search in job ads..." />

			<table class="wp-list-table widefat plugins" id="jobs_table">
				<thead>
				<tr>
					<td id="cb" class="manage-column column-cb check-column is-hidden-mobile">
						<label class="screen-reader-text" for="cb-select-all-1">Select All</label><input id="cb-select-all-1" type="checkbox">
					</td>
					<th scope="col" class="manage-column column-primary column-id">Job</th>
					<th scope="col" class="manage-column column-cateogry">Category</th>
					<th scope="col" class="manage-column column-salary-type">Salary</th>
					<th scope="col" class="manage-column column-dates">Expiry</th>
					<th scope="col" class="manage-column column-dates">Views</th>
					<th scope="col" class="manage-column column-dates">Apps</th>
					<th scope="col" class="manage-column column-active">Active</th>
					<th scope="col" class="manage-column column-dates">Added</th>
					<th scope="col" class="manage-column column-job-type">Location</th>
				</tr>
				</thead>
				<tbody id="the-list">
				<tr>
					<th scope="row" class="check-column is-hidden-mobile"></th>
					<td class="text-center" colspan="10">
						<i class="fa fa-spin fa-spinner"></i> Loading ....
					</td>
				</tr>
				</tbody>
			</table>
		</form>
	</div>
<?php include_once self::JOBS_P_PATH . "/tmpl/admin/jobs.tpl"; ?>