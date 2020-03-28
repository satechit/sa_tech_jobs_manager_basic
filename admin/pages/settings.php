<?php

use SAJobsF\Jobs\Currency;

?>

<form action="" class="login_form modal hide" id="cat_form">
	<input type="hidden" name="action" value="<?php echo self::AjaxKey ?>">
	<input type="hidden" name="command" value="save_category">
	<input type="hidden" name="id" value="0">
	<h3><?php esc_attr_e( 'Save Category', 'jobsp-domain' ) ?></h3>
	<div class="body">
		<div class="row">
			<label for="category"><?php esc_attr_e( 'Category name', 'jobsp-domain' ) ?></label>
			<input type="text" class="form-control" id="category" name="category" aria-describedby="emailHelp" placeholder="Enter category" autofocus="autofocus" required="required">
		</div>
		<div class="row">
			<label class="form-check-label" for="is_active"><input checked="checked" value="1" type="checkbox" name="is_active" id="is_active"> <?php esc_attr_e( 'Active Category', 'jobsp-domain' ) ?>
			</label>
			<small id="activeHelp" class="form-text text-muted"><?php esc_attr_e( 'If this option is enabled then category will show on website', 'jobsp-domain' ) ?></small>
		</div>
	</div>
	<div class="footer2">
		<button type="button" onclick="jQuery('#cat_form').trigger('submit');" class="button button-primary"><?php esc_attr_e( 'Save changes', 'jobsp-domain' ) ?></button>
	</div>
</form>

