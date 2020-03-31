<?php

namespace SAJobsF\Jobs;

class ReceivedJobs {

	private $DB;
	private $obj;

	const Status = [
		'New'         => 0,
		'Read'        => 1,
		'Rejected'    => 11,
		'Shortlisted' => 10,
		'Interview'   => 12,
		'Selected'    => 100,
	];

	public function __construct() {
		global $SAJobsF;

		$this->obj = $SAJobsF;
		$this->DB  = $this->obj->DB2;
	}

	public function get_received_jobs( $args = [] ) {
		$this->auto_delete();

		$default      = [
			'mysql_cols'        => '*',
			'orderBy'           => 'id',
			'order'             => 'DESC',
			'page'              => 1,
			'pagesize'          => \get_option( 'posts_per_page' ),
			'mark_as_read'      => true,
			'job_category_name' => '',
			'ad_id'             => '',
			'status'            => null,
			'search'            => '',
			'deleted'           => 0,

			'with_apply_link' => false,
		];
		$args         = wp_parse_args( $args, $default );
		$args['page'] = (int) $args['page'];

		$this->DB->pageLimit = $args['pagesize'];

		try {
			$this->DB->withTotalCount();

			$args['job_category_name'] = trim( (string) $args['job_category_name'] );
			if ( $args['job_category_name'] <> '' ) {
				$this->DB->where( 'job_category_name', $args['job_category_name'] );
			}

			if ( ! is_null( $args['deleted'] ) ) {
				$this->DB->where( 'deleted', $args['deleted'] );
			}
			if ( $args['ad_id'] != '' ) {
				$this->DB->where( 'ad_id', $args['ad_id'] );
			}
			if ( ! is_null( $args['status'] ) ) {
				$this->DB->where( 'status', $args['status'] );
			}
			if ( $args['search'] != '' ) {
				$s = '%' . $args['search'] . '%';
				$this->DB->where( "(`job_category_name` LIKE ? OR `job_title` LIKE ? OR `applicant_name` LIKE ? OR
					`applicant_contact` LIKE ? OR `applicant_email` LIKE ? OR `applicant_message` LIKE ?)", [
					$s,
					$s,
					$s,
					$s,
					$s,
					$s,
				] );
			}

			$this->DB->orderBy( $args['orderBy'], $args['order'] );

			$date_format        = $this->obj->get_mysql_datetime_format();
			$args['mysql_cols'] .= ", DATE_FORMAT(`read_by_admin_time`, '{$date_format}') read_by_admin_time_formatted";
			$args['mysql_cols'] .= ", DATE_FORMAT(`received_time`, '{$date_format}') received_time_formatted";

