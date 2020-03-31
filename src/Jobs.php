<?php

namespace SAJobsF\Jobs;

class Jobs {

	/**
	 * Database object
	 *
	 * @var \MysqliDb
	 */
	private $DB;

	/**
	 * Object for main class of this plugin.
	 *
	 * @var \JobsPManagement
	 */
	private $obj;

	/**
	 * Path where attached files with job ads will be saved.
	 *
	 * @var string
	 */
	public $FilesPath;

	const errors = [
		'Invalid Request' => [
			'code'    => 1,
			'message' => 'Invalid form request.',
		],
	];


	/**
	 * Jobs constructor.
	 */
	public function __construct() {
		global $SAJobsF;

		$this->obj = $SAJobsF;
		$this->DB  = $this->obj->DB2;

		$array           = wp_upload_dir();
		$this->FilesPath = $array['basedir'] . DIRECTORY_SEPARATOR . 'satech_basic_jobs' . DIRECTORY_SEPARATOR;
	}

	/**
	 * Auto delete job ads from trash after 10 days.
	 *
	 * @throws \Exception
	 */
	private function auto_delete() {
		try {
			$this->DB->where( 'deleted', 1 )
			         ->where( 'DATEDIFF(NOW(),deleted_time)', 15, '>' )
			         ->delete( $this->obj->Tables['job_ads'] );
		} catch ( \Exception $e ) {

		}
	}

	/**
	 * @param string $args
	 *
	 * @return array|null|object
	 *
	 * Get database result.
	 */
	public function get_jobs( $args = '' ) {
		$default = [
			'order_by'           => 'id',
			'order'              => 'DESC',
			'is_active'          => null,
			'category_is_active' => null,
			'job_category_id'    => 0,
			'expired'            => null,
			'job_type'           => '',
			'salary_type'        => '',
			'count_applications' => false,
			'search'             => '',
			'deleted'            => 0,

			'with_apply_link' => false,
		];
		$args    = wp_parse_args( $args, $default );

		try {
			// Remove jobs if attached category is removed.
			$extra_ids = $this->getting_jobs_without_category_existance();

			if ( count( $extra_ids ) > 0 ) {
				foreach ( $extra_ids as $extra_id ) {
					$this->remove_files( $extra_id );
				}
				$this->delete_job( $extra_ids, true );
			}
			// End remove jobs

			$mysql_date_format     = $this->obj->get_mysql_date_format();
			$mysql_datetime_format = $this->obj->get_mysql_datetime_format();

			if ( ! is_null( $args['deleted'] ) ) {
				$this->DB->where( 'deleted', $args['deleted'] );
			}

			/**
			 * Check for expired jobs
			 */
			if ( $args['expired'] === '1' || $args['expired'] === true || $args['expired'] === 1 ) {
				$this->DB->where( '`expiry_date` < NOW()' );
			} else if ( $args['expired'] === 0 || $args['expired'] === '0' || $args['expired'] === false ) {
				$this->DB->where( '`expiry_date` >= NOW()' );
			}

			$args['search'] = (string) $args['search'];
			if ( $args['search'] !== '' ) {
				$s = '%' . $args['search'] . '%';
				$this->DB->where( "(`title` LIKE ? OR `description` LIKE ? OR `location` LIKE ? OR `job_type` LIKE ? OR `salary_type` LIKE ? OR `salary` LIKE ?)", [
					$s,
					$s,
					$s,
					$s,
					$s,
					$s,
				] );
			}

			if ( $args['is_active'] === 1 || $args['is_active'] === '1' || $args['is_active'] === true ) {
				$this->DB->where( 'j.`is_active`', 1 );
			} else if ( $args['is_active'] === 0 || $args['is_active'] === '0' || $args['is_active'] === false ) {
				$this->DB->where( 'j.`is_active`', 0 );
			}

			if ( ! is_null( $args['category_is_active'] ) ) {
				$args['category_is_active'] = (int) $args['category_is_active'];
				$this->DB->where( 'c.is_active', $args['category_is_active'] );
			}

			if ( ! empty( $args['job_category_id'] ) ) {
				$this->DB->where( 'job_category_id', $args['job_category_id'] );
			}

			if ( ! empty( $args['job_type'] ) ) {
				$this->DB->where( 'job_type', $args['job_type'] );
			}

			if ( ! empty( $args['salary_type'] ) ) {
				$this->DB->where( 'salary_type', $args['salary_type'] );
			}

			$cols[] = 'j.*';
			$cols[] = 'category';
			$cols[] = "DATE_FORMAT(j.expiry_date, '{$mysql_date_format}') expiry_date_formatted";
			$cols[] = "DATE_FORMAT(j.added_time, '{$mysql_datetime_format}') added_time_formatted";
			$cols[] = 'IF(j.expiry_date < CURRENT_DATE(),\'1\',\'0\') expired';

			$this->DB->orderBy( 'j.' . $args['order_by'], $args['order'] )->where( 'c.id = j.job_category_id' );
			$rows = $this->DB->get( $this->obj->Tables['job_ads'] . ' j,' . $this->obj->Tables['categories'] . ' c', null, implode( ',', $cols ) );
		} catch ( \Exception $e ) {
			return [];
		}

		$Currency = new Currency();
		if ( is_array( $rows ) ) {
			foreach ( $rows as &$row ) {
				$row['files'] = $this->get_files( $row['id'] );

				if ( $args['count_applications'] ) {
					$row['applications'] = $this->count_job_applications( $row['id'] );
				}

				$row['currency_symbol'] = $Currency->get_symbol( $row['currency_code'] );

				if ( $args['with_apply_link'] ) {
					$row['apply_link'] = $this->get_apply_job_link( $row['id'] );
				}
			}
		}

		return $rows;
	}

