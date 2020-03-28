<?php

namespace SAJobsF\Jobs;

class Category {

	private $DB;
	private $obj;

	public function __construct() {
		global $SAJobsF;

		$this->obj = $SAJobsF;
		$this->DB  = $this->obj->DB2;
	}

	public function save( $args = [] ) {
		$default = [
			'id'        => 0,
			'category'  => '',
			'is_active' => 0,
		];
		$args    = wp_parse_args( $args, $default );

		try {
			$args['category'] = trim( (string) $args['category'] );
			if ( empty( $args['category'] ) ) {
				return esc_attr__( 'Please type category name', $this->obj::DOMAIN );
			}

			if ( empty( $args['id'] ) ) {
				$id = $this->DB->insert( $this->obj->Tables['categories'], $args );

			} else {
				$id = $args['id'];
				$this->DB->where( 'id', $id )->update( $this->obj->Tables['categories'], $args );
			}

			if ( $this->DB->getLastErrno() !== 0 ) {
				return $this->DB->getLastError();
			}

			Logs::save_log( 'Category saved.', $id, 'category' );

			return 'OK';
		} catch ( \Exception $e ) {
			return $e->getMessage();
		}
	}

	public function get_cats( $args = [] ) {
		$default = [
			'order'    => 'DESC',
			'order_by' => 'id',
		];
		$args    = wp_parse_args( $args, $default );

		$query = "SELECT *, (SELECT COUNT(*) FROM `{$this->obj->Tables['job_ads']}` WHERE job_category_id=c.id) job_count FROM {$this->obj->Tables['categories']} c WHERE 1";
		$query .= " ORDER BY {$args['order_by']} {$args['order']}";

		try {
			$rows = $this->obj->DB2->objectBuilder()->rawQuery( $query );
			$rows = wp_unslash($rows);
		} catch ( \Exception $e ) {
			return [];
		}

		return $rows;
	}

	public function get_single_cat( $id ) {
		$id = (int) $id;

		try {
			$row            = $this->DB->where( 'id', $id )->getOne( $this->obj->Tables['categories'] );
			$row->job_count = $this->count_jobs( $row['id'] );
			$row = wp_unslash($row);

			return $row;
		} catch ( \Exception $e ) {
			return $e->getMessage();
		}
	}

	public function delete_cats( $ids ) {
		if ( ! is_array( $ids ) ) {
			$ids = (string) $ids;
			$ids = explode( ',', $ids );
		} else if ( is_object( $ids ) ) {
			$ids = (array) $ids;
		}

		foreach ( $ids as $id ) {
			try {
				if ( $this->DB->where( 'job_category_id', $id )
				              ->getValue( $this->obj->Tables['job_ads'], 'COUNT(*)' ) > 0 ) {
					continue;
				}

				Logs::save_log( 'Category deleted', $id, 'category' );

				$this->DB->where( 'id', $id )->delete( $this->obj->Tables['categories'] );
			} catch ( \Exception $e ) {
				return false;
			}
		}

		return true;
	}

	public function count_jobs( $id ): int {
		$id = (int) $id;

		try {
			return (int) $this->DB->where( 'job_category_id', $id )
			                      ->getValue( $this->obj->Tables['job_ads'], 'count(*)' );
		} catch ( \Exception $e ) {
			return 0;
		}
	}

	public function activate_category( $id ) {
		$id = (int) $id;

		try {
			$this->DB->where( 'id', $id )->update( $this->obj->Tables['categories'], [ 'is_active' => 1 ] );
		} catch ( \Exception $e ) {
			return false;
		}

		Logs::save_log( 'Category activated', $id, 'category' );

		return true;
	}

	public function deactivate_category( $id ) {
		$id = (int) $id;

		try {
			$this->DB->where( 'id', $id )->update( $this->obj->Tables['categories'], [ 'is_active' => 0 ] );
		} catch ( \Exception $e ) {
			return false;
		}

		Logs::save_log( 'Category de-activated', $id, 'category' );

		return true;
	}
}