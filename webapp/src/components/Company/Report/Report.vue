<script>

import App from '../../../App.vue';
import { api } from '../../api.js';
import ClipLoader from 'vue-spinner/src/ClipLoader.vue';
import moment from 'moment';
import { Modal } from 'vue-neat-modal';
import { AgGridVue } from "ag-grid-vue3";

import "ag-grid-community/styles/ag-grid.css";
import "ag-grid-community/styles/ag-theme-alpine.css";
import "../../ag-theme-custom.css";

import 'vue-neat-modal/dist/vue-neat-modal.css';

export default {
	data() {
		return {
			report_guid:      this.$route.params.report_guid,
			uri_category:     this.$route.params.category,
			report:      {
				report_guid:  '',
				company_guid: '',
				pii_data:     {},
				filing_year:  '',
				created_at:   '',
				updated_at:   '',
				firm_guid:    '',
				report_type:  '',
				status:       '',
				company:      {
					guid:     '',
					role:     'company',
					email:    '',
					pii_data: {
						name:  '',
						type:  '',
						phone: ''
					},
					status:   ''
				}
			},
			loading:     false,
			columnDefs:  [
			],
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
		}
	},

	components: {
		ClipLoader,
		AgGridVue,
		Modal
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
				this.report = response.detail;

				//// update progress based on report return object
			} else {
				this.loading = false;
				this.$root.toast(
					'',
					response.message,
					'error'
				);
			}
		},

		routeToReports() {
			window.location.href = '/c/reports';
		},

		calculateProgress() {
			let progress_keys = Object.keys(this.progress);
			let denominator   = 0;

			progress_keys.forEach((key, index) => {
				if (this.progress[key] === true) {
					denominator++;
				}
			});

			this.progress_total = (
				denominator / 
				progress_keys.length
			).toFixed(2) * 100;

			this.progressBarWidth = this.progress_total + '%';
		},

		async intro_complete() {
			const response = await api(
				'PUT',
				'user/update-report',
				{
					report_guid: this.report_guid
				},
				this.$root.bearer_token
			);

			this.$root.catch401(response);

			if (response.status == 200) {
				console.log(response);
				this.progress.intro = true;
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
			<div class="container-fluid sub-view-header">
				<div class="row">
					<div class="col-12">
						<div class="mt0">
							<button class="btn btn-success width-200 float-right" @click="routeToReports">
								Save & Exit
							</button>

							<p class="bold">
								Report {{ this.$root.shortGUID(report.report_guid) }} for {{ report.company.pii_data.name }}
							</p>

							<p class="fs12 op8">
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
					<div class="col-12">
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

						<button class="btn btn-yellow mt40 width-200 mb100" @click="intro_complete">
							Begin Report
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>

</template>

<style scoped>

ol {
	margin: 0;
	margin-left: 20px;
	padding: 0;
	list-style: none;
	display: grid;
}

li {
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