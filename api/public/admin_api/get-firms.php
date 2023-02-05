<?php
include_once('../../core.php');
/**
 *
 * GET /admin/get-firms
 *
 * HEADER Authorization: Bearer
 *
 * @api
 *
 */
class AdminGetFirms extends Endpoints {
	function __construct() {
		global $db, $helper;

		require_method('GET');

		$auth       = authenticate_session(2);
		$admin_guid = $auth['guid'] ?? '';

		$firms = $db->do_select("
			SELECT 
			guid,
			role,
			email,
			pii_data,
			verified,
			created_at
			FROM users
			WHERE role = 'firm'
		") ?? array();

		foreach ($firms as &$firm) {
			$enc_pii  = $firm['pii_data'] ?? '';
			$pii      = $helper->decrypt_pii($enc_pii);

			$firm['name'] = $pii['name'] ?? '';

			$verified = (int)($firm['verified'] ?? 0);

			if (!$verified) {
				$firm['status'] = 'Invited';
			} else {
				$firm['status'] = 'Trial';
				$firm['plan']   = 'Trial';
			}

			$firm['total_reports'] = 0;

			$firm['total_companies'] = 0;

			$firm['total_invoiced'] = 0;
		}

		_exit(
			'success',
			$firms
		);
	}
}
new AdminGetFirms();
