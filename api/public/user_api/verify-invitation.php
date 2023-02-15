<?php
include_once('../../core.php');
/**
 *
 * GET /user/verify-invitation
 *
 * @api
 * @param string $hash  Hash from email link
 *
 */
class UserVerifyInvitation extends Endpoints {
	function __construct(
		$hash = ''
	) {
		global $db, $helper, $authentication;

		require_method('GET');

		$hash = parent::$params['hash'] ?? '';

		$uri  = $helper->aes_decrypt($hash);
		$uri  = explode('::', $uri);
		$guid = $uri[0];
		$code = $uri[1] ?? '';
		$time = (int)($uri[2] ?? 0);

		// check
		$check = $db->do_select("
			SELECT 
			guid, 
			confirmation_code, 
			email
			FROM  users
			WHERE confirmation_code = '$code'
			AND   guid              = '$guid'
			AND   verified          = 0
		");

		if (!$check) {
			_exit(
				'error',
				'Invitation code is invalid',
				403,
				'Invitation code is invalid'
			);
		}

		$email = $check[0]['email'] ?? '';

		// 1 month for registering
		$expire_time = 2592000;

		if ($time < (int)time() - $expire_time) {
			_exit(
				'error',
				'Invitation code is expired. Please try registering again',
				403,
				'Invitation code is expired'
			);
		}

		// for company flow
		$law_firm_name = '';

		$firm_guid = $db->do_select("
			SELECT firm_guid
			FROM firm_company_relations
			WHERE company_guid = '$guid'
		");

		$firm_guid = $firm_guid[0]['firm_guid'] ?? '';
		$firm      = $helper->get_user($firm_guid);
		elog('FIRM');
		elog($firm);

		// tag clicked_invite_at
		$clicked = $db->do_select("
			SELECT clicked_invite_at
			FROM users
			WHERE guid = '$guid'
		");

		$clicked = $clicked[0]['clicked_invite_at'] ?? '';

		if (!$clicked) {
			$now = $helper->get_datetime();
			$db->do_query("
				UPDATE users
				SET clicked_invite_at = '$now'
				WHERE guid = '$guid'
			");
		}

		_exit(
			'success',
			array(
				"email"         => $email,
				"law_firm_name" => $firm['pii_data']['name'] ?? ''
			)
		);
	}
}
new UserVerifyInvitation();