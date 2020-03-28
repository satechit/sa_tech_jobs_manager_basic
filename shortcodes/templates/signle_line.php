<table class="single_line_table">
	<?php foreach ( $rows as $row ) {
		$symbol = $Currency->get_symbol( $row['currency_code'] );
		$url    = $jobs->get_apply_job_link( $row['id'] );
		?>
		<tr class="satech_job_listings">
			<td class="text-left">
				<?php if ( ! empty( $row['location'] ) ) { ?>
					<span class="location is-hidden-desktop pull-right"><i class="fa fa-map-marker"></i> <?php echo $row['location'] ?></span>
				<?php } ?>

				<span class="title"><a href="<?php echo $url ?>"><?php echo $row['title'] ?></a></span>
				<?php if ( trim( $row['job_type'] ) !== '' ) { ?>
					<span class="job_type is-hidden-mobile">(<?php echo $row['job_type'] ?>)</span>
				<?php } ?>
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

				<span class="link is-hidden-mobile">
					<a href="<?php echo $url ?>">Details <i class="fa fa-long-arrow-right" aria-hidden="true"></i></a>
				</span>
				<?php if ( ! empty( $row['location'] ) ) { ?>
					<span class="location is-hidden-mobile"><i class="fa fa-map-marker"></i> <?php echo $row['location'] ?></span>
				<?php } ?>
			</td>
		</tr>
	<?php } ?>
</table>
