<?php

use SAJobsF\Jobs\Category;
use SAJobsF\Jobs\EmailLogs;
use SAJobsF\Jobs\Jobs;
use SAJobsF\Jobs\Logs;
use SAJobsF\Jobs\ReceivedJobs;

## Sanitizing Whole POST array
$_POST = wp_kses_post_deep( $_POST );

## Action = SA_jobsF_jobs_ajax
$command = $_POST['command'] ?? '';
unset( $_POST['command'] );
unset( $_POST['action'] );

switch ( $command ) {
	case 'save_category':
		$cats = new Category();

		echo $cats->save( $_POST );
		break;
	case 'get_cats':
		$cats = new Category();

		$rows = $cats->get_cats( $_POST );
		$this->json( $rows );
		break;
	case 'delOneCat':
		$cats        = new Category();
		$_POST['id'] = $_POST['id'] ?? 0;
		$cats->delete_cats( $_POST['id'] );
		break;
	case 'getOneCat':
		$cats        = new Category();
		$_POST['id'] = $_POST['id'] ?? 0;
		$this->json( $cats->get_single_cat( $_POST['id'] ) );
		break;
	case 'deleteMulti':
		$cats                 = new Category();
		$_POST['bulk-action'] = $_POST['bulk-action'] ?? '';
		$_POST['checked']     = $_POST['checked'] ?? [];

		switch ( $_POST['bulk-action'] ) {
			case 'delete':
				$cats->delete_cats( $_POST['checked'] );
				echo 'OK';
				break;
			case 'activate':
				foreach ( $_POST['checked'] as $id ) {
					$cats->activate_category( $id );
				}
				echo 'OK';
				break;
			case 'deactivate':
				foreach ( $_POST['checked'] as $id ) {
					$cats->deactivate_category( $id );
				}
				echo 'OK';
				break;
			default:
				echo esc_attr__( 'Invalid bulk action', self::DOMAIN );
		}
		break;
	case 'resetAllData':
		echo $this->drop_tables();
		break;
	case 'get_jobs':
		$jobs                        = new Jobs();
		$_POST['count_applications'] = true;
		$_POST['with_apply_link']    = true;
		$rows                        = $jobs->get_jobs( $_POST );
		$this->json( $rows );
		break;
	case 'get_job':
		$_POST['id'] = $_POST['id'] ?? 0;

		$jobs = new Jobs();
		try {
			$row = $jobs->get_job( $_POST['id'] );
		} catch ( Exception $e ) {
			$row = [];
		}
		$this->json( $row );
		break;
	case 'save_job':
		$jobs = new Jobs();

		try {
			echo $jobs->save_job( $_POST, $_FILES );
		} catch ( Exception $e ) {
			echo $e->getMessage();
		}
		exit();
		break;
	case 'delete_job':
		$jobs        = new Jobs();
		$_POST['id'] = $_POST['id'] ?? 0;
		$jobs->delete_job( $_POST['id'] );
		exit();
		break;
	case 'restore_job':
		$jobs        = new Jobs();
		$_POST['id'] = $_POST['id'] ?? 0;
		$jobs->restore_job( $_POST['id'] );
		exit();
		break;
	case 'get_job_counters':
		$jobs = new Jobs();
		$this->json( [
			'all'      => $jobs->count_job_ads(),
			'active'   => $jobs->count_job_ads( 'is_active=1' ),
			'inactive' => $jobs->count_job_ads( 'is_active=0' ),
			'expired'  => $jobs->count_job_ads( 'expired=1' ),
			'trash'    => $jobs->count_job_ads( 'deleted=1' ),
		] );
		exit();
		break;
	case 'change_job_activation':
		$jobs           = new Jobs();
		$_POST['id']    = $_POST['id'] ?? 0;
		$_POST['value'] = $_POST['value'] ?? -1;
		$r              = $jobs->change_activation( $_POST['id'], $_POST['value'] );
		echo $r;
		break;
	case 'delete_file':
		$_POST['path'] = $_POST['path'] ?? '';
		if ( $_POST['path'] <> '' ) {
			$_POST['path'] = base64_decode( $_POST['path'] );
		}
		if ( ! is_file( $_POST['path'] ) ) {
			echo 'File not found on server.';
			exit();
		}
		@unlink( $_POST['path'] );

		echo is_file( $_POST['path'] ) ? esc_attr__( 'File cannot delete now, Please try again later', 'jobsp-domain' ) : 'OK';
		break;
	case 'save_option':
		$_POST['option_name']  = $_POST['option_name'] ?? '';
		$_POST['option_value'] = $_POST['option_value'] ?? '';

		echo $this->set_option( $_POST['option_name'], $_POST['option_value'] ) ? 'OK' : esc_attr__( 'Not saved', 'jobsp-domain' );
		break;
	case 'send_job':
		$job = new SAJobsF\Jobs\Jobs();
		try {
			echo $job->submit_job_by_applicant( $_POST, $_FILES );
		} catch ( Exception $e ) {
			echo $e->getMessage();
		}
		exit();
		break;
	case 'get_received_job':
		$RH          = new ReceivedJobs();
		$Logs        = new EmailLogs();
		$_POST['id'] = $_POST['id'] ?? 0;

		$RH->mark_as_read( $_POST['id'] );
		$row                = $RH->get_received_job( $_POST['id'] );
		$row['status_text'] = $RH->get_status_text( $row['status'] ?? -1 );

		$notes = [];

		$logs     = Logs::get_logs( $_POST['id'], 'application' );

		$this->json( [
			'row'       => $row,
			'notes'     => $notes,
			'logs'      => $logs,
		] );
		break;
	case 'get_received_jobs':
		$RH                       = new ReceivedJobs();
		$_POST['with_apply_link'] = true;
		$this->json( $RH->get_received_jobs( $_POST ) );
		break;
	case 'received_bulk_action':
		$_POST['bulk-action'] = $_POST['bulk-action'] ?? '';
		$RH                   = new ReceivedJobs();
		if ( $_POST['bulk-action'] == 'delete' ) {
			try {
				echo $RH->delete_received_job( $_POST['checked'] ) ? 'OK' : esc_attr__( 'Cannot delete selected jobs', self::DOMAIN );
			} catch ( Exception $e ) {
				echo $e->getMessage();
			}
		} else if ( $_POST['bulk-action'] == 'restore' ) {
			try {
				echo $RH->restore_application( $_POST['checked'] ) ? 'OK' : esc_attr__( 'Cannot restore selected jobs', self::DOMAIN );
			} catch ( Exception $e ) {
				echo $e->getMessage();
			}
		} else {
			echo esc_attr__( 'Unknown', self::DOMAIN );
		}
		break;
	case 'delete_single_app':
		$RH = new ReceivedJobs();
		try {
			$RH->delete_received_job( $_POST['id'] ?? 0 );
			echo 'OK';
		} catch ( Exception $e ) {
			echo $e->getMessage();
		}
		break;
	case 'get_application_counter_all_types':
		$RH = new ReceivedJobs();
		$this->json( $RH->count_all_types() );
		break;
	case 'restore_single_app':
		$_POST['id'] = $_POST['id'] ?? 0;
		$RH          = new ReceivedJobs();
		try {
			$RH->restore_application( $_POST['id'] );
			echo 'OK';
		} catch ( Exception $e ) {
			echo $e->getMessage();
		}
		break;
	case 'openEmail':
		$RH          = new ReceivedJobs();
		$_POST['id'] = $_POST['id'] ?? 0;
		$this->json( $RH->get_applicant_information( $_POST['id'] ) );
		break;
	case 'send_email':
		try {
			echo $this->send_mail( $_POST );

			if ( ! empty( $_POST['application_id'] ) && ! empty( $_POST['new_status'] ) ) {
				$RH = new ReceivedJobs();
			}
		} catch ( Exception $e ) {
			echo $e->getMessage();
		}
		break;
	case 'bulk-job-ads':
		$jobs             = new Jobs();
		$_POST['checked'] = $_POST['checked'] ?? [];
		switch ( $_POST['bulk-action'] ?? '' ) {
			case 'activate':
				foreach ( $_POST['checked'] as $id ) {
					$jobs->change_activation( $id, 1 );
				}
				echo 'OK';
				break;
			case 'deactivate':
				foreach ( $_POST['checked'] as $id ) {
					$jobs->change_activation( $id, 0 );
				}
				echo 'OK';
				break;
			case 'delete':
				$jobs->delete_job( $_POST['checked'] );
				echo 'OK';
				break;
			case 'restore':
				$jobs->restore_job( $_POST['checked'] );
				echo 'OK';
				break;
			default:
				echo 'Invalid action';
		}
		break;
	case 'view_job_ad_logs':
		$this->json( Logs::get_logs( $_POST['id'] ?? 0, 'job_ad' ) );
		break;
	default:
		// Do nothing.
		exit();
}