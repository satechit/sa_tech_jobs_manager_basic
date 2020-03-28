<?php

if ( ! defined( 'JOBS_F_URL' ) ) {
	echo 'You cannot access this file';
	exit();
}
?>

<script>
	var ajaxurl = "<?php echo admin_url( 'admin-ajax.php' ) ?>";
</script>

<div class="satech_loader"></div>
<h1><?php echo $job_data['title'] ?></h1>
<div class="single_job_listing">
	<ul class="job-listing-meta meta">
		<li class="job-type freelance"><?php echo $job_data['category'] ?></li>
		<?php if ( ! empty( $job_data['location'] ) ) { ?>
			<li class="location">
				<i class="fa fa-map-marker" aria-hidden="true"></i> <?php echo $job_data['location'] ?>
			</li>
		<?php } ?>
		<li class="date-posted is-hidden-mobile">
			<time datetime="<?php ( new \DateTime( $job_data['added_time'] ) )->format( 'Y-m-d' ) ?>">Posted: <?php echo human_time_diff( strtotime( $job_data['added_time'] ), current_time( 'timestamp' ) ) ?> ago</time>
		</li>
	</ul>

	<div class="job_description">
		<?php echo $job_data['description'] ?>

		<div class="bottom mt10"></div>
		<?php if ( ! empty( $job_data['job_type'] ) ) { ?>
			<div>
				<b><?php echo esc_attr_e( 'Job Type', self::DOMAIN ) ?>:</b> <?php echo $job_data['job_type'] ?>
			</div>
		<?php } ?>
		<?php if ( ! empty( $job_data['salary_type'] ) && ! empty( $job_data['salary'] ) ) {
			$word = str_replace( [ 'Annually', 'Project Based', 'Per Word', 'ly' ], [
				'Annum',
				'Project',
				'Word',
				'',
			], $job_data['salary_type'] );
			?>
			<div>
				<b><?php echo esc_attr_e( 'Salary', self::DOMAIN ) ?>:</b> <?php echo $job_data['currency_symbol']; ?><?php echo $job_data['salary'] ?>/<?php echo $word ?>
			</div>
		<?php } ?>

		<div class="bottom mb10"></div>
	</div>

	<?php
	$files = $jobs->get_files( $job_data['id'] );
	if ( count( $files ) > 0 ) { ?>
		<div class="jobsP_files">
			<?php foreach ( $files as $file ) {
				$ext = $this->getFileExtension( $file['path'] );

				switch ( $ext ) {
					case 'doc':
					case 'docx':
					case 'xls':
					case 'xlsx':
					case 'ppt':
					case 'pptx':
						$url    = 'https://view.officeapps.live.com/op/embed.aspx?src=' . urlencode( $file['url'] );
						$iframe = true;
						break;
					case 'pdf':
					case 'txt':
						$iframe = true;
						$url    = $file['url'];
						break;
					default:
						$iframe = false;
						$url    = $file['url'];
				}

				echo "<a href='{$url}' target='_blank'>";
				echo basename( $file['path'] );
				echo "</a> ";
			} ?>
		</div>
	<?php } ?>

	<?php if ( empty( $job_data['external_link'] ) ) { ?>
		<div>
			<a href="javascript:;" id="apply_job_btn"><?php esc_attr_e( 'Click here to apply for this job', self::DOMAIN ) ?></a>
			<div class="application_details hide">
				<form action="" method="post" class="" enctype="multipart/form-data" novalidate="novalidate" id="job_form">
					<input type="hidden" name="action" value="<?php echo self::AjaxKey ?>">
					<input type="hidden" name="command" value="send_job">
					<input type="hidden" name="job_id" value="<?php echo $job_id ?>">

					<div class="field">
						<label class="label" for="form_name">Your name (required)</label>
						<div class="control">
							<input id="form_name" class="input" type="text" name="name" placeholder="Type your name" required="required">
						</div>
					</div>

					<div class="field">
						<label class="label" for="form_contact">Contact no (required)</label>
						<div class="control">
							<input id="form_contact" class="input" type="tel" name="contact" placeholder="Type your contact number." required="required">
						</div>
					</div>

					<div class="field">
						<label class="label" for="form_email">Your Email (required)</label>
						<div class="control">
							<input id="form_email" class="input is-fullwidth" type="email" name="email" placeholder="Type your email address." required="required">
						</div>
					</div>

					<div class="field">
						<label class="label" for="form_message">Your Message</label>
						<div class="control">
							<textarea id="form_message" rows="5" class="textarea is-fullwidth" name="message" placeholder="Type your message here."></textarea>
						</div>
					</div>

					<div class="columns">
						<div class="column">
							Allowed types: .pdf, .doc, .docx, images/*
						</div>
					</div>
					<div class="columns">
						<div class="column is-6">
							<input type="file" class="drop_files" id="cv_file" name="cv_file"
							       data-label="<?php esc_attr_e( 'Drop your C.V', self::DOMAIN ) ?>"
							       accept="<?php echo $this->get_file_accept() ?>">
						</div>
						<div class="column is-6">
							<input type="file" class="drop_files" id="other_files" name="other_files[]" multiple="multiple"
							       data-label="<?php esc_attr_e( 'Drop other files', self::DOMAIN ) ?>"
							       accept="<?php echo $this->get_file_accept() ?>">
						</div>
					</div>
					<div class="clearDiv"> </div>

					<hr class="mb20">
					<div class="columns mt20">
						<div class="column">
							<button class="button is-link"><?php esc_attr_e( 'Submit', self::DOMAIN ) ?></button>
						</div>
					</div>
				</form>
			</div>
		</div>
	<?php } else { ?>
		<a href="<?php echo $job_data['external_link'] ?>" target="_blank" id="apply_job_link"><?php esc_attr_e( 'Click here to apply for this job', self::DOMAIN ) ?></a>
	<?php } ?>
</div>