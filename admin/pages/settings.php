<?php

use SAJobsF\Jobs\Currency;

?>

<form action="" class="login_form modal hide" id="cat_form">
	<input type="hidden" name="action" value="<?php echo self::AjaxKey ?>">
	<input type="hidden" name="command" value="save_category">
	<input type="hidden" name="id" value="0">
	<h3>Save Category</h3>
	<div class="body">
		<div class="row">
			<label for="category">Category name</label>
			<input type="text" class="form-control" id="category" name="category" aria-describedby="emailHelp" placeholder="Enter category" autofocus="autofocus" required="required">
		</div>
		<div class="row">
			<label class="form-check-label" for="is_active"><input checked="checked" value="1" type="checkbox" name="is_active" id="is_active"> Active Category
			</label>
			<small id="activeHelp" class="form-text text-muted">If this option is enabled then category will show on website</small>
		</div>
	</div>
	<div class="footer2">
		<button type="button" onclick="jQuery('#cat_form').trigger('submit');" class="button button-primary">Save changes</button>
	</div>
</form>

<div class="wrap">
	<h1 class="wp-heading-inline">
		Settings
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
								<label for="from_name" class="label">From name and email address:</label>
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
									The name and email address that you would like the applicants to see when they receive an email from you.
									<br>
								</p>
							</div>
						</div>
						<div class="column is-6">
							<div class="field">
								<label for="currency_code" class="label">Select default currency:</label>
								<div class="control">
									<?php $currencies = ( new Currency() )->get_currencies();
									$value            = $this->get_option( 'currency_code' ); ?>
									<div class="select is-fullwidth">
										<select name="currency_code" id="currency_code" class="save_setting noarrow mw100">
											<option value="">== No currency ==</option>
											<?php foreach ( $currencies as $currency ) { ?>
												<option <?php echo $value == $currency['code'] ? 'selected' : '' ?> value="<?php echo $currency['code'] ?>">
													<?php echo $currency['code'] . ' (' . $currency['name'] . ') ' . $currency['symbol'] ?>
												</option>
											<?php } ?>
										</select>
									</div>
									<p class="help">This currency symbol will appear with job listing on website.</p>
								</div>
							</div>
						</div>
					</div>

					<div class="columns">
						<div class="column is-6">
							<div class="field">
								<label for="job_form_page" class="label">Job form page:</label>
								<div class="control">
									<?php $pages = get_pages(); ?>
									<?php $value = $this->get_option( 'job_form_page' ); ?>
									<div class="select is-fullwidth">
										<select name="job_form_page" id="job_form_page" class="save_setting noarrow mw100" data-old-value="<?php echo $value ?>">
											<option value="">== Select job application page ==</option>
											<?php foreach ( $pages as $page ) { ?>
												<option <?php echo $value == $page->ID ? 'selected' : '' ?> value="<?php echo $page->ID ?>"><?php echo $page->post_title ?></option>
											<?php } ?>
										</select>
									</div>

									<p class="help">
										Please select the page for job application form and description
										<br>
										<b>Note:</b> Do not enter any content on this page.
									</p>
								</div>
							</div>
						</div>
						<div class="column is-6">
							<div class="field">
								<label for="remove_jobs_after_days" class="label">Delete job applications automatically:</label>
								<div class="control">
									<?php $value = $this->get_option( 'remove_jobs_after_days' ); ?>
									<div class="select is-fullwidth">
										<select name="remove_jobs_after_days" id="remove_jobs_after_days" class="save_setting noarrow mw100" data-old-value="<?php echo $value ?>">
											<option value="0">== Never ==</option>
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
									<input type="text" value="[sa_jobs_basic_list_design1]" readonly style="width: 210px">
									<button title="Copy shortcode" data-shortcode="[sa_jobs_basic_list_design1]" type="button" class="button button-primary copy_btn">Copy</button>
								</div>
								<div>
									<input type="text" value="[sa_jobs_basic_list_design2]" readonly style="width: 210px">
									<button title="Copy shortcode" data-shortcode="[sa_jobs_basic_list_design2]" type="button" class="button button-primary copy_btn">Copy</button>
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