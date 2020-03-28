<?php

namespace SAJobsF\Jobs;

class EmailLogs {

	private $DB;
	private $obj;

	public function __construct() {
		global $SAJobsF;

		$this->obj = $SAJobsF;
		$this->DB  = $this->obj->DB2;
	}

	/**
	 * Get email logs.
	 *
	 * @param array $args
	 *
	 * @return array|\MysqliDb
	 * @throws \Exception
	 */
	public function get_email_logs( $args = [] ) {
		$default = [
			'application_id' => 0,
			'orderby'        => 'id',
			'order'          => 'DESC',
		];
		$args    = wp_parse_args( $args, $default );

		if ( ! empty( $args['application_id'] ) ) {
			$this->DB->where( 'application_id', $args['application_id'] );
		}

		$this->DB->orderBy( $args['orderby'], $args['order'] );
		$mysql_datetime_format = $this->obj->get_mysql_datetime_format();

		$rows = $this->DB->get( $this->obj->Tables['email_logs'], null, "*, DATE_FORMAT(datetime, '{$mysql_datetime_format}') datetime_formatted" );


		$collected_users = [];
		foreach ( $rows as &$row ) {
			if ( $row['sent_by'] > 0 ) {
				if ( ! isset( $collected_users[ $row['sent_by'] ] ) ) {
					$collected_users[ $row['sent_by'] ] = get_user_by( 'id', $row['sent_by'] );
				}

				$row['sent_by_username'] = $collected_users[ $row['sent_by'] ]->user_login;
			} else {
				$row['sent_by_username'] = '';
			}
		}

		return $rows;
	}

}