	/**
	 * Get link for apply job.
	 *
	 * @param $id
	 *
	 * @return string
	 */
	public function get_apply_job_link( $id ) {
		global $wp;

		return add_query_arg( [
			'job_id' => $id,
			'f'      => rawurlencode( base64_encode( home_url( $wp->request ) ) ),
		], get_page_link( $this->obj->get_option( 'job_form_page' ) ) );
	}

	/**
	 * Get job counters with filters.
	 *
	 * @param array $args
	 *
	 * @return int
	 */
	public function count_job_ads( $args = [] ): int {
		$default = [
			'is_active' => null,
			'expired'   => null,
			'deleted'   => 0,
		];
		$args    = wp_parse_args( $args, $default );

		try {
			if ( ! is_null( $args['is_active'] ) ) {
				$this->DB->where( 'is_active', $args['is_active'] );
			}
			if ( ! is_null( $args['expired'] ) && $args['expired'] == 1 ) {
				$this->DB->where( 'expiry_date<NOW()' );
			} else if ( ! is_null( $args['expired'] ) && $args['expired'] == 0 ) {
				$this->DB->where( 'expiry_date>=NOW()' );
			}
			if ( ! is_null( $args['deleted'] ) ) {
				$this->DB->where( 'deleted', $args['deleted'] );
			}

			return (int) $this->DB->getValue( $this->obj->Tables['job_ads'], 'COUNT(*)' );
		} catch ( \Exception $e ) {
			return 0;
		}
	}

	/**
	 * Count job applications.
	 *
	 * @param $job_id
	 *
	 * @return int
	 */
	public function count_job_applications( $job_id ) {
		$job_id = (int) $job_id;
		try {
			return (int) $this->DB->where( 'ad_id', $job_id )
			                      ->getValue( $this->obj->Tables['job_applications'], 'COUNT(*)' );
		} catch ( \Exception $e ) {
			return 0;
		}
	}

