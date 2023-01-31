<?php
/**
 *
 * Page permissions controller
 *
 */
class Permissions {
	/** 
	 * Known endpoints array.
	 */
	public const endpoints = array(
	);

	function __construct() {
		//
	}

	function __destruct() {
		// do nothing yet
	}

	/**
	 *
	 * Fetches permissions array by user
	 *
	 * @param  string $guid
	 * @return array  $permissions
	 *
	 */
	private static function get_permissions($guid) {
		global $db;

		$permissions = $db->do_select("
			SELECT *
			FROM permissions
			WHERE guid = '$guid'
		");

		if (!$permissions) {
			$db->do_query("
				INSERT INTO permissions (
					guid
				) VALUES (
					'$guid'
				)
			");

			$permissions = $db->do_select("
				SELECT *
				FROM permissions
				WHERE guid = '$guid'
			");
		}

		$permissions = $permissions[0];
		unset($permissions['guid']);

		return $permissions;
	}

	/**
	 *
	 * Determine if a user is able to access the requested endpoint
	 *
	 * @param  string $guid
	 * @return bool   $allowed
	 *
	 */
	public function allowed($guid) {
		$allowed = self::get_permissions($guid);
		$uri     = $_SERVER['REQUEST_URI'] ?? '/';
		$explode = explode('/', $uri);
		$uri     = end($explode);
		$noparam = explode('?', $uri);
		$uri     = $noparam[0];

		foreach ($allowed as $endpoint => $value) {
			if (
				(int)$value === 0 &&
				in_array($uri, self::endpoints[$endpoint])
			) {
				return false;
			}
		}

		return true;
	}
}