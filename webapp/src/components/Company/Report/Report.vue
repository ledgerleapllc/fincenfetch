<script>

import App from '../../../App.vue';
import { api } from '../../api.js';
import ClipLoader from 'vue-spinner/src/ClipLoader.vue';
import moment from 'moment';
import { Modal } from 'vue-neat-modal';
import { AgGridVue } from "ag-grid-vue3";
import Datepicker from '@vuepic/vue-datepicker';
import Introduction from './sections/Introduction.vue';
import CompanyName from './sections/CompanyName.vue';
import Dbas from './sections/Dbas.vue';
import TaxNumber from './sections/TaxNumber.vue';
import FormationLocation from './sections/FormationLocation.vue';
import Address from './sections/Address.vue';
import Applicants from './sections/Applicants.vue';
import BeneficialOwners from './sections/BeneficialOwners.vue';
import IdentificationDocs from './sections/IdentificationDocs.vue';
import Review from './sections/Review.vue';

import "ag-grid-community/styles/ag-grid.css";
import "ag-grid-community/styles/ag-theme-alpine.css";
import "../../ag-theme-custom.css";

import '@vuepic/vue-datepicker/dist/main.css'

import 'vue-neat-modal/dist/vue-neat-modal.css';

export default {
	data() {
		return {
			report_guid:       this.$route.params.report_guid,
			uri_category:      this.$route.params.category,
			report:            {
				report_guid:     '',
				company_guid:    '',
				firm_guid:       '',
				firm_email:      '',
				report_type:     '',
				status:          '',
				link_sent:       '',
				link_clicked:    '',
				report_started:  '',
				report_returned: '',
				report_reviewed: '',
				report_review_complete: '',
				marked_filed:    false,
				pii_data:      {
					company_name:               '',
					foreign_investment:         false,
					exempt:                     false,
					request_fincen_number:      false,
					has_dbas:                   null,
					dbas:                       [],
					tax_number_type:            '',
					tax_number:                 '',
					foreign_tax_number_country: '',
					company_origination_type:   '',
					state_of_formation:         '',
					tribe_of_formation:         '',
					state_of_registration:      '',
					tribe_of_registration:      '',
					formation_date:             '',
					us_office_address_1:        '',
					us_office_address_2:        '',
					us_office_city:             '',
					us_office_state:            '',
					us_office_zip:              '',
					company_before_2024:        null,
					applicant_needed:           null,
					applicants:                 [],
					beneficial_owners:          []
				},
				company:         {
					guid:        '',
					role:        'company',
					email:       '',
					pii_data:    {
						name:    '',
						type:    '',
						phone:   ''
					},
					status:      ''
				}
			},
			report_default:             {
				report_guid:     '',
				company_guid:    '',
				firm_guid:       '',
				firm_email:      '',
				report_type:     '',
				status:          '',
				link_sent:       '',
				link_clicked:    '',
				report_started:  '',
				report_returned: '',
				report_reviewed: '',
				report_review_complete: '',
				marked_filed:    false,
				pii_data:      {
					company_name:               '',
					foreign_investment:         false,
					exempt:                     false,
					request_fincen_number:      false,
					has_dbas:                   null,
					dbas:                       [],
					tax_number_type:            '',
					tax_number:                 '',
					foreign_tax_number_country: '',
					company_origination_type:   '',
					state_of_formation:         '',
					tribe_of_formation:         '',
					state_of_registration:      '',
					tribe_of_registration:      '',
					formation_date:             '',
					us_office_address_1:        '',
					us_office_address_2:        '',
					us_office_city:             '',
					us_office_state:            '',
					us_office_zip:              '',
					company_before_2024:        null,
					applicant_needed:           null,
					applicants:                 [],
					beneficial_owners:          []
				},
				company:         {
					guid:        '',
					role:        'company',
					email:       '',
					pii_data:    {
						name:    '',
						type:    '',
						phone:   ''
					},
					status:      ''
				}
			},
			loading:       true,
			loading_init:  true,
			quickFilterText:     "",
			quickFilterCategory: "",
			gridApi:             null,
			defaultColDef: {
				flex:      1,
				minWidth:  100,
				resizable: true,
			},

			// progress tracking
			progress: {
				clicked:              false,
				intro:                false,
				company_name:         false,
				dbas:                 false,
				tax_number:           false,
				formation_location:   false,
				address:              false,
				applicants:           false,
				beneficial_owners:    false,
				identification_docs1: false,
				identification_docs2: false,
				review:               false
			},
			progress_total:   0,
			progressBarWidth: 0 + '%',
			affirmation:      false,

			// dbas clone
			dbas_clone: [],

			// applicant_clone
			applicants_clone:  [],
			applicant_default: {
				created_at:           '',
				updated_at:           '',
				fincen_id:            '',
				first_name:           '',
				middle_name:          '',
				last_name:            '',
				suffix:               '',
				date_of_birth:        '',
				address_type:         'business',
				country:              'United States',
				us_address_1:         '',
				us_address_2:         '',
				us_city:              '',
				us_state:             '',
				us_zip:               '',
				foreign_address_1:    '', 
				foreign_address_2:    '',
				foreign_city:         '',
				foreign_province:     '',
				foreign_postalcode:   '',
				identifying_document: {
					type:                    '',
					document_number:         '',
					drivers_license_state:   '',
					id_card_state:           '',
					id_card_tribe:           '',
					foreign_passport_issuer: '',
					file_url:                '',
					file_name:               ''
				}
			},

			// beneficial_owners_clone
			beneficial_owners_clone:  [],
			beneficial_owner_default: {
				created_at:           '',
				updated_at:           '',
				has_fincen_id:        false,
				fincen_id:            '',
				is_exempt_entity:     false,
				exempt_entity_name:   '',
				first_name:           '',
				middle_name:          '',
				last_name:            '',
				suffix:               '',
				date_of_birth:        '',
				country:              'United States',
				us_address_1:         '',
				us_address_2:         '',
				us_city:              '',
				us_state:             '',
				us_zip:               '',
				foreign_address_1:    '', 
				foreign_address_2:    '',
				foreign_city:         '',
				foreign_province:     '',
				foreign_postalcode:   '',
				identifying_document: {
					type:                    '',
					document_number:         '',
					drivers_license_state:   '',
					id_card_state:           '',
					id_card_tribe:           '',
					foreign_passport_issuer: '',
					file_url:                '',
					file_name:               ''
				}
			},

			// formation
			formation_date: new Date(),

			// applicant fincenID check
			applicant_has_fincen_id: false,
			applicant_fincen_id:     '',

			// docs check
			applicants_requiring_docs: 0,
			beneficial_owners_requiring_docs: 0,
			identifying_document_default: {
				'type':                    '',
				'document_number':         '',
				'drivers_license_state':   '',
				'id_card_state':           '',
				'id_card_tribe':           '',
				'id_card_state_or_tribe':  'state',
				'foreign_passport_issuer': '',
				'file_url':                '',
				'file_name':               ''
			},

			// state/tribe
			state_or_tribe: 'state',

			// address_types
			address_types: [
				'personal',
				'business'
			],

			// report types
			report_types: [
				'initial',
				'updated',
				'correction'
			],

			// suffix types
			suffixs: [
				'Mr.',
				'Mrs.',
				'Miss',
				'Dr.'
			],

			// document types
			document_types: {
				'drivers_license': 'State Issued Drivers License',
				'state_or_tribe_id': 'State/Tribe Issued ID Card',
				'us_passport': 'US Passport',
				'foreign_passport': 'Foreign Passport'
			},

			// US states
			us_states: [],

			// Countries
			countries: []
		}
	},

	components: {
		ClipLoader,
		AgGridVue,
		Modal,
		Datepicker,

		// sections
		Introduction,
		CompanyName,
		Dbas,
		TaxNumber,
		FormationLocation,
		Address,
		Applicants,
		BeneficialOwners,
		IdentificationDocs,
		Review
	},

	created() {
		this.getReport();
		this.getCountries();
		this.getUSStates();
	},

	mounted() {
		let that = this;

		if (
			!this.uri_category ||
			this.uri_category == ''
		) {
			this.$root.routeTo(`/c/report/${this.report_guid}/intro`);
			return false;
		}

		if (
			this.uri_category != 'intro' &&
			this.uri_category != 'company-name' &&
			this.uri_category != 'dbas' &&
			this.uri_category != 'tax-number' &&
			this.uri_category != 'formation-location' &&
			this.uri_category != 'address' &&
			this.uri_category != 'applicants' &&
			this.uri_category != 'beneficial-owners' &&
			this.uri_category != 'identification-docs' &&
			this.uri_category != 'review'
		) {
			this.uri_category = 'intro';
			this.$root.routeTo(`/c/report/${this.report_guid}/intro`);
		}
	},

	watch: {
		quickFilterCategory: "quickFilterCategorySelect",
		'$route' (to, from) {
			this.report_guid  = this.$route.params.report_guid;
			this.uri_category = this.$route.params.category;
		},
		'progress.clicked':              "calculateProgress",
		'progress.intro':                "calculateProgress",
		'progress.company_name':         "calculateProgress",
		'progress.dbas':                 "calculateProgress",
		'progress.tax_number':           "calculateProgress",
		'progress.formation_location':   "calculateProgress",
		'progress.address':              "calculateProgress",
		'progress.applicants':           "calculateProgress",
		'progress.beneficial_owners':    "calculateProgress",
		'progress.identification_docs1': "calculateProgress",
		'progress.identification_docs2': "calculateProgress",
		'progress.review':               "calculateProgress"
	},

	methods: {
		onGridReady(params) {
			this.gridApi = params.api;
		},

		downloadCsv() {
			this.gridApi.exportDataAsCsv({
				fileName: `report-${this.report_guid.format('YYYY-MM-DD')}`
			});
		},

		quickFilterCategorySelect(filter) {
			this.gridApi.setFilterModel({
				source: {
					filterType: 'text',
					type: 'contains',
					filter: filter
				}
			});
		},

		async getCountries() {
			const response = await api(
				'GET',
				'public/get-countries',
				{},
				this.$root.bearer_token
			);

			this.$root.catch401(response);

			if (response.status == 200) {
				this.countries = response.detail;
			}
		},

		async getUSStates() {
			const response = await api(
				'GET',
				'public/get-states',
				{},
				this.$root.bearer_token
			);

			this.$root.catch401(response);

			if (response.status == 200) {
				this.us_states = response.detail;
			}
		},

		async getReport() {
			let fetch_bearer_token = this.$cookies.get('bearer_token');

			this.dbas_clone              = [];
			this.applicants_clone        = [];
			this.beneficial_owners_clone = [];

			this.applicants_requiring_docs        = 0;
			this.beneficial_owners_requiring_docs = 0;

			const response = await api(
				'GET',
				'user/get-report',
				{
					report_guid: this.report_guid
				},
				fetch_bearer_token
			);

			this.$root.catch401(response);



			if (response.status == 200) {
				this.report = response.detail ?? this.report_default;

				// calculate progress by checking each section
				// there are 11 sections so far.

				// 1. clicked
				if (this.report.clicked) {
					this.progress.clicked = true;
				}

				// 2. intro
				if (this.report.status != 'start') {
					this.progress.intro = true;
				}

				// 3. company_name
				if (
					this.report.pii_data.company_name &&
					this.report.pii_data.company_name != ''
				) {
					this.progress.company_name = true;
				}

				// 4. dbas
				if (this.report.pii_data.has_dbas === false) {
					this.progress.dbas = true;
				}

				if (this.report.pii_data.has_dbas === true) {
					if (
						this.report.pii_data.dbas &&
						Object.keys(this.report.pii_data.dbas).length > 0
					) {
						this.progress.dbas = true;

						Object.values(this.report.pii_data.dbas)
						.forEach((value, index) => {
							this.dbas_clone.push(value);
						});
					}

					else {
						this.progress.dbas = false;
					}
				}

				// 5. tax_number
				if (
					this.report.pii_data.tax_number &&
					this.report.pii_data.tax_number != ''
				) {
					this.progress.tax_number = true;
				}

				// 6. formation_location
				if (
					this.report.pii_data.state_of_formation ||
					this.report.pii_data.tribe_of_formation ||
					this.report.pii_data.state_of_registration ||
					this.report.pii_data.tribe_of_registration
				) {
					this.formation_date = this.report.pii_data.formation_date;
					this.progress.formation_location = true;

					if (
						this.report.pii_data.tribe_of_formation ||
						this.report.pii_data.tribe_of_registration
					) {
						this.state_or_tribe = 'tribe';
					}
				}

				// 7. office address
				if (this.report.pii_data.us_office_address_1) {
					this.progress.address = true;
				}

				// 8. applicants
				let applicants = Object.values(this.report.pii_data.applicants);

				// applicants are optional
				if (this.report.pii_data.applicant_needed === false) {
					this.progress.applicants = true;
				}

				if (
					this.report.pii_data.applicant_needed &&
					applicants.length > 0
				) {
					this.progress.applicants = true;

					applicants.forEach((applicant, index) => {
						// fincen ID
						if (applicant.has_fincen_id) {
							this.applicant_has_fincen_id = true;
							this.applicant_fincen_id     = applicant.fincen_id;
						}

						// check if requires doc upload
						if (applicant.has_fincen_id === false) {
							this.applicants_requiring_docs++;
						}

						// push to cloned object
						this.applicants_clone.push(applicant);
					});
				}

				// 9. beneficial_owners
				let owners = Object.values(this.report.pii_data.beneficial_owners);

				if (owners.length > 0) {
					this.progress.beneficial_owners = true;

					owners.forEach((owner, index) => {
						// check if requires doc upload
						if (
							owner.has_fincen_id ||
							owner.is_exempt_entity
						) {} else {
							this.beneficial_owners_requiring_docs++;
						}

						// push to cloned object
						this.beneficial_owners_clone.push(owner);
					});
				}

				// 10. identification docs

				// applicants docs
				if (this.report.pii_data.applicant_needed === false) {
					this.progress.identification_docs1 = true;
				}

				if (
					this.report.pii_data.applicant_needed &&
					applicants.length > 0
				) {
					// set to pass, and subtract
					let applicant_docs_ok = true;

					applicants.forEach((applicant, index) => {
						let id = applicant.identifying_document;

						if (
							!id.type ||
							!id.document_number
						) {
							applicant_docs_ok = false;
						}

						// revert to true if applicant is filled using fincen_id
						if (applicant.has_fincen_id) {
							applicant_docs_ok = true;
						}
					});

					this.progress.identification_docs1 = applicant_docs_ok;
				}

				// beneficial_owners docs
				if (owners.length > 0) {
					// set to pass, and subtract
					let owner_docs_ok = true;

					owners.forEach((owner, index) => {
						let id = owner.identifying_document;

						if (
							!id.type ||
							!id.document_number
						) {
							owner_docs_ok = false;
						}

						// revert to true if owner is exempt
						if (owner.is_exempt_entity) {
							owner_docs_ok = true;
						}

						// revert to true if owner is filled using fincen_id
						if (owner.has_fincen_id) {
							owner_docs_ok = true;
						}
					});

					this.progress.identification_docs2 = owner_docs_ok;
				}

				// 11. review
				if (this.report.report_returned) {
					this.progress.review = true;

					// if (this.uri_category != 'review') {
					// 	this.$root.routeTo(`/c/report/${this.report_guid}/review`);
					// }
				}

				// done
				this.loading      = false;
				this.loading_init = false;

				return true;
			} 

			else {
				this.loading      = false;
				this.loading_init = false;
				this.$root.toast(
					'',
					response.message,
					'error'
				);

				this.$root.routeTo('/c/reports');
			}
		},

		addDBA() {
			let len  = Object.keys(this.dbas_clone).length;
			let stop = false;

			this.dbas_clone.forEach((key, index) => {
				if (
					!this.dbas_clone[index] ||
					this.dbas_clone[index] == ""
				) {
					stop = true;
				}
			});

			if (!stop) {
				this.dbas_clone[len] = "";
			}
		},

		removeDBA(index) {
			this.dbas_clone.splice(index, 1);
		},

		addBeneficialOwner() {
			let len = Object.keys(this.beneficial_owners_clone).length;

			this.beneficial_owners_clone[len] = JSON.parse(JSON.stringify(this.beneficial_owner_default));
		},

		removeBeneficialOwner(index) {
			this.beneficial_owners_clone.splice(index, 1);
		},

		addApplicant() {
			let len = Object.keys(this.applicants_clone).length;

			this.applicants_clone[len] = JSON.parse(JSON.stringify(this.applicant_default));
		},

		removeApplicant(index) {
			this.applicants_clone.splice(index, 1);
		},

		async saveAndExit() {
			if (this.uri_category == 'company-name') {
				await this.saveProgress('company_name');
			}

			if (this.uri_category == 'dbas') {
				await this.saveProgress('dbas');
			}

			if (this.uri_category == 'tax-number') {
				await this.saveProgress('tax_number');
			}

			if (this.uri_category == 'address') {
				await this.saveProgress('address');
			}

			if (this.uri_category == 'formation-location') {
				await this.saveProgress('formation_location');
			}

			if (this.uri_category == 'beneficial-owners') {
				await this.saveProgress('beneficial_owners');
			}

			if (this.uri_category == 'identification-docs') {
				await this.saveProgress('identification_docs');
			}

			this.routeToReports();
		},

		routeToReports() {
			window.location.href = '/c/reports';
		},

		calculateProgress() {
			let progress_keys = Object.keys(this.progress);
			let numerator     = 0;

			progress_keys.forEach((key, index) => {
				if (this.progress[key] === true) {
					numerator++;
				}
			});

			this.progress_total = (
				numerator / 
				progress_keys.length
			 * 100).toFixed(0);

			this.progressBarWidth = this.progress_total + '%';
		},

		pickupBookmark() {
			if (!this.progress.company_name) {
				this.uri_category = 'company-name';
				this.$root.routeTo(`/c/report/${this.report_guid}/company-name`);
				return;
			}

			if (!this.progress.dbas) {
				this.$root.routeTo(`/c/report/${this.report_guid}/dbas`);
				return;
			}

			if (!this.progress.tax_number) {
				this.$root.routeTo(`/c/report/${this.report_guid}/tax-number`);
				return;
			}

			if (!this.progress.formation_location) {
				this.$root.routeTo(`/c/report/${this.report_guid}/formation-location`);
				return;
			}

			if (!this.progress.address) {
				this.$root.routeTo(`/c/report/${this.report_guid}/address`);
				return;
			}

			if (!this.progress.applicants) {
				this.$root.routeTo(`/c/report/${this.report_guid}/applicants`);
				return;
			}

			if (!this.progress.beneficial_owners) {
				this.$root.routeTo(`/c/report/${this.report_guid}/beneficial-owners`);
				return;
			}

			if (
				!this.progress.identification_docs1 ||
				!this.progress.identification_docs2
			) {
				this.$root.routeTo(`/c/report/${this.report_guid}/identification-docs`);
				return;
			}

			if (!this.progress.review) {
				this.$root.routeTo(`/c/report/${this.report_guid}/review`);
				return;
			}
		},

		nextStep() {
			if (this.uri_category == 'intro') {
				this.$root.routeTo(`/c/report/${this.report_guid}/company-name`);
			}

			if (this.uri_category == 'company-name') {
				this.$root.routeTo(`/c/report/${this.report_guid}/dbas`);
			}

			if (this.uri_category == 'dbas') {
				this.$root.routeTo(`/c/report/${this.report_guid}/tax-number`);
			}

			if (this.uri_category == 'tax-number') {
				this.$root.routeTo(`/c/report/${this.report_guid}/formation-location`);
			}

			if (this.uri_category == 'formation-location') {
				this.$root.routeTo(`/c/report/${this.report_guid}/address`);
			}

			if (this.uri_category == 'address') {
				this.$root.routeTo(`/c/report/${this.report_guid}/applicants`);
			}

			if (this.uri_category == 'applicants') {
				this.$root.routeTo(`/c/report/${this.report_guid}/beneficial-owners`);
			}

			if (this.uri_category == 'beneficial-owners') {
				this.$root.routeTo(`/c/report/${this.report_guid}/identification-docs`);
			}

			if (this.uri_category == 'identification-docs') {
				this.$root.routeTo(`/c/report/${this.report_guid}/review`);
			}

			if (this.uri_category == 'review') {
				this.$root.routeTo(`/c/report/${this.report_guid}/intro`);
			}
		},

		async saveProgress(params) {
			this.loading = true;

			const response = await api(
				'PUT',
				'user/update-report',
				params,
				this.$root.bearer_token
			);

			this.$root.catch401(response);

			if (response.status == 200) {
				// console.log(response);
				let report_refreshed = await this.getReport();

				if (
					report_refreshed &&
					params.data_point != 'identification_files'
				) {
					this.nextStep();
				}
			} else {
				this.loading = false;
				this.$root.toast(
					'',
					response.message,
					'error'
				);
			}
		}
	}
};