	/**
	 * Get a single job, by primary key ID
	 *
	 * @param      $id
	 * @param null $is_active
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function get_job( $id, $is_active = null ) {
		$id = (int) $id;

		$this->DB->join( "{$this->obj->Tables['categories']} c", "c.id=j.job_category_id" )->where( 'j.id', $id );

		//$query = "SELECT j.*,c.category FROM `{$this->obj->table_jobs}` j, `{$this->obj->table_cats}` c WHERE j.`id`={$id} AND c.id = j.job_category_id";

		if ( ! is_null( $is_active ) ) {
			$is_active = (int) $is_active;
			$this->DB->where( 'j.is_active', $is_active )->where( 'c.is_active', $is_active );
			//$query     .= " AND j.`is_active`={$is_active} AND c.is_active={$is_active}";
		}

		$mysql_date_format = $this->obj->get_mysql_date_format();
		$cols[]            = 'j.*';
		$cols[]            = 'c.category';
		$cols[]            = 'c.is_active cat_active';
		$cols[]            = "DATE_FORMAT(j.added_time, '{$mysql_date_format}') added_date";
		$cols[]            = "DATE_FORMAT(j.expiry_date, '{$mysql_date_format}') expiry_date";

		try {
			$row                    = $this->DB->getOne( $this->obj->Tables['job_ads'] . ' j', implode( ',', $cols ) );
			$row['currency_symbol'] = ( new Currency() )->get_symbol( $row['currency_code'] );
		} catch ( \Exception $e ) {
			return [];
		}

		//return json_decode( json_encode( $row ), false );
		return $row;
	}

	/**
	 * @param      $ids
	 *
	 * This method deletes one or more rows from database.
	 * @param bool $remove_permanent
	 *
	 * @throws \Exception
	 */
	public function delete_job( $ids, $remove_permanent = false ) {
		$this->auto_delete();
		if ( ! is_array( $ids ) ) {
			$ids = (string) $ids;
			$ids = explode( ",", $ids );
		}

		foreach ( $ids as $id ) {
			$this->DB->where( 'id', $id );

			try {
				if ( $remove_permanent === true ) {
					$this->DB->where( 'id', $id )->delete( $this->obj->Tables['job_ads'] );
				} else {
					$this->DB->update( $this->obj->Tables['job_ads'], [
						'deleted'      => 1,
						'deleted_by'   => get_current_user_id(),
						'deleted_time' => current_time( 'mysql' ),
					] );

					Logs::save_log( 'Job ad deleted', $id, 'job_ad' );
				}
			} catch ( \Exception $e ) {

			}
		}
	}

	/**
	 * Restore deleted job from trash.
	 *
	 * @param $ids
	 */
	public function restore_job( $ids ) {
		if ( ! is_array( $ids ) ) {
			$ids = (string) $ids;
			$ids = explode( ",", $ids );
		}

		foreach ( $ids as $id ) {
			try {
				$this->DB->where( 'id', $id )->update( $this->obj->Tables['job_ads'], [
					'deleted' => 0,
				] );

				Logs::save_log( 'Job ad restored from trash', $id, 'job_ad' );
			} catch ( \Exception $e ) {

			}
		}
	}

