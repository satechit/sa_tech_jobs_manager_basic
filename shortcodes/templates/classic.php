<?php if ( $this->is_mobile() ) { ?>
	<div class="mobile">
		<?php foreach ( $rows as $row ) {
			$symbol = $Currency->get_symbol( $row['currency_code'] );
			$url    = $jobs->get_apply_job_link( $row['id'] );
			?>
			<div class="record">
				<div class="title">
					<a href="<?php echo $url ?>"><?php echo $row['title'] ?></a>
				</div>
				<?php if ( ! empty( $row['job_type'] ) ) { ?>
					<div class="category">
						<i class="fa fa-briefcase"></i>
						<?php echo $row['job_type'] ?>
					</div>
				<?php } ?>
				<?php if ( ! empty( $row['location'] ) ) { ?>
					<div class="location"><i class="fa fa-map-marker"></i> <?php echo $row['location'] ?></div>
				<?php } ?>
				<a href="<?php echo $url ?>" class="link">
					More Details <i class="fa fa-long-arrow-right" aria-hidden="true"></i>
				</a>
			</div>
		<?php } ?>
	</div>
<?php } else { ?>
	<table class="satech_table">
		<tbody>
		<?php
		foreach ( $rows as $row ) {
			$symbol = $Currency->get_symbol( $row['currency_code'] );
			$url    = $jobs->get_apply_job_link( $row['id'] );
			?>
			<tr class="satech_job_listings">
				<td class="text-left">
					<span class="title"><a href="<?php echo $url ?>"><?php echo $row['title'] ?></a></span><br>
					<span class="category">(<?php echo $row['category'] ?>)</span>

					<?php if ( ! empty( $row['salary_type'] ) ) {
						$word = str_replace( [ 'Annually', 'Project Based', 'Per Word', 'ly' ], [
							'Annum',
							'Project',
							'Word',
							'',
						], $row['salary_type'] );
						?>
						<span class="salary is-hidden-mobile"><kbd><?php echo $symbol . $row['salary'] ?>/<?php echo $word ?></kbd></span>
					<?php } ?>
				</td>
				<td class="text-left">
					<?php if ( ! empty( $row['location'] ) ) { ?>
						<span class="location"><i class="fa fa-map-marker"></i> <?php echo $row['location'] ?></span>
						<br>
					<?php } ?>
					<?php if ( ! empty( $row['job_type'] ) ) { ?>
						<span class="is-hidden-mobile"><i class="fa fa-briefcase" aria-hidden="true"></i> <?php echo $row['job_type'] ?></span>
					<?php } ?>
				</td>
				<td class="right is-hidden-mobile">
					<a href="<?php echo $url ?>" class="job_detail_link">
						Details
						<i class="fa fa-long-arrow-right" aria-hidden="true"></i>
					</a>
				</td>
			</tr>
		<?php } ?>
		</tbody>
	</table>
<?php } ?>