</script>

<template>
	<div class="sub-view">
		<div class="sub-view-left">
			<p class=" text-blue fs18 bold">
				Navigation
			</p>

			<div 
				class="sub-view-menu-item mt30"
				:class="uri_category == 'intro' ? 'sub-view-menu-item-active' : ''"
				@click="this.$root.routeTo('/c/report/'+report_guid+'/intro')"
			>
				<i 
					class="fa fa-check fs17 mr10"
					:class="progress.intro ? 'text-green text-border' : 'text-black op2'"
				></i>
				Introduction
			</div>

			<div 
				class="sub-view-menu-item"
				:class="uri_category == 'company-name' ? 'sub-view-menu-item-active' : ''"
				@click="this.$root.routeTo('/c/report/'+report_guid+'/company-name')"
			>
				<i 
					class="fa fa-check fs17 mr10"
					:class="progress.company_name ? 'text-green text-border' : 'text-black op2'"
				></i>
				Company Name
			</div>

			<div 
				class="sub-view-menu-item"
				:class="uri_category == 'dbas' ? 'sub-view-menu-item-active' : ''"
				@click="this.$root.routeTo('/c/report/'+report_guid+'/dbas')"
			>
				<i 
					class="fa fa-check fs17 mr10"
					:class="progress.dbas ? 'text-green text-border' : 'text-black op2'"
				></i>
				DBAs
			</div>

			<div 
				class="sub-view-menu-item"
				:class="uri_category == 'tax-number' ? 'sub-view-menu-item-active' : ''"
				@click="this.$root.routeTo('/c/report/'+report_guid+'/tax-number')"
			>
				<i 
					class="fa fa-check fs17 mr10"
					:class="progress.tax_number ? 'text-green text-border' : 'text-black op2'"
				></i>
				Tax Number
			</div>

			<div 
				class="sub-view-menu-item"
				:class="uri_category == 'formation-location' ? 'sub-view-menu-item-active' : ''"
				@click="this.$root.routeTo('/c/report/'+report_guid+'/formation-location')"
			>
				<i 
					class="fa fa-check fs17 mr10"
					:class="progress.formation_location ? 'text-green text-border' : 'text-black op2'"
				></i>
				Formation Location
			</div>

			<div 
				class="sub-view-menu-item"
				:class="uri_category == 'address' ? 'sub-view-menu-item-active' : ''"
				@click="this.$root.routeTo('/c/report/'+report_guid+'/address')"
			>
				<i 
					class="fa fa-check fs17 mr10"
					:class="progress.address ? 'text-green text-border' : 'text-black op2'"
				></i>
				U.S. Address
			</div>

			<div 
				class="sub-view-menu-item"
				:class="uri_category == 'applicants' ? 'sub-view-menu-item-active' : ''"
				@click="this.$root.routeTo('/c/report/'+report_guid+'/applicants')"
			>
				<i 
					class="fa fa-check fs17 mr10"
					:class="progress.applicants ? 'text-green text-border' : 'text-black op2'"
				></i>
				Applicants
			</div>

			<div 
				class="sub-view-menu-item"
				:class="uri_category == 'beneficial-owners' ? 'sub-view-menu-item-active' : ''"
				@click="this.$root.routeTo('/c/report/'+report_guid+'/beneficial-owners')"
			>
				<i 
					class="fa fa-check fs17 mr10"
					:class="progress.beneficial_owners ? 'text-green text-border' : 'text-black op2'"
				></i>
				Beneficial Owners
			</div>

			<div 
				class="sub-view-menu-item"
				:class="uri_category == 'identification-docs' ? 'sub-view-menu-item-active' : ''"
				@click="this.$root.routeTo('/c/report/'+report_guid+'/identification-docs')"
			>
				<i 
					class="fa fa-check fs17 mr10"
					:class="(progress.identification_docs1 && progress.identification_docs2) ? 'text-green text-border' : 'text-black op2'"
				></i>
				Identification Docs
			</div>

			<div 
				class="sub-view-menu-item"
				:class="uri_category == 'review' ? 'sub-view-menu-item-active' : ''"
				@click="this.$root.routeTo('/c/report/'+report_guid+'/review')"
			>
				<i 
					class="fa fa-check fs17 mr10"
					:class="progress.review ? 'text-green text-border' : 'text-black op2'"
				></i>
				Review
			</div>
		</div>

		<div class="sub-view-right">
			<div v-if="loading" class="ajax-box">
				<ClipLoader size="45px" :color="this.$root.color_primary"></ClipLoader>
			</div>
			<div class="container-fluid sub-view-header">
				<div class="row">
					<div class="col-12">
						<div class="mt0">
							<button class="btn btn-success width-200 float-right" @click="saveAndExit">
								Save & Exit
							</button>

							<p class="bold">
								Report {{ this.$root.shortGUID(report.report_guid) }} for {{ report.company.pii_data.name }}
							</p>

							<p class="fs12 op8 inline">
								Requested {{ this.$root.dateTimeToDate(report.link_sent) }} by {{ this.$root.shortGUID(report.firm_email) }}
							</p>
						</div>

						<div class="mt20">
							<div class="progress-wrap">
								<div 
									class="progress"
									:style="{ '--progressBarWidth': progressBarWidth }"
								>
								</div>
							</div>

							<p class="text-blue fs11 mt5">
								{{ progress_total }}% Complete
							</p>
						</div>
					</div>
				</div>
			</div>

			<div class="container-fluid mt30">
				<div class="row">
					<div class="col-12" style="max-width: 800px">
						<Introduction v-if="uri_category == 'intro'"></Introduction>
						<CompanyName v-if="uri_category == 'company-name'"></CompanyName>
						<Dbas v-if="uri_category == 'dbas'"></Dbas>
						<TaxNumber v-if="uri_category == 'tax-number'"></TaxNumber>
						<FormationLocation v-if="uri_category == 'formation-location'"></FormationLocation>
						<Address v-if="uri_category == 'address'"></Address>
						<Applicants v-if="uri_category == 'applicants'"></Applicants>
						<BeneficialOwners v-if="uri_category == 'beneficial-owners'"></BeneficialOwners>
						<IdentificationDocs v-if="uri_category == 'identification-docs'"></IdentificationDocs>
						<Review v-if="uri_category == 'review'"></Review>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<style scoped>

.tr-left {
	width: 250px
}

.tr-right {
	width: auto;
	padding-left: 20px;
	padding-right: 20px;
}

.tr-check {
	width: 30px;
	vertical-align: top;
}

ol {
	margin: 0;
	margin-left: 20px;
	padding: 0;
	list-style: none;
	display: grid;
}

ol li {
	display: grid;
	grid-template-columns: 0 1fr;
	gap: 1.75em;
	align-items: start;
}

ol li::before {
	content: attr(data-icon);
	font-size: 18px;
	font-weight: bold;
	color: var(--color-primary);
	margin-top: -2px;
}

.text-border {
	text-shadow: 0px 0px 1px #a0a0a0;
}

.progress-wrap {
	background-color: #DAD9E0;
	width: 100%;
	height: 5px;
	border-radius: 2px;
	overflow: hidden;
	box-shadow: 0px 0px 2px #e0e0e0;
	position: relative;
}

.progress {
	height: 100%;
	width: var(--progressBarWidth);
	background-color: var(--color-primary);
	transition: .5s ease;
}

</style>