<div class="wrap">
	<h1 class="wp-heading-inline">
		<?php esc_attr_e( 'Settings', 'jobsp-domain' ) ?>
		<i id="loader_span" class="fa fa-spin fa-spinner hide"></i>
	</h1>
	<hr class="wp-header-end">

	<div class="card mt10">
		<div class="card-content">
			<div class="tabs is-boxed">
				<ul>
					<li class="is-active" data-target="general_tab">
						<a>
							<span>General</span>
						</a>
					</li>
					<li data-target="info_tab">
						<a>
							<span>Info</span>
						</a>
					</li>
				</ul>
			</div>
			<div class="tab-content">
				<div id="general_tab" class="active">
					<div class="columns">
						<div class="column is-6">
							<div class="field">
								<label for="company_name" class="label"><?php esc_attr_e( 'Company name', self::DOMAIN ) ?>:</label>
								<div class="control">
									<input class="input save_setting" type="text" placeholder="<?php esc_attr_e( 'Company name', self::DOMAIN ) ?>" name="company_name" id="company_name" value="<?php echo esc_attr( $this->get_option( 'company_name' ) ) ?>">

									<p class="help">
										<?php esc_attr_e( 'Type your company name and use it in email templates.', self::DOMAIN ) ?>
										<br>
										<?php esc_attr_e( 'You can use it as {company_name} in email templates.', self::DOMAIN ) ?>
									</p>
								</div>
							</div>
						</div>
						<div class="column is-6">
							<div class="field">
								<label for="from_name" class="label"><?php esc_attr_e( 'From name and email address', self::DOMAIN ) ?>:</label>
								<div class="field is-horizontal mb5">
									<div class="field-body">
										<div class="field">
											<p class="control is-expanded has-icons-left">
												<input class="input save_setting" type="text" placeholder="Name" name="from_name" id="from_name" value="<?php echo esc_attr( $this->get_option( 'from_name' ) ) ?>">
												<span class="icon is-small is-left"><i class="fa fa-user"></i></span>
											</p>
										</div>
										<div class="field">
											<p class="control is-expanded has-icons-left has-icons-right">
												<input class="input save_setting" type="email" placeholder="Email" value="<?php echo esc_attr( $this->get_option( 'from_email' ) ) ?>" name="from_email" id="from_email">
												<span class="icon is-small is-left"><i class="fa fa-envelope"></i></span>
											</p>
										</div>
									</div>
								</div>
								<p class="help">
									<?php esc_attr_e( 'The name and email address that you would like the applicants to see when they receive an email from you.', self::DOMAIN ) ?><br>
								</p>
							</div>
						</div>
					</div>
					<div class="columns">
						<div class="column is-6">
							<div class="field">
								<label for="job_email" class="label"><?php esc_attr_e( 'Email address where you would like to receive the job applications', self::DOMAIN ) ?>:</label>
								<div class="control">
									<input id="job_email" name="job_email" value="<?php echo esc_attr( $this->get_option( 'job_email' ) ) ?>" class="input save_setting"
									       type="email" placeholder="<?php esc_attr_e( 'Email jobs will be received at this email', self::DOMAIN ) ?>">

									<p class="help">
										Please enter a valid email address if you'd like to receive the job applications via email. Otherwise, if an email template is attached with the event "Email to admin on new job application", the system will start sending the job applications at
										Administrator's email address.
									</p>
								</div>
							</div>
						</div>
						<div class="column is-6">
							<div class="field">
								<label for="currency_code" class="label"><?php esc_attr_e( 'Select default currency', self::DOMAIN ) ?>:</label>
								<div class="control">
									<?php $currencies = ( new Currency() )->get_currencies();
									$value            = $this->get_option( 'currency_code' ); ?>
									<div class="select is-fullwidth">
										<select name="currency_code" id="currency_code" class="save_setting noarrow mw100">
											<option value="">== <?php esc_attr_e( 'No currency', self::DOMAIN ) ?> ==</option>
											<?php foreach ( $currencies as $currency ) { ?>
												<option <?php echo $value == $currency['code'] ? 'selected' : '' ?> value="<?php echo $currency['code'] ?>">
													<?php echo $currency['code'] . ' (' . $currency['name'] . ') ' . $currency['symbol'] ?>
												</option>
											<?php } ?>
										</select>
									</div>
									<p class="help"><?php esc_attr_e( 'This currency symbol will appear with job listing on website.', self::DOMAIN ) ?></p>
								</div>
							</div>
						</div>
					</div>
					<div class="columns">
						<div class="column is-6">
							<div class="field">
								<label for="job_form_page" class="label"><?php esc_attr_e( 'Job form page:', self::DOMAIN ) ?></label>
								<div class="control">
									<?php $pages = get_pages(); ?>
									<?php $value = $this->get_option( 'job_form_page' ); ?>
									<div class="select is-fullwidth">
										<select name="job_form_page" id="job_form_page" class="save_setting noarrow mw100" data-old-value="<?php echo $value ?>">
											<option value="">== <?php esc_attr_e( 'Select job application page' ) ?> ==</option>
											<?php foreach ( $pages as $page ) { ?>
												<option <?php echo $value == $page->ID ? 'selected' : '' ?> value="<?php echo $page->ID ?>"><?php echo $page->post_title ?></option>
											<?php } ?>
										</select>
									</div>

									<p class="help">
										<?php esc_attr_e( 'Please select the page for job application form and description', self::DOMAIN ) ?>
										<br>
										<b>Note:</b> <?php esc_attr_e( 'Do not enter any content on this page.' ) ?>
									</p>
								</div>
							</div>
						</div>
						<div class="column is-6">
							<div class="field">
								<label for="remove_jobs_after_days" class="label"><?php esc_attr_e( 'Delete job applications automatically', self::DOMAIN ) ?>:</label>
								<div class="control">
									<?php $value = $this->get_option( 'remove_jobs_after_days' ); ?>
									<div class="select is-fullwidth">
										<select name="remove_jobs_after_days" id="remove_jobs_after_days" class="save_setting noarrow mw100" data-old-value="<?php echo $value ?>">
											<option value="0">== <?php esc_attr_e( 'Never', self::DOMAIN ) ?> ==</option>
											<?php if ( $this->is_test_server() ) { ?>
												<option value="1" <?php echo $value == 1 ? 'selected' : '' ?>>1</option>
											<?php } ?>
											<?php for ( $x = 10; $x <= 500; $x += 10 ) { ?>
												<option <?php echo $value == $x ? 'selected' : '' ?> value="<?php echo $x ?>"><?php echo $x ?></option>
											<?php } ?>
										</select>
									</div>
									<p class="help">
										Our plugin is GDPR compliant. You've the power to delete job applications after a set number of days. Please select the number of days from the menu above if you'd like to make use of this function.
									</p>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div id="info_tab">
					<table class="table is-bordered">
						<tr>
							<td colspan="2" class="shortcode_td">
								<b>Available Shortcodes</b>
								<div>
									<input type="text" value="[sa_jobs_list_design1]" readonly>
									<button title="Copy shortcode" data-shortcode="[sa_jobs_list_design1]" type="button" class="button button-primary copy_btn"><?php esc_attr_e( 'Copy', self::DOMAIN ) ?></button>
								</div>
								<div>
									<input type="text" value="[sa_jobs_list_design2]" readonly>
									<button title="Copy shortcode" data-shortcode="[sa_jobs_list_design2]" type="button" class="button button-primary copy_btn"><?php esc_attr_e( 'Copy', self::DOMAIN ) ?></button>
								</div>
							</td>
						</tr>
						<tr class="is-hidden-mobile">
							<td><strong>Database Version:</strong> <em><?php echo $this->get_db_version() ?></em></td>
							<td><strong>Last Database Update:</strong>
								<em><?php echo $this->date( self::CurrentDBUpdateDate ); ?></em>
							</td>
						</tr>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>