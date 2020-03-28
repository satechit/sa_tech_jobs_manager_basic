<?php

namespace SAJobsF\Jobs;

class Currency {

	private $DB;
	private $obj;

	public function __construct() {
		global $SAJobsF;

		$this->obj = $SAJobsF;
		$this->DB  = $this->obj->DB2;
	}

	/**
	 * Get all currencies from MySQL table
	 *
	 * @return array
	 */
	public function get_currencies() {
		try {
			return $this->DB->orderBy( 'code', 'ASC' )->get( $this->obj->Tables['currencies'] );
		} catch ( \Exception $e ) {
			return [];
		}
	}

	/**
	 * @param string $saved_symbol
	 *
	 * @return mixed|string
	 */
	public function get_symbol( $saved_symbol = '' ) {
		if ( trim( $saved_symbol ) === '' ) {
			$saved_symbol = (string) $this->obj->get_option( 'currency_code' );
		}

		if ( trim( $saved_symbol ) === '' ) {
			return '$';
		}

		try {
			return $this->DB->where( 'code', $saved_symbol )->getValue( $this->obj->Tables['currencies'], 'symbol' );
		} catch ( \Exception $e ) {
			return '$';
		}
	}


	/**
	 * @return mixed|string
	 * @throws \Exception
	 */
	public function get_code() {
		$saved_symbol = (string) $this->obj->get_option( 'currency_code' );

		if ( trim( $saved_symbol ) === '' ) {
			return 'USD';
		}

		try {
			return (string) $this->DB->where( 'code', $saved_symbol )
			                         ->getValue( $this->obj->Tables['currencies'], 'code' );
		} catch ( \Exception $e ) {
			return 'USD';
		}
	}
}