			$rows['rows']          = $this->DB->arrayBuilder()
			                                  ->paginate( $this->obj->Tables['job_applications'], $args['page'], $args['mysql_cols'] );
			$rows['page']          = $args['page'];
			$rows['total_pages']   = $this->DB->totalPages;
			$rows['total_records'] = (int) $this->DB->totalCount;
			$stats                 = $this->obj->pagination_stats( $args['page'], $rows['total_pages'], $args['pagesize'], $rows['total_records'] );
			$rows['start']         = $stats['start'];
			$rows['end']           = $stats['end'];
			$rows['from']          = $stats['from'];
			$rows['to']            = $stats['to'];
		} catch ( \Exception $e ) {
			$rows['rows']          = [];
			$rows['page']          = 0;
			$rows['total_pages']   = 0;
			$rows['total_records'] = 0;
			$rows['start']         = 0;
			$rows['end']           = 0;
			$rows['from']          = 0;
			$rows['to']            = 0;
		}

		$Users         = [];
		$wp_upload_dir = wp_upload_dir();
		$Jobs          = new Jobs();
		foreach ( $rows['rows'] as &$row ) {
			if ( ! isset( $Users[ $row['read_by_admin'] ] ) ) {
				$Users[ $row['read_by_admin'] ] = \get_user_by( 'id', $row['read_by_admin'] );
			}
			if ( ! empty( $row['read_by_admin'] ) && isset( $Users[ $row['read_by_admin'] ] ) ) {
				$row['read_by_admin_username'] = $Users[ $row['read_by_admin'] ]->display_name;
			} else {
				$row['read_by_admin_username'] = '';
			}
			$row['status_text'] = $this->get_status_text( $row['status'] );
			$row['avatar']      = get_avatar_url( $row['applicant_email'], [
				'size' => 50,
			] );
			//$row['received_time_ago'] = $this->obj->timeago( $row['received_time'] );
			$row['received_time_ago'] = $row['received_time'];
			$row['received_time_ago'] = human_time_diff( strtotime( $row['received_time'] ), current_time( 'timestamp' ) );

			$row['cv_file'] = $this->get_cv_file( $row['id'] );
			if ( is_file( $row['cv_file'] ) ) {
				$row['cv'] = [
					'name'     => basename( $row['cv_file'] ),
					'path'     => $row['cv_file'],
					'url'      => $this->obj->get_file_url( $row['cv_file'] ),
					'url_real' => $wp_upload_dir['baseurl'] . '/satech_basic_jobs/received/' . $row['id'] . '/' . basename( $row['cv_file'] ),
					'size'     => filesize( $row['cv_file'] ),
					'type'     => mime_content_type( $row['cv_file'] ),
				];
			} else {
				$row['cv'] = [];
			}

			$row['other_files'] = $this->get_other_files( $row['id'] );

			if ( $args['with_apply_link'] ) {
				$row['apply_job_link'] = $Jobs->get_apply_job_link( $row['ad_id'] );
			}
		}

		return $rows;
	}

	/**
	 * Auto delete job ads from trash after 10 days.
	 */
	private function auto_delete() {
		try {
			$this->DB->where( 'deleted', 1 )
			         ->where( 'DATEDIFF(NOW(),deleted_time)', 15, '>' )
			         ->delete( $this->obj->Tables['job_applications'] );
		} catch ( \Exception $e ) {

		}
	}

	/**
	 * Get array of
	 *
	 * @param $id
	 *
	 * @return array
	 */
	public function get_received_job( $id ) {
		$id = (int) $id;

		$wp_upload_dir = wp_upload_dir();
		try {
			$time_format = $this->obj->get_mysql_datetime_format();
			$this->DB->where( 'id', $id );

			$row     = $this->DB->getOne( $this->obj->Tables['job_applications'], "*,DATE_FORMAT(`received_time`, '{$time_format}') received_time2" );
			$cv_file = $this->get_cv_file( $row['id'] );
			if ( is_file( $cv_file ) ) {
				$row['cv'] = [
					'name'     => basename( $cv_file ),
					'path'     => $cv_file,
					'url'      => $this->obj->get_file_url( $cv_file ),
					'url_real' => $wp_upload_dir['baseurl'] . '/satech_basic_jobs/received/' . $row['id'] . '/' . basename( $cv_file ),
					'size'     => filesize( $cv_file ),
					'type'     => mime_content_type( $cv_file ),
				];
			} else {
				$row['cv'] = [];
			}

			$row['other_files']       = $this->get_other_files( $id );
			$row['received_time_ago'] = human_time_diff( strtotime( $row['received_time'] ), current_time( 'timestamp' ) );
			$row['avatar']            = get_avatar_url( $row['applicant_email'], [
				'size' => 128,
			] );
			$row['applicant_message'] = wp_unslash( $row['applicant_message'] );
			$Job                      = new Jobs();
			$row['job_row']           = $Job->get_job( $row['ad_id'] );
			//$row['job_is_active']           = $Job->get_active_status( $row['ad_id'] );
			//$row['job_get_views']           = $Job->get_views( $row['ad_id'] );
			$row['job_applictions_counter'] = $Job->get_application_counts( $row['ad_id'] );
			$row['last_submitted_date']     = $Job->get_last_submitted_application_date( $row['ad_id'] );

			return $row;
		} catch ( \Exception $e ) {
			return [];
		}
	}

	/**
	 * Get array of attached files with an application by applicant other than C.V.
	 *
	 * @param $id
	 *
	 * @return array
	 */
	public function get_other_files( $id ) {
		$id    = (int) $id;
		$array = wp_upload_dir();
		$path  = $array['basedir'] . DIRECTORY_SEPARATOR . 'satech_basic_jobs' . DIRECTORY_SEPARATOR . 'received' . DIRECTORY_SEPARATOR . $id . DIRECTORY_SEPARATOR;
		if ( ! is_dir( $path ) ) {
			return [];
		}

		try {
			$cv_file = $this->DB->where( 'id', $id )->getValue( $this->obj->Tables['job_applications'], 'cv_file' );
		} catch ( \Exception $e ) {
			return [];
		}

		$wp_upload_dir = wp_upload_dir();
		$files         = list_files( $path );

		$out = [];

		foreach ( $files as $file ) {
			if ( basename( $file ) == $cv_file ) {
				continue;
			}

			$out[] = [
				'name'     => basename( $file ),
				'path'     => $file,
				'url'      => $this->obj->get_file_url( $file ),
				'url_real' => $wp_upload_dir['baseurl'] . '/satech_basic_jobs/received/' . $id . '/' . basename( $file ),
				'size'     => filesize( $file ),
				'type'     => mime_content_type( $file ),
			];
		}

		return $out;
	}

	public function get_cv_file( $id ) {
		$id    = (int) $id;
		$array = wp_upload_dir();
		$path  = $array['basedir'] . DIRECTORY_SEPARATOR . 'satech_basic_jobs' . DIRECTORY_SEPARATOR . 'received' . DIRECTORY_SEPARATOR . $id . DIRECTORY_SEPARATOR;
		if ( ! is_dir( $path ) ) {
			return '';
		}

		try {
			$cv_file = $this->DB->where( 'id', $id )->getValue( $this->obj->Tables['job_applications'], 'cv_file' );
		} catch ( \Exception $e ) {
			return '';
		}
		if ( empty( $cv_file ) ) {
			return '';
		}

		return $path . $cv_file;
	}

	public function restore_application( $ids ) {
		try {
			if ( is_array( $ids ) ) {
				foreach ( $ids as $id ) {
					Logs::save_log( 'Application restored from trash', $id );
				}
			} else {
				Logs::save_log( 'Application restored from trash', $ids );
			}

			$this->DB->where( 'id', $ids, ( is_array( $ids ) ? 'IN' : '=' ) )
			         ->update( $this->obj->Tables['job_applications'], [
				         'deleted' => 0,
			         ] );

			return true;
		} catch ( \Exception $e ) {
			throw new \Exception( $e->getMessage() );
		}
	}

	public function delete_received_job( $ids ) {
		try {
			if ( is_array( $ids ) ) {
				foreach ( $ids as $id ) {
					Logs::save_log( 'Application deleted', $id );
				}
			} else {
				Logs::save_log( 'Application deleted', $ids );
			}

			$this->DB->where( 'id', $ids, ( is_array( $ids ) ? 'IN' : '=' ) )
			         ->update( $this->obj->Tables['job_applications'], [
				         'deleted'      => 1,
				         'deleted_by'   => get_current_user_id(),
				         'deleted_time' => current_time( 'mysql' ),
			         ] );
			$this->obj->dump( $this->DB->getLastQuery() );

			return true;
		} catch ( \Exception $e ) {
			throw new \Exception( $e->getMessage() );
		}
	}

	public function mark_as_read( $id ) {
		$id = (int) $id;
		$this->DB->where( 'id', $id )->where( 'status', self::Status['Read'], '<' );

		try {
			$this->DB->update( $this->obj->Tables['job_applications'], [ 'status' => self::Status['Read'] ] );

			$this->DB->where( 'id', $id )
			         ->where( '(read_by_admin IS NULL OR read_by_admin = ?)', [ 0 ] )
			         ->update( $this->obj->Tables['job_applications'], [
				         'read_by_admin'      => 1,
				         'read_by_admin_ip'   => $this->obj->get_ip_address(),
				         'read_by_admin_time' => current_time( 'mysql' ),
			         ] );

			if ( $this->DB->count > 0 ) {
				Logs::save_log( 'Application read', $id );
			}
		} catch ( \Exception $e ) {

		}
	}

	public function get_status_text( $status ) {
		$status = (int) $status;

		$text = array_search( $status, self::Status, true ) ?? 'Unknown';

		if ( $text == 'Interview' ) {
			$text = 'Interview Scheduled';
		}

		return $text;
	}

	/**
	 * This method will return applicatnt email address.
	 *
	 * @param int $applicant_id
	 *
	 * @return string
	 */
	public function get_applicant_email( $applicant_id ): string {
		$applicant_id = (int) $applicant_id;
		try {
			return (string) $this->DB->where( 'id', $applicant_id )
			                         ->getValue( $this->obj->Tables['job_applications'], 'applicant_email' );
		} catch ( \Exception $e ) {
			return '';
		}
	}

	public function delete_auto_jobs() {
		$value = (int) $this->obj->get_option( 'remove_jobs_after_days' );
		if ( $value < 1 ) {
			return null;
		}

		try {
			$this->DB->where( 'DATEDIFF(NOW(),received_time)', $value, '>=' )
			         ->delete( $this->obj->Tables['job_applications'] );
		} catch ( \Exception $e ) {
			// Do nothing.
		}

		return null;
	}

	/**
	 * This method will return job categories available in received jobs
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function get_job_categories() {
		try {
			$rows = $this->DB->orderBy( 'job_category_name', 'ASC' )
			                 ->get( $this->obj->Tables['job_applications'], null, 'DISTINCT job_category_name' );

			return array_column( $rows, 'job_category_name' );
		} catch ( \Exception $e ) {
			return [];
		}
	}

	public function get_applicant_information( $id ) {
		$id = (int) $id;

		try {
			$row = $this->DB->where( 'id', $id )
			                ->getOne( $this->obj->Tables['job_applications'], 'applicant_name name, applicant_contact contact, applicant_email email' );
			if ( ! isset( $row['email'] ) ) {
				$row['error'] = $this->DB->getLastError();
				if ( $row['error'] == '' ) {
					$row['error'] = 'Unknown error';
				}
			}
		} catch ( \Exception $e ) {
			$row['error'] = $e->getMessage();
		}

		return $row;
	}

	/**
	 * Count all job applications.
	 *
	 * @return int
	 * @throws \Exception
	 */
	public function count_all( $args = [] ) {
		$default = [
			'status'  => null,
			'deleted' => 0,
		];
		$args    = \wp_parse_args( $args, $default );

		if ( ! is_null( $args['status'] ) ) {
			$this->DB->where( 'status', $args['status'] );
		}
		if ( ! is_null( $args['deleted'] ) ) {
			$this->DB->where( 'deleted', $args['deleted'] );
		}

		return (int) $this->DB->getValue( $this->obj->Tables['job_applications'], 'COUNT(*)' );
	}

	/**
	 * Get array of all type of counters
	 *
	 * @return array
	 */
	public function count_all_types() {
		$query = "SELECT SUM(IF(deleted=0,1,0)) total,
		SUM(IF(deleted=1,1,0)) deleted";

		foreach ( self::Status as $status => $status_number ) {
			$label = str_replace( ' ', '', strtolower( $status ) );
			$query .= ",SUM(IF(status={$status_number} AND deleted=0,1,0)) status_{$status_number}";
		}

		$query .= " FROM " . $this->obj->Tables['job_applications'] . '';

		try {
			return $row = $this->DB->rawQueryOne( $query );
		} catch ( \Exception $e ) {
			return [];
		}
	}
}