	/**
	 * Saving job ad.
	 *
	 * @param array $args
	 * @param array $files
	 *
	 * @return string|void
	 * @throws \Exception
	 */
	public function save_job( $args = [], $files = [] ) {
		$default = [
			'id'              => 0,
			'job_category_id' => 0,
			'title'           => '',
			'description'     => '',
			'is_active'       => 0,
			'expiry_date'     => null,
			'location'        => '',
		];
		$args    = wp_parse_args( $args, $default );

		$args['id']              = (int) $args['id'];
		$args['job_category_id'] = (int) $args['job_category_id'];
		$args['title']           = wp_unslash( (string) $args['title'] );
		$args['description']     = wp_unslash( (string) $args['description'] );
		$args['is_active']       = (int) $args['is_active'];

		if ( trim( $args['title'] ) == '' ) {
			return esc_attr__( 'Please select job title', $this->obj::DOMAIN );
		}

		if ( trim( $args['description'] ) == '' ) {
			return esc_attr__( 'Please type job description', $this->obj::DOMAIN );
		}

		if ( $args['job_category_id'] < 1 ) {
			return esc_attr__( 'Please select job category', $this->obj::DOMAIN );
		}

		if ( empty( $args['expiry_date'] ) ) {
			return esc_attr__( 'Expiry date for job is missing.', $this->obj::DOMAIN );
		}

		if ( ! empty( $args['salary_type'] ) && empty( $args['salary'] ) ) {
			return esc_attr__( 'Please specify the salary.', $this->obj::DOMAIN );
		}

		if ( empty( $args['salary_type'] ) && ! empty( $args['salary'] ) ) {
			return esc_attr__( 'Please specify salary type.', $this->obj::DOMAIN );
		}

		try {
			$args['expiry_date'] = ( new \DateTime( $args['expiry_date'] ) )->format( 'Y-m-d' );
		} catch ( \Exception $e ) {
			$args['expiry_date'] = null;
		}

		if ( empty( $args['id'] ) ) {
			if ( strtotime( $args['expiry_date'] ) < strtotime( \date( 'd-M-Y' ) ) ) {
				return esc_attr__( 'Please provide a future expiry date.', $this->obj::DOMAIN );
			}

			$args['added_time'] = current_time( 'mysql' );
			try {
				$args['id'] = $this->DB->insert( $this->obj->Tables['job_ads'], $args );
				if ( $args['id'] === false ) {
					return $this->DB->getLastError();
				}

				Logs::save_log( 'New job ad saved', $args['id'], 'job_ad' );
			} catch ( \Exception $e ) {
				//$args['id'] = 0;
				return $e->getMessage();
			}
		} else {
			$args['last_update_time'] = current_time( 'mysql' );
			$this->DB->where( 'id', $args['id'] );

			try {
				$this->DB->update( $this->obj->Tables['job_ads'], $args );

				if ( $this->DB->getLastErrno() !== 0 ) {
					return $this->DB->getLastError();
				}

				Logs::save_log( 'Job ad edited', $args['id'], 'job_ad' );
			} catch ( \Exception $e ) {
				return $e->getMessage();
			}
		}

		if ( isset( $files['file']['error'] ) && $files['file']['error'] == 0 ) {
			$newPath = $this->FilesPath . $args['id'] . DIRECTORY_SEPARATOR . $files['file']['name'];
			wp_mkdir_p( dirname( $newPath ) );
			try {
				\move_uploaded_file( $files['file']['tmp_name'], $newPath );
			} catch ( \Exception $e ) {

			}
			//\WP_Filesystem_Base::rmdir( dirname( $newPath ), true );
		}

		return 'OK';
	}

	/**
	 * @return array
	 *
	 * Get all IDs of jobs where category is removed from database.
	 */
	public function getting_jobs_without_category_existance() {
		$this->DB->where( "job_category_id NOT IN (SELECT `id` FROM `{$this->obj->Tables['categories']}`)" );

		try {
			$rows = $this->DB->get( $this->obj->Tables['job_ads'], null, 'id' );
			if ( count( $rows ) > 0 ) {
				$rows = array_column( $rows, 'id' );
			}

			return $rows;
		} catch ( \Exception $e ) {
			return [];
		}

		//return $this->DB->get_col( "SELECT `id` FROM `{$this->obj->table_jobs}` WHERE `job_category_id` NOT IN (SELECT `id` FROM `{$this->obj->table_cats}`)" );
	}

	/**
	 * @param $id
	 *
	 * Remove all attached files of a job.
	 *
	 * @return bool
	 */
	public function remove_files( $id ): bool {
		$id   = (int) $id;
		$path = $this->FilesPath . $id;

		Logs::save_log( 'Files deleted "' . $path . '"', $id, 'job_ad' );
		if ( is_dir( $path ) ) {
			return $this->obj->delete_folder( $path );
		}

		return true;
	}

