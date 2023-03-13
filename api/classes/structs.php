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
		"company_name"               => "",
		"foreign_investment"         => false,
		"exempt"                     => false,
		"request_fincen_number"      => false,
		"has_dbas"                   => null,
		"dbas"                       => array(
			/* hash('dba'.$company_guid.$report_guid.$index) => Structs::dba */
		),
		"tax_number_type"            => "",
		"tax_number"                 => "",
		"foreign_tax_number_country" => "",
		"company_origination_type"   => "",
		"state_of_formation"         => "",
		"tribe_of_formation"         => "",
		"state_of_registration"      => "",
		"tribe_of_registration"      => "",
		"formation_date"             => "",
		"us_office_address_1"        => "",
		"us_office_address_2"        => "",
		"us_office_city"             => "",
		"us_office_state"            => "",
		"us_office_zip"              => "",
		"company_before_2024"        => null,
		"applicant_needed"           => null,
		"applicants"                 => array(
			/* hash('applicant'.$company_guid.$report_guid.$index) => Structs::applicant */
		),
		"beneficial_owners"          => array(
			/* hash('beneficial_owner'.$company_guid.$report_guid.$index) => Structs::beneficial_owner */
		)
	);

	public const dba = "";

	public const applicant = array(
		"created_at"           => "",
		"updated_at"           => "",
		"has_fincen_id"        => false,
		"fincen_id"            => "",
		"first_name"           => "",
		"middle_name"          => "",
		"last_name"            => "",
		"suffix"               => "",
		"date_of_birth"        => "",
		"address_type"         => "",
		"country"              => "",
		"us_address_1"         => "", 
		"us_address_2"         => "",
		"us_city"              => "",
		"us_state"             => "",
		"us_zip"               => "",
		"foreign_address_1"    => "", 
		"foreign_address_2"    => "",
		"foreign_city"         => "",
		"foreign_province"     => "",
		"foreign_postalcode"   => "",
		"identifying_document" => array(
			/* Structs::identifying_document */
		)
	);

	public const beneficial_owner = array(
		"created_at"           => "",
		"updated_at"           => "",
		"has_fincen_id"        => false,
		"fincen_id"            => "",
		"is_exempt_entity"     => false,
		"exempt_entity_name"   => "",
		"first_name"           => "",
		"middle_name"          => "",
		"last_name"            => "",
		"suffix"               => "",
		"date_of_birth"        => "",
		"country"              => "",
		"us_address_1"         => "", 
		"us_address_2"         => "",
		"us_city"              => "",
		"us_state"             => "",
		"us_zip"               => "",
		"foreign_address_1"    => "", 
		"foreign_address_2"    => "",
		"foreign_city"         => "",
		"foreign_province"     => "",
		"foreign_postalcode"   => "",
		"identifying_document" => array(
			/* Structs::identifying_document */
		)
	);

	public const identifying_document = array(
		"type"                    => "enum[drivers_license, state_or_tribe_id, us_passport, foreign_passport]",
		"document_number"         => "",
		"drivers_license_state"   => "",
		"id_card_state"           => "",
		"id_card_tribe"           => "",
		"id_card_state_or_tribe"  => "",
		"foreign_passport_issuer" => "",
		"file_url"                => "",
		"file_name"               => ""
	);
}

?>