<?php

use SAJobsF\Jobs\Category;
use SAJobsF\Jobs\Currency;
use SAJobsF\Jobs\Jobs;

$is_new = ( isset( $_GET['add'] ) || ! isset( $_GET['edit'] ) );
$id     = $_GET['edit'] ?? 0;

$Jobs = new Jobs();
$Cats = new Category();

if ( $is_new && $id == 0 ) {
	$row['description']     = $row['title'] = $row['location'] = $row['job_type'] = $row['salary_type'] = $row['last_update_time'] = '';
	$row['currency_code'] = '';
	$row['expiry_date']     = date( 'Y-m-d', strtotime( '+30 days' ) );
	$row['job_category_id'] = 0;
	$row['salary']          = '';
	$row['is_active']       = 1;
} else {
	try {
		$row = $Jobs->get_job( $id );
		if (!isset($row['title'])) wp_redirect('?page=SAjobsF_jobs_management');
	} catch ( Exception $e ) {
		$row = [];
	}
}

$allcats      = $Cats->get_cats( 'order_by=category&order=asc' );
$types        = $Jobs->get_job_types();
$salary_types = $Jobs->get_salary_types();
unset( $_REQUEST['error'] );
unset( $_GET['error'] );

$Currency   = new Currency();
$currencies = $Currency->get_currencies();

