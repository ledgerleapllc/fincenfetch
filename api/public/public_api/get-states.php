<?php
/**
 *
 * GET /public/get-states
 *
 * @api
 *
 */
class PublicGetStates extends Endpoints {
	function __construct() {
		global $db, $helper;

		require_method('GET');

		$states = Helper::$states;

		_exit(
			'success',
			$states
		);
	}
}
new PublicGetStates();