	/**
	 * Get attached files with job ad.
	 *
	 * @param $id
	 *
	 * @return array
	 */
	public function get_files( $id ): array {
		$id = (int) $id;

		$path = $this->FilesPath . $id;
		if ( ! is_dir( $path ) ) {
			return [];
		}

		$files = list_files( $path );

		$return_files = [];
		foreach ( $files as $file ) {
			$return_files[] = [
				'path' => $file,
				'url'  => $this->obj->get_file_url( $file ),
				'size' => filesize( $file ),
				'name' => basename( $file ),
				'type' => mime_content_type( $file ),
			];
		}

		return $return_files;
	}

	/**
	 * Submit job by applicant from website and save into database.
	 *
	 * @param array $args
	 * @param array $files
	 *
	 * @return string|void
	 * @throws \Exception
	 */
	public function submit_job_by_applicant( $args = [], $files = [] ) {
		$default = [
			'job_id'  => 0,
			'name'    => '',
			'contact' => '',
			'email'   => '',
			'message' => '',
		];
		$args    = wp_parse_args( $args, $default );

		$job_data = $this->get_job( $args['job_id'] );

		if ( ! isset( $job_data['id'] ) ) {
			return esc_attr__( 'Job not found', $this->obj::DOMAIN );
		}

		if ( trim( $args['name'] ) == '' ) {
			return esc_attr__( 'Your name is missing', $this->obj::DOMAIN );
		}

		if ( trim( $args['contact'] ) == '' ) {
			return esc_attr__( 'Please provide your contact number', $this->obj::DOMAIN );
		}

		if ( ! is_email( $args['email'] ) ) {
			return esc_attr__( 'Please provide your valid email address.', $this->obj::DOMAIN );
		}

		$data = [
			'job_category_name' => $job_data['category'],
			'job_title'         => $job_data['title'],
			'applicant_name'    => $args['name'],
			'applicant_contact' => $args['contact'],
			'applicant_email'   => $args['email'],
			'applicant_message' => $args['message'],
			'received_time'     => current_time( 'mysql' ),
			'received_ip'       => $this->obj->get_ip_address(),
			'ad_id'             => $args['job_id'],
		];

		try {
			$id = $this->DB->insert( $this->obj->Tables['job_applications'], $data );
		} catch ( \Exception $e ) {
			return $e->getMessage();
		}

		if ( ! $id ) {
			try {
				return $this->DB->getLastError();
			} catch ( \Exception $e ) {
				return $e->getMessage();
			}
		}

		/**
		 * Saving uploaded/attached CV.
		 */
		$attached_files = [];
		if ( isset( $files['cv_file']['error'] ) && $files['cv_file']['error'] === 0 ) {
			$file = $files['cv_file'];

			$path = $this->FilesPath . 'received' . DIRECTORY_SEPARATOR . $id . DIRECTORY_SEPARATOR . $file['name'];
			if ( ! is_dir( dirname( $path ) ) ) {
				wp_mkdir_p( dirname( $path ) );
			}
			@move_uploaded_file( $file['tmp_name'], $path );

			if ( is_file( $path ) ) {
				$this->DB->where( 'id', $id )
				         ->update( $this->obj->Tables['job_applications'], [ 'cv_file' => basename( $path ) ] );
				$attached_files[] = $path;
			}
		}

		if ( isset( $files['other_files']['error'] ) && is_array( $files['other_files']['error'] ) ) {
			for ( $x = 0; $x < count( $files['other_files']['error'] ); $x++ ) {
				if ( $files['other_files']['error'][ $x ] === 0 ) {
					$path = $this->FilesPath . 'received' . DIRECTORY_SEPARATOR . $id . DIRECTORY_SEPARATOR . $files['other_files']['name'][ $x ];
					if ( ! is_dir( dirname( $path ) ) ) {
						wp_mkdir_p( dirname( $path ) );
					}
					@move_uploaded_file( $files['other_files']['tmp_name'][ $x ], $path );

					if ( is_file( $path ) ) {
						$attached_files[] = $path;
					}
				}
			}
		}

		return 'OK' . $id;
	}

	/**
	 * Get all available job types.
	 *
	 * @return array
	 */
	public function get_job_types() {
		return [
			'Full Time',
			'Part Time',
			'Remote',
			'Home Based',
			'Freelance',
		];
	}

