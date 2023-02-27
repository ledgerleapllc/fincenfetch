<?php
/**
 *
 * Data structure templates for encrypted PII objects
 *
 * @static max_size
 * @static user_info
 * @static firm_info
 * @static report_info
 * @static dba
 * @static applicant
 * @static office
 * @static beneficial_owner
 *
 */
class Structs {
	static $max_size = 64;

	public const user_info = array(
		"name"  => "",
		"ip"    => ""
	);

	public const firm_info = array(
		"name"  => "",
		"phone" => ""
	);

	public const report_info = array(
		"report_year"        => "YYYY",
		"company_name"       => "",
		"dbas"               => array(
			/* hash('dba'.$company_guid.$report_guid.$index) => Structs::dba */
		),
		"office"             => Structs::office,
		"formation_location" => Structs::formation_location,
		"tax_number"         => "",
		"beneficial_owners"  => array(
			/* hash('beneficial_owner'.$company_guid.$report_guid.$index) => Structs::beneficial_owner */
		)
	);

	public const dba = "";

	public const office = array(
		"created_at"        => "YYYY-mm-dd HH:ii:ss UTC",
		"updated_at"        => "YYYY-mm-dd HH:ii:ss UTC",
		"country"           => "",
		"address1"          => "",
		"address2"          => "",
		"city"              => "",
		"state_or_province" => "",
		"postal_code"       => ""
	);

	public const formation_location = array(
		"created_at"        => "YYYY-mm-dd HH:ii:ss UTC",
		"updated_at"        => "YYYY-mm-dd HH:ii:ss UTC",
		"formed_at"         => "YYYY-mm-dd HH:ii:ss UTC",
		"state_or_province" => ""
	);

	public const beneficial_owner = array(
		"created_at"              => "YYYY-mm-dd HH:ii:ss UTC",
		"updated_at"              => "YYYY-mm-dd HH:ii:ss UTC",
		"owner_type"              => "enum[individual, entity]",
		"first_name"              => "",
		"middle_name"             => "",
		"last_name"               => "",
		"suffix"                  => "",
		"dob"                     => "YYYY-mm-dd",
		"is_us_citizen"           => true,
		"country_of_citizenship"  => "",
		"more_than_25"            => true,
		"percent_owned"           => 0.25,
		"important_decisions"     => true,
		"owner_title"             => "",
		"address"                 => array(
			"country"             => "",
			"address1"            => "",
			"address2"            => "",
			"city"                => "",
			"state_or_province"   => "",
			"postal_code"         => ""
		),
		"identifying_document"    => array(
			"type"                => "enum[driver_license, passport, government_issue_id]",
			"number"              => "",
			"issue_date"          => "YYYY-mm-dd",
			"expire_date"         => "YYYY-mm-dd",
			"country_of_issuance" => "",
			"sha256_checksum"     => ""
		)
	);
}

?>