?>
<div class="wrap">
	<a href="?page=SAjobsF_jobs_management" class="black"><i class="fa fa-times-circle fa-2x pull-right" aria-hidden="true"></i></a>

	<h1 class="wp-heading-inline"><?php $is_new ? 'Add Job Ad' : 'Edit Job'; ?></h1>
	<hr class="wp-header-end">

	<form name="post" method="post" id="post">
		<input type="hidden" name="action" value="<?php echo self::AjaxKey ?>">
		<input type="hidden" name="command" value="save_job">
		<input type="hidden" name="id" value="<?php echo $id ?>">

		<div id="poststuff">
			<div id="post-body" class="metabox-holder columns-2">
				<div id="post-body-content">
					<div id="titlediv">
						<div id="titlewrap">
							<label class="screen-reader-text" id="title-prompt-text" for="title"><?php echo $is_new ? esc_attr__( 'Add new job ad', self::DOMAIN ) : esc_attr__( 'Edit job', self::DOMAIN ); ?></label>
							<input required="required" type="text" name="title" value="<?php echo esc_attr( $row['title'] ) ?>"
							       id="title" spellcheck="true" autocomplete="off" placeholder="<?php echo $is_new ? esc_attr__( 'New job title', self::DOMAIN ) : esc_attr__( 'Job title', self::DOMAIN ); ?>" />
						</div>
						<div class="inside">
							<div id="edit-slug-box" class="hide-if-no-js"></div>
						</div>
					</div>
					<textarea name="description" id="description" class="tinymce" rows="10" cols="80"><?php echo esc_html( $row['description'] ) ?></textarea>
				</div>
				<div id="postbox-container-1" class="postbox-container">
					<div id="side-sortables" class="meta-box-sortables2">
						<div id="pageparentdiv" class="postbox ">
							<button type="button" class="handlediv" aria-expanded="true">
								<span class="screen-reader-text">Toggle panel: Job Attributes</span><span class="toggle-indicator" aria-hidden="true"></span>
							</button>
							<h2 class="hndle"><span>Job Attributes</span></h2>
							<div class="inside">
								<p class="post-attributes-label-wrapper">
									<label class="post-attributes-label" for="job_category_id">Category</label>
								</p>
								<select name='job_category_id' id='job_category_id' required="required">
									<option value="">(select category)</option>
									<?php foreach ( $allcats as $category ) { ?>
										<option <?php echo $category->id == $row['job_category_id'] ? 'selected' : ''; ?> class="level-0" value="<?php echo $category->id ?>">
											<?php echo esc_attr($category->category) ?>
										</option>
									<?php } ?>
								</select>

								<p class="post-attributes-label-wrapper">
									<label class="post-attributes-label" for="expiry_date">Expiry date</label>
								</p>
								<input min="<?php echo $is_new ? substr( current_time( 'mysql' ), 0, 10 ) : '' ?>" type="date" name="expiry_date" id="expiry_date" required="required" value="<?php echo esc_attr( date( 'Y-m-d', strtotime( $row['expiry_date'] ) ) ) ?>">

								<p class="post-attributes-label-wrapper">
									<label class="post-attributes-label" for="location">Location</label>
								</p>
								<input type="text" name="location" id="location" value="<?php echo esc_attr( $row['location'] ); ?>">

								<p class="post-attributes-label-wrapper">
									<label class="post-attributes-label" for="job_type">Type</label>
								</p>
								<select name="job_type" id="job_type">
									<option value="">(job type)</option>
									<?php foreach ( $types as $type ) { ?>
										<option <?php echo $type == $row['job_type'] ? 'selected' : '' ?>><?php echo esc_attr($type) ?></option>
									<?php } ?>
								</select>

								<p class="post-attributes-label-wrapper">
									<label class="post-attributes-label" for="salary_type">Salary Type</label>
								</p>
								<select name="salary_type" id="salary_type">
									<option value="">(salary type)</option>
									<?php foreach ( $salary_types as $type ) { ?>
										<option <?php echo $type == $row['salary_type'] ? 'selected' : '' ?>><?php echo esc_attr($type) ?></option>
									<?php } ?>
								</select>

								<p class="post-attributes-label-wrapper">
									<label class="post-attributes-label" for="salary">Salary</label>
								</p>
								<input type="number" step="0.01" name="salary" id="salary" value="<?php echo esc_attr( $row['salary'] ); ?>">

								<p class="post-attributes-label-wrapper">
									<label for="currency" class="post-attributes-label">Currency</label>
								</p>
								<select name="currency_code" id="currency_code">
									<option value="">(parent currency from settings)</option>
									<?php foreach ( $currencies as $currency ) { ?>
										<option <?php echo $row['currency_code'] == $currency['code'] ? 'selected' : '' ?> value="<?php echo $currency['code'] ?>">
											<?php echo $currency['code'] . ' (' . $currency['name'] . ') ' . $currency['symbol'] ?>
										</option>
									<?php } ?>
								</select>

								<p class="post-attributes-label-wrapper">
									<label class="post-attributes-label" for="is_active">Active</label>
								</p>
								<select id="is_active" name="is_active" required="required">
									<option value="1">Yes</option>
									<option value="0" <?php echo $row['is_active'] == 0 ? 'selected' : '' ?>>No</option>
								</select>

								<p>Need help? Visit <a href="?page=SAjobsF_jobs_user_manual">user manual</a> page.</p>
							</div>
						</div>

						<div id="submitdiv" class="postbox">
							<button type="button" class="handlediv" aria-expanded="true">
								<span class="screen-reader-text">Toggle panel: Save</span><span class="toggle-indicator" aria-hidden="true"></span>
							</button>
							<h2 class="hndle"><span>Save</span></h2>
							<div class="inside">
								<div class="submitbox" id="submitpost">
									<div id="minor-publishing">
										<div id="misc-publishing-actions">
											<div class="misc-pub-section curtime misc-pub-curtime">
												<?php if ( $is_new ) { ?>
													<span id="timestamp">Add time:</span>
													<b>Now</b>
												<?php } else { ?>
													<span id="timestamp">Added time:</span>
													<?php echo human_time_diff( strtotime( $row['added_time'] ), current_time( 'timestamp' ) ) ?> ago
												<?php } ?>
											</div>
											<?php if ( ! $is_new && $row['last_update_time'] ) { ?>
												<div class="misc-pub-section curtime misc-pub-curtime">
													<span id="timestamp">Last update:</span>
													<?php echo human_time_diff( strtotime( $row['last_update_time'] ), current_time( 'timestamp' ) ) ?> ago
												</div>
											<?php } ?>
										</div>

										<div id="major-publishing-actions">
											<div id="delete-action">
												<a class="submitdelete" href="?page=SAjobsF_jobs_management">Cancel</a>
											</div>

											<div id="publishing-action">
												<span class="spinner"></span>
												<input type="submit" name="publish" id="publish" class="button button-primary button-large" value="Save" />
											</div>
											<div class="clear"></div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</form>
</div>