<?php

use SAJobsF\Jobs\Currency;
use SAJobsF\Jobs\Jobs;

if ( ! defined( 'JOBS_F_URL' ) ) {
	echo esc_attr__( 'You cannot access this file', self::DOMAIN );
	exit();
}

$jobs     = new Jobs();
$template = $template ?? 'classic';

$Currency = new Currency();
$symbol   = $Currency->get_symbol();

$rows = $jobs->get_jobs( [
	'is_active'          => true,
	'category_is_active' => true,
	'expired' => false
] );

if ( count( $rows ) > 0 ) {
	?>
	<div class="is-hidden-mobile">
		<input type="search" id="satech_jobs_search" placeholder="Search in jobs .." />
	</div>
	<?php
}

if ( count( $rows ) === 0 ) {
	include_once __DIR__ . "/templates/no_ads.php";
} else {
	switch ( $template ) {
		case 'classic':
			include_once __DIR__ . "/templates/classic.php";
			break;
		case 'singleline':
			include_once __DIR__ . "/templates/signle_line.php";
			break;
	}
}
