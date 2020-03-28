<?php

namespace SAJobsF\Jobs;

class Logs {

	/**
	 * Saving log into database.
	 *
	 * @param        $log
	 * @param        $data_id
	 * @param string $data_type
	 *
	 * @return string
	 */
	public static function save_log( $log, $data_id, $data_type = 'application' ) {
		global $SAJobsF;

		//$SAJobsF->Tables['logs']
		$data = [
			'data_type' => $data_type,
			'data_id'   => $data_id,
			'log'       => $log,
			'ip'        => $SAJobsF->get_ip_address(),
			'sent_by'   => get_current_user_id(),
			'datetime'  => current_time( 'mysql' ),
		];

		try {
			$SAJobsF->DB2->insert( $SAJobsF->Tables['logs'], $data );

			return 'OK';
		} catch ( \Exception $e ) {
			return $e->getMessage();
		}
	}

	/**
	 * Get logs array from database.
	 *
	 * @param null $data_id
	 * @param null $data_type
	 *
	 * @return array|\MysqliDb
	 */
	public static function get_logs( $data_id = null, $data_type = null ) {
		global $SAJobsF;

		if ( ! is_null( $data_id ) ) {
			$SAJobsF->DB2->where( 'data_id', $data_id );
		}

		if ( ! is_null( $data_type ) ) {
			$SAJobsF->DB2->where( 'data_type', $data_type );
		}

		try {
			$time_format = $SAJobsF->get_mysql_datetime_format();
			$rows        = $SAJobsF->DB2->orderBy( 'id' )
			                          ->get( $SAJobsF->Tables['logs'], null, "*,DATE_FORMAT(datetime, '{$time_format}') datetime_formatted" );

			$names = [];
			foreach ( $rows as &$row ) {
				$row['log'] = wp_unslash( $row['log'] );
				$lines      = explode( '<SATECH></SATECH>', $row['log'] );
				if ( isset( $lines[0] ) ) {
					$row['log_title'] = str_replace( [ '<br>', '<br />' ], ' ', $lines[0] );
				} else {
					$row['log_title'] = $row['log'];
				}
				$row['log_title'] = strip_tags( $row['log_title'] );
				if ( ! isset( $names[ $row['sent_by'] ] ) ) {
					$names[ $row['sent_by'] ] = get_userdata( $row['sent_by'] )->display_name;
				}
				$row['sent_by_name'] = $names[ $row['sent_by'] ];
			}

			return $rows;
		} catch ( \Exception $e ) {
			return [];
		}
	}
}