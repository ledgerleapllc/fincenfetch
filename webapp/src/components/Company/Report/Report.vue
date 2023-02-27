<script>

import App from '../../../App.vue';
import { api } from '../../api.js';
import ClipLoader from 'vue-spinner/src/ClipLoader.vue';
import moment from 'moment';
import { Modal } from 'vue-neat-modal';
import { AgGridVue } from "ag-grid-vue3";
import Datepicker from '@vuepic/vue-datepicker';

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
				report_guid:   '',
				company_guid:  '',
				pii_data:      {
					report_year:        '',
					company_name:       '',
					dbas:               [],
					office:             {},
					formation_location: {},
					tax_number:         {},
					beneficial_owners:  []
				},
				filing_year:     '',
				created_at:      '',
				updated_at:      '',
				firm_guid:       '',
				report_type:     '',
				status:          '',
				previous_report: false,
				reviewed:        false,
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
				report_guid:            '',
				company_guid:           '',
				pii_data:               {
					report_year:        '',
					company_name:       '',
					dbas:               [],
					office:             {},
					formation_location: {},
					tax_number:         {},
					beneficial_owners:  []
				},
				filing_year:     '',
				created_at:      '',
				updated_at:      '',
				firm_guid:       '',
				report_type:     '',
				status:          '',
				previous_report: false,
				reviewed:        false,
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
				clicked:             false,
				intro:               false,
				company_name:        false,
				dbas:                false,
				address:             false,
				formation_location:  false,
				tax_number:          false,
				beneficial_owners:   false,
				identification_docs: false,
				review:              false
			},
			progress_total:   0,
			progressBarWidth: 0 + '%',
			affirmation:      false,

			// dbas clone
			dbas_clone: [],

			// formation
			formation_date: new Date(),

			// office / US states
			us_states: [
				"Alabama",
				"Alaska",
				"Arizona",
				"Arkansas",
				"California",
				"Colorado",
				"Connecticut",
				"Delaware",
				"District Of Columbia",
				"Florida",
				"Georgia",
				"Hawaii",
				"Idaho",
				"Illinois",
				"Indiana",
				"Iowa",
				"Kansas",
				"Kentucky",
				"Louisiana",
				"Maine",
				"Maryland",
				"Massachusetts",
				"Michigan",
				"Minnesota",
				"Mississippi",
				"Missouri",
				"Montana",
				"Nebraska",
				"Nevada",
				"New Hampshire",
				"New Jersey",
				"New Mexico",
				"New York",
				"North Carolina",
				"North Dakota",
				"Ohio",
				"Oklahoma",
				"Oregon",
				"Pennsylvania",
				"Rhode Island",
				"South Carolina",
				"South Dakota",
				"Tennessee",
				"Texas",
				"Utah",
				"Vermont",
				"Virginia",
				"Washington",
				"West Virginia",
				"Wisconsin",
				"Wyoming",
				//canada
				"Alberta",
				"British Columbia",
				"Manitoba",
				"New Brunswick",
				"Newfoundland and Labrador",
				"Nova Scotia",
				"Northwest Territories",
				"Nunavut",
				"Ontario",
				"Prince Edward Island",
				"Quebec",
				"Saskatchewan",
				"Yukon"
			]
		}
	},

	components: {
		ClipLoader,
		AgGridVue,
		Modal,
		Datepicker
	},

	created() {
		this.getReport();
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
			this.uri_category != 'address' &&
			this.uri_category != 'formation-location' &&
			this.uri_category != 'tax-number' &&
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
		'progress.clicked':             "calculateProgress",
		'progress.intro':               "calculateProgress",
		'progress.company_name':        "calculateProgress",
		'progress.dbas':                "calculateProgress",
		'progress.address':             "calculateProgress",
		'progress.formation_location':  "calculateProgress",
		'progress.tax_number':          "calculateProgress",
		'progress.beneficial_owners':   "calculateProgress",
		'progress.identification_docs': "calculateProgress",
		'progress.review':              "calculateProgress"
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

		async getReport() {
			let fetch_bearer_token = this.$cookies.get('bearer_token');

			this.dbas_clone = [];

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
				console.log(response);
				this.report = response.detail ?? this.report_default;

				// update progress based on report elements
				// clicked
				if (this.report.clicked) {
					this.progress.clicked = true;
				}

				// intro
				if (this.report.status != 'start') {
					this.progress.intro = true;
				}

				// company_name
				if (
					this.report.pii_data.company_name &&
					this.report.pii_data.company_name != ''
				) {
					this.progress.company_name = true;
				}

				// dbas
				if (
					this.report.pii_data.dbas &&
					Object.keys(this.report.pii_data.dbas).length > 0
				) {
					this.progress.dbas = true;

					Object.keys(this.report.pii_data.dbas).forEach((key, index) => {
						this.dbas_clone.push(this.report.pii_data.dbas[key]);
					});
				}

				// address
				if (
					this.report.pii_data.office &&
					this.report.pii_data.office.address1
				) {
					this.progress.address = true;
				}

				// formation_location
				if (
					this.report.pii_data.formation_location &&
					this.report.pii_data.formation_location.state_or_province
				) {
					this.formation_date = this.report.pii_data.formation_location.formed_at;
					this.progress.formation_location = true;
				}

				// tax_number
				if (
					this.report.pii_data.tax_number &&
					this.report.pii_data.tax_number != ''
				) {
					this.progress.tax_number = true;
				}

				// beneficial_owners
				let obj_keys = Object.keys(this.report.pii_data.beneficial_owners);

				if (
					this.report.pii_data.beneficial_owners &&
					obj_keys.length > 0
				) {
					this.progress.beneficial_owners = true;

					// identification_docs
					let id_complete = true;

					obj_keys.forEach((value, index) => {
						let id = this.report.pii_data.beneficial_owners[value].identifying_document ?? {};

						if (!id.number || id.number == '') {
							id_complete = false;
						}
					});

					if (id_complete) {
						this.progress.identification_docs = true;
					}
				}

				// review
				if (this.report.pii_data.reviewed) {
					this.progress.review = true;
				}

				// done
				this.loading      = false;
				this.loading_init = false;

				return true;
			} else {
				this.loading      = false;
				this.loading_init = false;
				this.$root.toast(
					'',
					response.message,
					'error'
				);
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

		async saveAndExit() {
			if (this.uri_category == 'company-name') {
				await this.saveProgress('company_name');
			}

			if (this.uri_category == 'dbas') {
				await this.saveProgress('dbas');
			}

			if (this.uri_category == 'address') {
				await this.saveProgress('address');
			}

			if (this.uri_category == 'formation-location') {
				await this.saveProgress('formation_location');
			}

			if (this.uri_category == 'tax-number') {
				await this.saveProgress('tax_number');
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
			).toFixed(2) * 100;

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

			if (!this.progress.address) {
				this.$root.routeTo(`/c/report/${this.report_guid}/address`);
				return;
			}

			if (!this.progress.formation_location) {
				this.$root.routeTo(`/c/report/${this.report_guid}/formation-location`);
				return;
			}

			if (!this.progress.tax_number) {
				this.$root.routeTo(`/c/report/${this.report_guid}/tax-number`);
				return;
			}

			if (!this.progress.beneficial_owners) {
				this.$root.routeTo(`/c/report/${this.report_guid}/beneficial-owners`);
				return;
			}

			if (!this.progress.identification_docs) {
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
				this.$root.routeTo(`/c/report/${this.report_guid}/address`);
			}

			if (this.uri_category == 'address') {
				this.$root.routeTo(`/c/report/${this.report_guid}/formation-location`);
			}

			if (this.uri_category == 'formation-location') {
				this.$root.routeTo(`/c/report/${this.report_guid}/tax-number`);
			}

			if (this.uri_category == 'tax-number') {
				this.$root.routeTo(`/c/report/${this.report_guid}/beneficial-owners`);
			}

			if (this.uri_category == 'beneficial-owners') {
				this.$root.routeTo(`/c/report/${this.report_guid}/identification-docs`);
			}

			if (this.uri_category == 'identification-docs') {
				this.$root.routeTo(`/c/report/${this.report_guid}/review`);
			}
		},

		submitNonUSTaxNumber() {
			//
		},

		async saveProgress(data_point) {
			let params = {
				report_guid: this.report_guid
			}

			// intro
			if (data_point == 'intro') {
				params.intro = true;
			}

			// company name / report type
			if (data_point == 'company_name') {
				params.company_name = this.$refs['company_name'].value;
				let report_type1 = this.$refs['report_type1'].checked;
				let report_type2 = this.$refs['report_type2'].checked;

				if (report_type1) {
					params.report_type = 'initial';
				} 

				if (report_type2) {
					params.report_type = 'updated';
				}

				if (
					!params.company_name ||
					params.company_name == ''
				) {
					this.$root.toast(
						'',
						'Please enter company name to continue',
						'error'
					);
					return false;
				}

				if (
					params.report_type != 'initial' &&
					params.report_type != 'updated'
				) {
					this.$root.toast(
						'',
						'Please select an option above to continue',
						'error'
					);
					return false;
				}
			}

			// dbas
			if (data_point == 'dbas') {
				let ret_false = false;

				Object.values(this.dbas_clone).forEach((value, index) => {
					if (
						!value ||
						value  == ''
					) {
						this.$root.toast(
							'',
							'Please enter a DBA',
							'error'
						);
						ret_false = true;
					}
				});

				if (ret_false) {
					return false;
				}

				params.dbas = this.dbas_clone;
			}

			// address
			if (data_point == 'address') {
				params.office_address1 = this.$refs['address1'].value;
				params.office_address2 = this.$refs['address2'].value;
				params.office_city     = this.$refs['city'].value;
				params.office_state    = this.$refs['state'].value;
				params.office_zip      = this.$refs['zip'].value;

				if (
					!params.office_address1 ||
					params.office_address1  == ''
				) {
					this.$root.toast(
						'',
						'Please enter primary office address',
						'error'
					);
					return false;
				}

				if (
					!params.office_city ||
					params.office_city  == ''
				) {
					this.$root.toast(
						'',
						'Please enter primary office city',
						'error'
					);
					return false;
				}

				if (
					!params.office_state ||
					params.office_state  == ''
				) {
					this.$root.toast(
						'',
						'Please select primary office state',
						'error'
					);
					return false;
				}

				if (
					!params.office_zip ||
					params.office_zip  == ''
				) {
					this.$root.toast(
						'',
						'Please enter primary office zip code',
						'error'
					);
					return false;
				}
			}

			// formation location
			if (data_point == 'formation_location') {
				params.formation_state = this.$refs['formation_state'].value;
				params.formation_date  = this.$root.dateTimeToDate(
					this.$root.formatZDate(
						this.formation_date
					)
				);

				if (
					!params.formation_state ||
					params.formation_state  == ''
				) {
					this.$root.toast(
						'',
						'Please enter formation state',
						'error'
					);
					return false;
				}

				if (
					!params.formation_date ||
					params.formation_date  == ''
				) {
					this.$root.toast(
						'',
						'Please enter formation date',
						'error'
					);
					return false;
				}
			}

			// tax number
			if (data_point == 'tax_number') {
				params.tax_number = this.$refs['tax_number'].value;

				if (
					!params.tax_number ||
					params.tax_number  == ''
				) {
					this.$root.toast(
						'',
						'Please enter tax number (EIN)',
						'error'
					);
					return false;
				}
			}

			// beneficial owners


			// identification docs


			// review
			////

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

				if (report_refreshed) {
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
				:class="uri_category == 'address' ? 'sub-view-menu-item-active' : ''"
				@click="this.$root.routeTo('/c/report/'+report_guid+'/address')"
			>
				<i 
					class="fa fa-check fs17 mr10"
					:class="progress.address ? 'text-green text-border' : 'text-black op2'"
				></i>
				Address
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
					:class="progress.identification_docs ? 'text-green text-border' : 'text-black op2'"
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
								Requested {{ this.$root.dateTimeToDate(report.created_at) }} by {{ this.$root.shortGUID(report.firm_guid) }}
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
					<!-- intro -->
					<div 
						v-if="uri_category == 'intro'"
						class="col-12"
						style="max-width: 800px"
					>
						<!-- returning -->
						<div v-if="progress.intro">
							<p class="bold fs20">
								Welcome back to your report
							</p>

							<p class="mt20">
								The navigation bar on the left shows all sections that you need to complete.
							</p>

							<p class="mt20">
								Please complete sections not yet marked with a blue check mark. You can click any of those to continue, or click the yellow button below to go to the first unfinished section.
							</p>

							<button class="btn btn-yellow mt40 width-300 mb100" @click="pickupBookmark">
								Go to first unfinished section
							</button>
						</div>

						<!-- first time -->
						<div v-else>
							<p class="bold fs20">
								Welcome to the fastest way to file your beneficial ownership report
							</p>

							<p class="mt20">
								FincenFetch strives to make the filing process as painless as possible and get you through your filing process in 30 minutes or less for first time filers.
							</p>

							<p class="mt20">
								The following tips will help you use FincenFetch:
							</p>

							<ol>
								<li class="mt20" data-icon="1.">
									You can click the yellow button on the left to ask a question at any time. Our team will typically get you an answer within 24 hours.
								</li>
								<li class="mt20" data-icon="2.">
									You do not need to finish in one sitting. The blue button on the left will let you save your place and come back to this report later. 
								</li>
								<li class="mt20" data-icon="3.">
									We recommend completing the steps in order. Completed steps will be marked with a blue checkmark on the menu to the left. You can move between sections using this menu.
								</li>
								<li class="mt20" data-icon="4.">
									Each section will have important information you need to know in order to submit accurate information for your report. Please read those tooltips outlined in yellow as you complete your report.
								</li>
							</ol>

							<button class="btn btn-yellow mt40 width-200 mb100" @click="saveProgress('intro')">
								Begin Report
							</button>
						</div>
					</div>

					<!-- company_name -->
					<div 
						v-if="uri_category == 'company-name'"
						class="col-12"
						style="max-width: 800px"
					>
						<p class="bold fs20">
							Company Name
						</p>

						<p class="mt20">
							Please verify the registered legal name of your business is listed in the box below. Make changes if needed.
						</p>

						<p class="op7 fs14 mt20">
							Company Name
						</p>
						<input 
							type="text" 
							class="form-control fincen-input width-400 mt5" 
							ref="company_name"
							:value="
								report.pii_data.company_name
								? report.pii_data.company_name
								: report.company.pii_data.name
							"
						>

						<p class="mt20">
							Is this the first beneficial ownership report this company has filed?
						</p>

						<table class="mt15">
							<tr>
								<td>
									<input 
										type="radio" 
										name="report_type" 
										id="report_type1" 
										class="form-radio pointer"
										ref="report_type1"
										checked
									>
								</td>
								<td class="tr-right">
									<label for="report_type1" class="pointer">
										<span>
											Yes, this is the first report.
										</span>
									</label>
								</td>
							</tr>
							<tr>
								<td>
									<input 
										type="radio" 
										name="report_type" 
										id="report_type2" 
										class="form-radio pointer"
										ref="report_type2"
									>
								</td>
								<td class="tr-right">
									<label for="report_type2" class="pointer">
										<span>
											No, this company has filed a beneficial ownership report previously.
										</span>
									</label>
								</td>
							</tr>
						</table>

						<div class="mt40 mb100 width-300">
							<ClipLoader 
								v-if="loading" 
								size="25px" 
								:color="this.$root.color_primary"
								class="pt10"
							></ClipLoader>
							<button 
								v-else 
								class="btn btn-yellow form-control" 
								@click="saveProgress('company_name')"
							>
								Confirm Name & Continue
							</button>
						</div>
					</div>

					<!-- dbas -->
					<div 
						v-if="uri_category == 'dbas'"
						class="col-12"
						style="max-width: 800px"
					>
						<p class="bold fs20">
							DBAs for your company
						</p>

						<p class="mt20">
							If your business uses any DBAs (doing business as) or trade names, list these below. DBAs should be listed whether or not they are registered with your state.
						</p>

						<p class="mt20">
							If you do not use any DBAs, simple click below to continue to the next step.
						</p>

						<div
							class="width-400"
							v-for="(dba, index) in dbas_clone"
						>
							<p class="op7 fs14 mt20">
								Company Name
							</p>
							<input 
								type="text" 
								class="form-control fincen-input eyeball-input mt5"
								v-model="dbas_clone[index]"
							>
							<div class="eyeball" @click="removeDBA(index)">
								<i class="fa fa-trash text-red pointer"></i>
							</div>
						</div>

						<button class="btn btn-success width-150 mt20" @click="addDBA">
							<i class="fa fa-plus"></i>
							Add DBA
						</button>

						<div class="mt40 mb100 width-300">
							<ClipLoader 
								v-if="loading" 
								size="25px" 
								:color="this.$root.color_primary"
								class="pt10"
							></ClipLoader>
							<button 
								v-else 
								class="btn btn-yellow form-control" 
								@click="saveProgress('dbas')"
							>
								Continue to next step
							</button>
						</div>
					</div>

					<!-- address -->
					<div 
						v-if="uri_category == 'address'"
						class="col-12"
						style="max-width: 800px"
					>
						<p class="bold fs20">
							Primary address of business operations
						</p>

						<p class="mt20">
							Enter the U.S. address where the majority of your business is conducted. This can be a home address if that is the primary location where your business performs its work.
						</p>

						<p class="mt20 bold">
							Note: FinCEN does not allow submitting registered agent addresses, PO boxes, or other third party addresses. These may only be used by businesses based outside the U.S. that have no other U.S. address.
						</p>

						<p class="op7 fs14 mt20">
							Street Address
						</p>
						<input 
							type="text" 
							class="form-control fincen-input width-400 mt5" 
							ref="address1"
							:value="report.pii_data.office.address1"
						>

						<p class="op7 fs14 mt20">
							Apt, Suite, Etc (optional)
						</p>
						<input 
							type="text" 
							class="form-control fincen-input width-400 mt5" 
							ref="address2"
							:value="report.pii_data.office.address2"
						>

						<p class="op7 fs14 mt20">
							City
						</p>
						<input 
							type="text" 
							class="form-control fincen-input width-400 mt5" 
							ref="city"
							:value="report.pii_data.office.city"
						>

						<div class="form-group">
							<div class="inline mr10">
								<p class="op7 fs14 mt20">
									State
								</p>
								<select
									class="form-select fincen-input width-200 mt5 pointer" 
									ref="state"
									:value="report.pii_data.office.state_or_province"
								>
									<option value="report.pii_data.office.state_or_province">
										{{ report.pii_data.office.state_or_province }}
									</option>

									<option v-for="state in us_states">
										{{ state }}
									</option>
								</select>
							</div>

							<div class="inline">
								<p class="op7 fs14 mt20">
									Zip
								</p>
								<input 
									type="text" 
									class="form-control fincen-input width-200 mt5" 
									ref="zip"
									:value="report.pii_data.office.postal_code"
									:onkeydown="this.$root.inputIsZipCodeFormat"
								>
							</div>
						</div>

						<div class="mt40 mb100 width-300">
							<ClipLoader 
								v-if="loading" 
								size="25px" 
								:color="this.$root.color_primary"
								class="pt10"
							></ClipLoader>
							<button 
								v-else 
								class="btn btn-yellow form-control" 
								@click="saveProgress('address')"
							>
								Continue to next step
							</button>
						</div>
					</div>

					<!-- formation_location -->
					<div 
						v-if="uri_category == 'formation-location'"
						class="col-12"
						style="max-width: 800px"
					>
						<p class="bold fs20">
							State of business formation
						</p>

						<p class="mt20">
							Please select the state (or tribal jurisdiction) where the business first submitted its documents to register the business such as articles of incorporation, organization, or similar formation documents. <span class="bold">This is generally the State where your business first registered.</span>
						</p>

						<p class="mt20">
							Note: For Foreign (non-U.S.) businesses, select the State where you registered as a foreign company to do business in the U.S.
						</p>

						<p class="op7 fs14 mt20">
							State of Formation
						</p>
						<select
							class="form-select fincen-input width-300 mt5 pointer" 
							ref="formation_state"
							:value="report.pii_data.formation_location.state_or_province"
						>
							<option value="report.pii_data.formation_location.state_or_province">
								{{ report.pii_data.formation_location.state_or_province }}
							</option>

							<option v-for="state in us_states">
								{{ state }}
							</option>
						</select>

						<p class="mt30">
							Enter the date your business submitted its formation documents. You can typically search for your business on your State’s Division of Corporations website to find this information if you do not have it available.
						</p>

						<p class="op7 fs14 mt20">
							Formation Date
						</p>
						<Datepicker 
							class=" width-400 mt5"
							:format="'yyyy/MM/dd'"
							:preview-format="'yyyy/MM/dd'"
							v-model="formation_date"
							utc
						></Datepicker>

						<div class="mt40 mb100 width-300">
							<ClipLoader 
								v-if="loading" 
								size="25px" 
								:color="this.$root.color_primary"
								class="pt10"
							></ClipLoader>
							<button 
								v-else 
								class="btn btn-yellow form-control" 
								@click="saveProgress('formation_location')"
							>
								Continue to next step
							</button>
						</div>
					</div>

					<!-- tax_number -->
					<div 
						v-if="uri_category == 'tax-number'"
						class="col-12"
						style="max-width: 800px"
					>
						<p class="bold fs20">
							Tax Number
						</p>

						<p class="mt20">
							Please enter the tax identification number for your business such as your EIN (Employer Identification Number). FinCEN regulations require your business to obtain an EIN if you do not already have one prior to filing a beneficial ownership report. You can often get these immediately on the IRS website here if your business does not yet have one.
						</p>

						<p class="mt20">
							Note: In some rare cases, a foreign (non-U.S.) company will not be subject to U.S. tax based on the nature of their business and only in these cases may a tax identification number issued by a foreign nation be submitted.
						</p>

						<p class="mt20">
							<span class="pointer text-blue underline" @click="submitNonUSTaxNumber">
								Submit non-U.S. tax number instead
							</span>
						</p>

						<p class="op7 fs14 mt20">
							EIN Number
						</p>
						<input 
							type="text" 
							class="form-control fincen-input width-400 mt5" 
							ref="tax_number"
							:value="report.pii_data.tax_number"
						>

						<div class="mt40 mb100 width-300">
							<ClipLoader 
								v-if="loading" 
								size="25px" 
								:color="this.$root.color_primary"
								class="pt10"
							></ClipLoader>
							<button 
								v-else 
								class="btn btn-yellow form-control" 
								@click="saveProgress('tax_number')"
							>
								Continue to next step
							</button>
						</div>
					</div>

					<!-- beneficial_owners -->
					<div 
						v-if="uri_category == 'beneficial-owners'"
						class="col-12"
						style="max-width: 800px"
					>
						<p class="bold fs20">
							Beneficial owners of the business
						</p>

						<p class="mt20">
							List all beneficial owners of the business entity. A beneficial owner is defined as an individual that meets any of the following criteria:
						</p>

						<div class="mt20 p25 bg-yellow">
							<ul>
								<li>
									Owns 25% of more of the reporting company, regardless if ownership is through shares, management units, or other structures leading to 25% or more ownership, profit distributions, or power over voting.
								</li>
								<li class="mt20">
									Has substantial control of the company by serving as a senior officer of the reporting company.
								</li>
								<li class="mt20">
									Has authority over the appointment or removal of any senior officer or a majority of the board of directors (or similar body) of the reporting company.
								</li>
								<li class="mt20">
									Can direct, determine or create substantial influence over important matters of the reporting company, including, for example, the reorganization, dissolution or merger of the reporting company, the selection or termination of business lines or ventures of the reporting company and the amendment of any governance documents of the reporting company.
								</li>
								<li class="mt20">
									Has any other form of substantial control over the reporting company.
								</li>
							</ul>
						</div>

						<p class="mt20">
							FinCEN’s published rules for determined beneficial ownership are broad. If you are uncertain whether or not an individual is a beneficial owner, we recommend including them on this page as there is no penalty for over-reporting, but penalties do exist for excluding beneficial owners. More information to help determine beneficial owners can be found here.
						</p>

						<p class="mt20 bold fs20">
							Beneficial Owner #1
						</p>

						<p class="mt20">
							Enter the legal name, date of birth, and current residential address of the beneficial owner below. Addresses must be residential addresses. PO boxes or office addresses are not allowed by FinCEN.
						</p>
					</div>

					<!-- identification_docs -->
					<div 
						v-if="uri_category == 'identification-docs'"
						class="col-12"
						style="max-width: 800px"
					>
						<p class="bold fs20">
							Identification Docs
						</p>

						<p class="mt20">
							Please upload a U.S. state-issued driver’s license or I.D. card or a U.S. Passport for each beneficial owner listed in the prior step. U.S. issued documents must be used if they are available. Tribal-issued documents may be used. A foreign passport may be used ONLY if the beneficial owner does not have a U.S. issued document.
						</p>

						<p class="mt20 bold">
							Beneficial Owner #1
						</p>

						<p class="op8 fs12">
							John Smith
						</p>
					</div>

					<!-- review -->
					<div 
						v-if="uri_category == 'review'"
						class="col-12"
						style="max-width: 800px"
					>
						<p class="bold fs20">
							Review
						</p>

						<p class="mt20">
							Please review all information below and complete any unfinished sections. You can click the make changes link for any section if updates are needed.
						</p>
						<p class="mt20 mb40">
							When your review is complete, we will alert your firm for them to complete a final review before filing your beneficial ownership report.
						</p>

						<hr>

						<p class="mt20 fs12 italic">
							Section 1
						</p>

						<table>
							<tr>
								<td class="tr-left bold">
									Business Legal Name
								</td>
								<td 
									v-if="progress.company_name" 
									class="tr-right text-darkgreen bold"
								>
									Complete
								</td>
								<td 
									v-else 
									class="tr-right text-red"
								>
									Incomplete
								</td>
							</tr>

							<tr>
								<td class="pt15 tr-left fs12">
									Company Name:
								</td>
								<td 
									v-if="progress.company_name"
									class="pt15 tr-right bold fs12"
								>
									{{ report.pii_data.company_name }}
								</td>
								<td 
									v-else
									class="pt15 tr-right bold fs12"
								>
									<label 
										class="fs12 underline pointer text-blue"
										@click="this.$root.routeTo('/c/report/'+this.report_guid+'/company-name')"
									>
										Return to section and finish
									</label>
								</td>
							</tr>

							<tr>
								<td class="pt5 tr-left fs12">
									Report Type:
								</td>
								<td class="pt5 tr-right bold fs12">
									{{
										this.$root.ucfirst(report.report_type)
									}}
								</td>
							</tr>
						</table>

						<p class="mt40 fs12 italic">
							Section 2
						</p>

						<table>
							<tr>
								<td class="tr-left bold">
									Business DBAs
								</td>
								<td 
									v-if="progress.dbas" 
									class="tr-right text-darkgreen bold"
								>
									Complete
								</td>
								<td 
									v-else 
									class="tr-right text-red"
								>
									Incomplete
								</td>
							</tr>

							<tr 
								v-if="progress.dbas"
								class="fs12"
								v-for="(dba, key, index) in report.pii_data.dbas"
							>
								<td class="tr-left pt5 fs12">
									&ensp;
								</td>
								<td class="tr-right pt5 fs12">
									{{ dba }}
								</td>
							</tr>
							<tr
								v-else
								class="fs12"
							>
								<td class="tr-left pt5 fs12">
									&ensp;
								</td>
								<td class="tr-right pt5 fs12">
									<label 
										class="fs12 underline pointer text-blue"
										@click="this.$root.routeTo('/c/report/'+this.report_guid+'/dbas')"
									>
										Return to section and finish
									</label>
								</td>
							</tr>
						</table>

						<p class="mt40 fs12 italic">
							Section 3
						</p>

						<table>
							<tr>
								<td class="tr-left bold">
									Business Address
								</td>
								<td 
									v-if="progress.address" 
									class="tr-right text-darkgreen bold"
								>
									Complete
								</td>
								<td 
									v-else 
									class="tr-right text-red"
								>
									Incomplete
								</td>
							</tr>

							<tr>
								<td class="pt15 tr-left fs12">
									Primary Address:
								</td>
								<td 
									v-if="progress.address"
									class="pt15 tr-right bold fs12"
								>
									{{ report.pii_data.office.address1 }}
								</td>
								<td 
									v-else
									class="tr-right pt5 fs12"
								>
									<label 
										class="fs12 underline pointer text-blue"
										@click="this.$root.routeTo('/c/report/'+this.report_guid+'/address')"
									>
										Return to section and finish
									</label>
								</td>
							</tr>

							<tr v-if="report.pii_data.office.address2">
								<td class="tr-left pt5 fs12">
									&ensp;
								</td>
								<td class="tr-right pt5 fs12">
									{{ report.pii_data.office.address2 }}
								</td>
							</tr>

							<tr v-if="report.pii_data.office.city">
								<td class="tr-left pt5 fs12">
									&ensp;
								</td>
								<td class="tr-right pt5 fs12">
									{{ report.pii_data.office.city }}
								</td>
							</tr>
						</table>

						<p class="mt40 fs12 italic">
							Section 4
						</p>

						<table>
							<tr>
								<td class="tr-left bold">
									Formation Location
								</td>
								<td 
									v-if="progress.formation_location" 
									class="tr-right text-darkgreen bold"
								>
									Complete
								</td>
								<td 
									v-else 
									class="tr-right text-red"
								>
									Incomplete
								</td>
							</tr>

							<tr>
								<td class="pt15 tr-left fs12">
									Formation Location:
								</td>
								<td 
									v-if="progress.formation_location"
									class="pt15 tr-right bold fs12"
								>
									{{ report.pii_data.formation_location.state_or_province }}
								</td>
								<td 
									v-else
									class="tr-right pt5 fs12"
								>
									<label 
										class="fs12 underline pointer text-blue"
										@click="this.$root.routeTo('/c/report/'+this.report_guid+'/formation-location')"
									>
										Return to section and finish
									</label>
								</td>
							</tr>

							<tr>
								<td class="pt5 tr-left fs12">
									Formation Date:
								</td>
								<td class="pt5 tr-right bold fs12">
									{{ report.pii_data.formation_location.formed_at }}
								</td>
							</tr>
						</table>

						<p class="mt40 fs12 italic">
							Section 5
						</p>

						<table>
							<tr>
								<td class="tr-left bold">
									Business Tax Number
								</td>
								<td 
									v-if="progress.tax_number" 
									class="tr-right text-darkgreen bold"
								>
									Complete
								</td>
								<td 
									v-else 
									class="tr-right text-red"
								>
									Incomplete
								</td>
							</tr>

							<tr>
								<td class="pt15 tr-left fs12">
									EIN Number:
								</td>
								<td 
									v-if="progress.tax_number"
									class="pt15 tr-right bold fs12"
								>
									{{ report.pii_data.tax_number }}
								</td>
								<td 
									v-else
									class="tr-right pt5 fs12"
								>
									<label 
										class="fs12 underline pointer text-blue"
										@click="this.$root.routeTo('/c/report/'+this.report_guid+'/tax-number')"
									>
										Return to section and finish
									</label>
								</td>
							</tr>
						</table>

						<p class="mt40 fs12 italic">
							Section 6
						</p>

						<table>
							<tr>
								<td class="tr-left bold">
									Beneficial Owners
								</td>
								<td 
									v-if="progress.beneficial_owners" 
									class="tr-right text-darkgreen bold"
								>
									Complete
								</td>
								<td 
									v-else 
									class="tr-right text-red"
								>
									Incomplete
								</td>
							</tr>
						</table>

						<table
							v-if="!progress.beneficial_owners"
						>
							<tr>
								<td class="pt15 tr-left bold fs12">
									Beneficial Owner #1
								</td>
								<td class="pt5 tr-right bold fs12">
									<label 
										class="fs12 underline pointer text-blue"
										@click="this.$root.routeTo('/c/report/'+this.report_guid+'/beneficial-owners')"
									>
										Return to section and finish
									</label>
								</td>
							</tr>
						</table>
						<table
							v-else
							v-for="(owner, key, index) in report.pii_data.beneficial_owners"
						>
							<tr>
								<td class="pt15 tr-left bold fs12">
									Beneficial Owner #1
								</td>
							</tr>

							<tr>
								<td class="pt5 tr-left fs12">
									Full Name:
								</td>
								<td class="pt5 tr-right bold fs12">
									Thomas spangler ////
								</td>
							</tr>

							<tr>
								<td class="pt5 tr-left fs12">
									Country of Residence:
								</td>
								<td class="pt5 tr-right bold fs12">
									United States ////
								</td>
							</tr>

							<tr>
								<td class="pt5 tr-left fs12">
									Primary Address:
								</td>
								<td class="pt5 tr-right bold fs12">
									123 aksjdh rd, kajsdh 33333 ////
								</td>
							</tr>
						</table>

						<p class="mt40 fs12 italic">
							Section 7
						</p>

						<table>
							<tr>
								<td class="tr-left bold">
									Owner Documents
								</td>
								<td 
									v-if="progress.identification_docs" 
									class="tr-right text-darkgreen bold"
								>
									Complete
								</td>
								<td 
									v-else 
									class="tr-right text-red"
								>
									Incomplete
								</td>
							</tr>

							<tr>
								<td class="pt15 tr-left fs12">
									Beneficial Owner #1:
								</td>
								<td class="pt15 tr-right bold fs12">
									Document Uploaded&ensp;
									<span class="pointer underline text-blue">
										View
									</span>
									////
								</td>
							</tr>
						</table>

						<p
							v-if="1==1" 
							class="mt40 text-red"
						>
							This report is NOT ready to submit. Please provide information in sections that display "INCOMPLETE" above by clicking the blue "Return to section and finish" link in those sections.
						</p>

						<table class="mt20">
							<tr>
								<td class="tr-check">
									<input type="checkbox" class="pointer" id="affirmation" v-model="affirmation">
								</td>
								<td>
									<label for="affirmation" class="pointer fs16">
										<span>
											I confirm that all information above is complete and correct, that no beneficial owners have been omitted from this report, and I acknowledge that my company, as the reporting company, is responsible for errors caused by incorrect or incomplete information.
										</span>
									</label>
								</td>
							</tr>
						</table>

						<button 
							class="mt30 mb100 btn width-200"
							:class="
								(
									affirmation &&
									progress.intro &&
									progress.dbas &&
									progress.address &&
									progress.formation_location &&
									progress.tax_number &&
									progress.beneficial_owners &&
									progress.identification_docs &&
									progress.review
								)
								? 'btn-yellow'
								: 'btn-black div-disabled'
							"
						>
							Submit to Filer
						</button>
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

.bg-yellow {
	background-color: #FFF9DD;
	width: 100%;
}

</style>