	/**
	 * Get all available salary types.
	 *
	 * @return array
	 */
	public function get_salary_types() {
		return [ 'Monthly', 'Annually', 'Hourly', 'Project Based', 'Per Word' ];
	}

	/**
	 * Get saved jobs list template
	 *
	 * @return mixed|null
	 */
	public function get_jobs_list_template() {
		$template = $this->obj->get_option( 'default_jobs_template' );

		return $template;
	}

	/**
	 * Update views of database.
	 *
	 * @param $job_id
	 *
	 * @throws \Exception
	 */
	public function view_update( $job_id ) {
		$job_id = (int) $job_id;
		//$this->DB->where( 'id', $job_id )->update( $this->obj->Tables['job_ads'], [ '`views`' => '`views` + 1' ] );

		//$this->DB->rawQuery( 'UPDATE ? SET `views`=`views`+1 WHERE `id`=?', [ $this->obj->Tables['job_ads'], $job_id ] );
		try {
			$this->DB->query( "UPDATE `{$this->obj->Tables['job_ads']}` SET `views`=`views`+1 WHERE `id`={$job_id}" );
		} catch ( \Exception $e ) {

		}
	}

	public function get_error_code( $message = '' ) {
		return self::errors[ $message ]['code'] ?? 0;
	}

	/**
	 * @param $code
	 *
	 * @return mixed|string
	 */
	public function get_error_message( $code ) {
		foreach ( self::errors as $key => $error ) {
			if ( $error['code'] == 1 ) {
				return $error['message'];
			}
		}

		return '';
	}

	/**
	 * Change activation status of job ad.
	 *
	 * @param $id
	 * @param $value
	 *
	 * @return bool
	 */
	public function change_activation( $id, $value ) {
		$id    = (int) $id;
		$value = (int) $value;

		if ( ! ( $value === 1 || $value === 0 ) ) {
			return false;
		}

		try {
			$response = $this->DB->where( 'id', $id )
			                     ->update( $this->obj->Tables['job_ads'], [ 'is_active' => $value ] );
			Logs::save_log( 'Status changed to ' . $value, $id, 'job_ad' );

			return $response;
		} catch ( \Exception $e ) {
			return false;
		}
	}

	/**
	 * Get is_active or not
	 *
	 * @param $id
	 *
	 * @return int
	 */
	public function get_active_status( $id ) {
		$id = (int) $id;

		try {
			return (int) $this->DB->where( 'id', $id )->getValue( $this->obj->Tables['job_ads'], 'is_active', 1 );
		} catch ( \Exception $e ) {
			return 0;
		}
	}

	/**
	 * Get views of job
	 *
	 * @param $id
	 *
	 * @return int
	 */
	public function get_views( $id ) {
		$id = (int) $id;

		try {
			return (int) $this->DB->where( 'id', $id )->getValue( $this->obj->Tables['job_ads'], 'views', 1 );
		} catch ( \Exception $e ) {
			return 0;
		}
	}

	/**
	 * Count received applications.
	 *
	 * @param $id
	 *
	 * @return int
	 */
	public function get_application_counts( $id ) {
		$id = (int) $id;

		try {
			return (int) $this->DB->where( 'ad_id', $id )
			                      ->getValue( $this->obj->Tables['job_applications'], 'COUNT(*)', 1 );
		} catch ( \Exception $e ) {
			return 0;
		}
	}

	public function get_last_submitted_application_date( $id ) {
		$id = (int) $id;

		$mysql_date_format = $this->obj->get_mysql_date_format();
		$cols[]            = "DATE_FORMAT(j.received_time, '{$mysql_date_format}') received_date";

		try {
			return (string) $this->DB->where( 'ad_id', $id )
			                         ->orderBy( 'received_time', 'DESC' )
			                         ->getValue( $this->obj->Tables['job_applications'], "DATE_FORMAT(received_time, '{$mysql_date_format}')", 1 );
		} catch ( \Exception $e ) {
			return null;
		}
	}
}