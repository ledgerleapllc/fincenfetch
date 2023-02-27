<?php
/**
 *
 * GET /public/decrypt
 *
 * @api
 *
 */
class PublicDecrypt extends Endpoints {
	function __construct() {
		global $db, $helper;

		require_method('GET');

		if (!DEV_MODE) {
			_exit(
				'error',
				'Disabled',
				400,
				'Decryption disabled'
			);
		}

		$enc     = parent::$params['s'] ?? '';
		$decrypt = $helper->aes_decrypt($enc);

		try {
			$json = json_decode($decrypt);
		} catch (Exception $e) {
			$json = null;
		}

		if ($json) {
			$decrypt = $json;
		}

		_exit(
			'success',
			$decrypt
		);
	}
}
new PublicDecrypt();