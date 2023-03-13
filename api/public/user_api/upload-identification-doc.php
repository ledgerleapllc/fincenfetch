<?php
/**
 *
 * POST /user/upload-identification-doc
 *
 * HEADER Authorization: Bearer
 *
 * @api
 * @param file   $doc
 * @param string $owner
 *
 */
class UserUploadIdentificationDoc extends Endpoints {
	function __construct(
		$doc   = '',
		$owner = ''
	) {
		global $db, $helper, $S3;

		require_method('POST');

		$auth      = authenticate_session(1);
		$user_guid = $auth['guid'] ?? '';
		$doc       = $_FILES['file'] ?? null;
		$owner     = (int)(parent::$params['owner'] ?? 0);
		$type      = $doc['type'] ?? '';
		$name      = $doc['name'] ?? '';
		$tmp_name  = $doc['tmp_name'] ?? '';
		$ext       = pathinfo($name, PATHINFO_EXTENSION);
		$ext       = strtolower($ext);
		$error     = $doc['error'] ?? '';
		$size      = (float)($doc['size'] ?? 0);
		$max_size  = 5000000;

		if ($size > $max_size) {
			_exit(
				'error', 
				'Document is too large. Cannot exceed 5 MB', 
				413, 
				'Document is too large. Cannot exceed 5 MB'
			);
		}

		if ($name && $tmp_name) {
			$hash_name = $helper->generate_hash(16).'.'.$ext;

			try {
				$s3result = $S3->putObject([
					'Bucket'     => S3BUCKET,
					'Key'        => 'identification_docs/'.$hash_name,
					'SourceFile' => $tmp_name
				]);

				$ObjectURL = 'https://'.S3BUCKET.'.s3.'.S3BUCKET_REGION.'.amazonaws.com/identification_docs/'.$hash_name;
			} catch (Exception $e) {
				elog($e);
				$ObjectURL = null;
			}

			if ($ObjectURL) {
				_exit(
					'success',
					array(
						"file_url"  => $ObjectURL,
						"file_name" => $name,
						"owner"     => $owner,
					)
				);
			}
		}

		_exit(
			'error', 
			'There was a problem uploading document at this time. Please try again later', 
			400, 
			'There was a problem uploading document at this time. Please try again later'
		);
	}
}
new UserUploadIdentificationDoc();