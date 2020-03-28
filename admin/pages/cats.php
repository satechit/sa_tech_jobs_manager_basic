<div class="modal" id="form-modal">
	<div class="modal-background"></div>
	<div class="modal-card">
		<header class="modal-card-head">
			<p class="modal-card-title"><?php esc_attr_e( 'Save Category', self::DOMAIN ) ?></p>
			<button class="delete" aria-label="close" onclick="$('#form-modal').removeClass('is-active')"></button>
		</header>
		<section class="modal-card-body">
			<form action="" class="login_form" id="cat_form">
				<input type="hidden" name="action" value="<?php echo self::AjaxKey ?>">
				<input type="hidden" name="command" value="save_category">
				<input type="hidden" name="id" value="0">

				<div class="field">
					<label class="label" for="category"><?php esc_attr_e( 'Category name', self::DOMAIN ) ?></label>
					<input type="text" class="input" id="category" name="category" aria-describedby="emailHelp" placeholder="<?php esc_attr_e( 'Enter category', self::DOMAIN ) ?>" autofocus="autofocus" required="required">
				</div>

				<div class="field">
					<label class="form-check-label" for="is_active">
						<input checked="checked" value="1" type="checkbox" name="is_active" id="is_active"> <?php esc_attr_e( 'Active Category', self::DOMAIN ) ?>
					</label>
					<p class="help"><?php esc_attr_e( 'If this option is enabled then category will show on website', self::DOMAIN ) ?></p>
				</div>
			</form>
		</section>
		<footer class="modal-card-foot">
			<button class="button is-success" onclick="$('#cat_form').trigger('submit')"><?php esc_attr_e('Save', self::DOMAIN) ?></button>
			<button class="button" type="button" onclick="$('#form-modal').removeClass('is-active')"><?php esc_attr_e('Cancel', self::DOMAIN) ?></button>
		</footer>
	</div>
</div>

<div class="wrap">
	<h1 class="wp-heading-inline"><?php esc_attr_e( 'Job Categories', self::DOMAIN ) ?></h1>
	<a href="#" title="Press Ctrl+M" id="add_cat_btn" data-target="form-modal" aria-haspopup="true" class="page-title-action"><?php esc_attr_e( 'Add Category', self::DOMAIN ) ?></a>
	<hr class="wp-header-end">

	<form action="" id="bulk-action-form" onsubmit="return false;">
		<input type="hidden" name="action" value="<?php echo self::AjaxKey ?>">
		<input type="hidden" name="command" value="deleteMulti">
		<div class="tablenav top">
			<div class="alignleft actions bulkactions">
				<label for="bulk-action-selector-top" class="screen-reader-text"><?php esc_attr_e( 'Select bulk action', self::DOMAIN ) ?></label>
				<select name="bulk-action" id="bulk-action-selector-top">
					<option value="-1"><?php esc_attr_e( 'Bulk Actions', self::DOMAIN ) ?></option>
					<option value="activate"><?php esc_attr_e( 'Activate', self::DOMAIN ) ?></option>
					<option value="deactivate"><?php esc_attr_e( 'Deactivate', self::DOMAIN ) ?></option>
					<option value="delete"><?php esc_attr_e( 'Delete', self::DOMAIN ) ?></option>
				</select>
				<input type="submit" id="doaction" class="button action" value="Apply">
			</div>
			<div class="tablenav-pages one-page">
				<span class="displaying-num">&nbsp;</span>
				<span class="pagination-links">
					<span class="tablenav-pages-navspan button disabled" aria-hidden="true">«</span>
					<span class="tablenav-pages-navspan button disabled" aria-hidden="true">‹</span>
					<span class="paging-input">
						<label for="current-page-selector" class="screen-reader-text"><?php esc_attr_e( 'Current Page', self::DOMAIN ) ?></label>
						<input class="current-page" id="current-page-selector" type="text" name="paged" value="1" size="1" aria-describedby="table-paging">
						<span class="tablenav-paging-text"> of <span class="total-pages">1</span></span>
					</span>
					<span class="tablenav-pages-navspan button disabled" aria-hidden="true">›</span>
					<span class="tablenav-pages-navspan button disabled" aria-hidden="true">»</span>
				</span>
			</div>
			<br class="clear">
		</div>
		<h2 class="screen-reader-text"><?php esc_attr_e( 'Categories list' ) ?></h2>
		<table class="wp-list-table widefat plugins" id="cats_table">
			<thead>
			<tr>
				<td id="cb" class="manage-column column-cb check-column is-hidden-mobile">
					<label class="screen-reader-text" for="cb-select-all-1"><?php esc_attr_e( 'Select All', self::DOMAIN ) ?></label><input id="cb-select-all-1" type="checkbox">
				</td>
				<th scope="col" class="manage-column column-primary column-category"><?php esc_attr_e( 'Category', self::DOMAIN ) ?></th>
				<th scope="col" class="manage-column column-is_active"><?php esc_attr_e( 'Active', self::DOMAIN ) ?></th>
				<th scope="col" class="manage-column column-counter"></th>
			</tr>
			</thead>
			<tbody id="the-list">
			<tr>
				<th scope="row" class="check-column is-hidden-mobile"></th>
				<td class="text-center" colspan="3">
					<i class="fa fa-spin fa-spinner"></i> <?php esc_attr_e( 'Loading', self::DOMAIN ) ?> ....
				</td>
			</tr>
			</tbody>
		</table>
	</form>
</div>
<?php include_once self::JOBS_P_PATH . "/tmpl/admin/cats.tpl"; ?>