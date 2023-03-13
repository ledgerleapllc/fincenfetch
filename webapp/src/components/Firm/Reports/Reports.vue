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
			loading:    false,
			reports:    [],
			columnDefs: [
				{
					field: 'report_guid',
					headerName: 'Report #',
					sortable: true,
					cellRenderer: (event) => {
						let report_guid = event.data.report_guid;

						if (report_guid) {
							return this.$root.shortGUID(report_guid);
						}
					},
				},
				{
					field: 'status',
					headerName: 'Status',
					filter: true,
					sortable: true
				},
				{
					field: 'company.pii_data.name',
					headerName: 'Company Name',
					sortable: true
				},
				{
					field: 'report_type',
					headerName: 'Report Type',
					sortable: true
				},
				{
					field: 'link_sent',
					headerName: 'Link Sent',
					sortable: true,
					sort: 'desc'
				},
				{
					field: 'report_started',
					headerName: 'Started',
					sortable: true
				},
				{
					field: 'progress',
					headerName: 'Progress',
					sortable: true,
					cellRenderer: (event) => {
						let report = event.data;
						let perc   = this.calculateProgress(report);

						return `
							<span style="position: absolute; top: -5px; font-size: 12px;">
								${perc}
							</span>
							<div class="progress2-wrap mt25">
								<div 
									class="progress2"
									style="width: ${perc}"
								>
								</div>
							</div>
						`;
					},
				},
				{
					field: 'report_returned',
					headerName: 'Returned',
					sortable: true
				},
				{
					field: 'report_review_complete',
					headerName: 'Review Complete',
					sortable: false,
					cellRenderer: (event) => {
						let report_review_complete = event.data.report_review_complete;

						if (report_review_complete) {
							return `<i class="fa fa-check text-blue"></i> ${report_review_complete}`;
						}

						else {
							return `<button class="btn btn-yellow btn-sm fs11 width-100">Review</button>`;
						}
					},
					onCellClicked: (event) => {
						let report_guid = event.data.report_guid;

						if (report_guid) {
							this.$root.routeTo(`/f/report/${report_guid}`);
						}
					}
				},
			],
			quickFilterText:     "",
			quickFilterCategory: "",
			gridApi:             null,
			defaultColDef: {
				flex:      1,
				minWidth:  100,
				resizable: true,
			},
			new_report_modal:  false,
			new_or_existing:   'new',

			new_company_name:  '',
			new_company_email: '',

			companies:        [],
			selected_company: {
				guid:              '',
				email:             '',
				created_at:        '',
				verified_at:       '',
				clicked_invite_at: '',
				pii_data: {
					name:                 '',
					type:                 '',
					phone:                '',
					registration_number:  '',
					registration_country: '',
					tax_id:               '',
					document_url:         '',
					document_page:        ''
				}
			}
		}
	},

	components: {
		ClipLoader,
		AgGridVue,
		Modal
	},

	created() {
		this.getReports();
		this.getExistingCompanies();
	},

	mounted() {
		let that = this;
	},

	watch: {
		quickFilterCategory: "quickFilterCategorySelect",
		selected_company(data) {
			console.log(data);
			if (data) {
				this.selected_company = data;
			} else {
				this.selected_company = {
					guid:              '',
					email:             '',
					created_at:        '',
					verified_at:       '',
					clicked_invite_at: '',
					pii_data: {
						name:                 '',
						type:                 '',
						phone:                '',
						registration_number:  '',
						registration_country: '',
						tax_id:               '',
						document_url:         '',
						document_page:        ''
					}
				}
			}
		}
	},

	methods: {
		onGridReady(params) {
			this.gridApi = params.api;
		},

		downloadCsv() {
			this.gridApi.exportDataAsCsv({
				fileName: `reports-${moment().format('YYYY-MM-DD')}`
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

		async getReports() {
			let fetch_bearer_token = this.$cookies.get('bearer_token');

			const response = await api(
				'GET',
				'user/get-reports',
				{},
				fetch_bearer_token
			);

			this.$root.catch401(response);

			if (response.status == 200) {
				console.log(response);
				this.reports = response.detail;
			} else {
				this.loading = false;
				this.$root.toast(
					'',
					response.message,
					'error'
				);
			}
		},

		async getExistingCompanies() {
			let fetch_bearer_token = this.$cookies.get('bearer_token');

			const response = await api(
				'GET',
				'user/get-companies',
				{},
				fetch_bearer_token
			);

			this.$root.catch401(response);

			if (response.status == 200) {
				// console.log(response);
				this.companies = response.detail;
			} else {
				this.loading = false;
				this.$root.toast(
					'',
					response.message,
					'error'
				);
			}
		},

		async newCompanyAndReport() {
			console.log(this.new_company_name);
			console.log(this.new_company_email);
			this.loading = true;
			let fetch_bearer_token = this.$cookies.get('bearer_token');

			const response = await api(
				'POST',
				'user/create-report',
				{
					company_name:  this.new_company_name,
					company_email: this.new_company_email
				},
				fetch_bearer_token
			);

			this.$root.catch401(response);

			if (response.status == 200) {
				console.log(response);
				this.new_report_modal = false;
				this.loading          = false;
				this.getReports();
				this.getExistingCompanies();
				this.$root.toast(
					'',
					response.message,
					'success'
				);
			} else {
				this.loading = false;
				this.$root.toast(
					'',
					response.message,
					'error'
				);
			}
		},

		async newReport() {
			if (
				!this.selected_company               ||
				!this.selected_company.guid          ||
				!this.selected_company.email         ||
				!this.selected_company.pii_data      ||
				!this.selected_company.pii_data.name
			) {
				this.$root.toast(
					'',
					'Please select a company',
					'warning'
				);
				return false;
			}

			console.log(this.selected_company.guid);
			console.log(this.selected_company.email);
			console.log(this.selected_company.pii_data.name);
			this.loading = true;
			let fetch_bearer_token = this.$cookies.get('bearer_token');

			const response = await api(
				'POST',
				'user/create-report',
				{
					company_guid:  this.selected_company.guid,
					company_name:  this.selected_company.pii_data.name,
					company_email: this.selected_company.email
				},
				fetch_bearer_token
			);

			this.$root.catch401(response);

			if (response.status == 200) {
				console.log(response);
				this.new_report_modal = false;
				this.loading          = false;
				this.getReports();
				this.$root.toast(
					'',
					response.message,
					'success'
				);
			} else {
				this.loading = false;
				this.$root.toast(
					'',
					response.message,
					'error'
				);
			}
		},

		calculateProgress(r) {
			// calculate progress by checking each section
			// there are 11 sections so far.

			var progress = {
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
			};

			// 1. clicked
			if (r.clicked) {
				progress.clicked = true;
			}

			// 2. intro
			if (r.status != 'start') {
				progress.intro = true;
			}

			// 3. company_name
			if (
				r.pii_data.company_name &&
				r.pii_data.company_name != ''
			) {
				progress.company_name = true;
			}

			// 4. dbas
			if (r.pii_data.has_dbas === false) {
				progress.dbas = true;
			}

			if (r.pii_data.has_dbas === true) {
				if (
					r.pii_data.dbas &&
					Object.keys(r.pii_data.dbas).length > 0
				) {
					progress.dbas = true;
				}

				else {
					progress.dbas = false;
				}
			}

			// 5. tax_number
			if (
				r.pii_data.tax_number &&
				r.pii_data.tax_number != ''
			) {
				progress.tax_number = true;
			}

			// 6. formation_location
			if (
				r.pii_data.state_of_formation ||
				r.pii_data.tribe_of_formation ||
				r.pii_data.state_of_registration ||
				r.pii_data.tribe_of_registration
			) {
				progress.formation_location = true;
			}

			// 7. office address
			if (r.pii_data.us_office_address_1) {
				progress.address = true;
			}

			// 8. applicants
			let applicants = Object.values(r.pii_data.applicants);

			// applicants are optional
			if (r.pii_data.applicant_needed === false) {
				progress.applicants = true;
			}

			if (
				r.pii_data.applicant_needed &&
				applicants.length > 0
			) {
				progress.applicants = true;
			}

			// 9. beneficial_owners
			let owners = Object.values(r.pii_data.beneficial_owners);

			if (owners.length > 0) {
				progress.beneficial_owners = true;
			}

			// 10. identification docs

			// applicants docs
			if (r.pii_data.applicant_needed === false) {
				progress.identification_docs1 = true;
			}

			if (
				r.pii_data.applicant_needed &&
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

				progress.identification_docs1 = applicant_docs_ok;
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

				progress.identification_docs2 = owner_docs_ok;
			}

			// 11. review
			if (r.report_returned) {
				progress.review = true;
			}

			// calculate
			let progress_keys = Object.keys(progress);
			let numerator     = 0;

			progress_keys.forEach((key, index) => {
				if (progress[key] === true) {
					numerator++;
				}
			});

			let progress_total = (
				numerator / 
				progress_keys.length
			 * 100).toFixed(0);

			return progress_total + '%';
		}
	}
};

</script>

<template>
	<div class="container-fluid mt35">
		<div class="row">
			<div class="col-12">
				<button class="btn btn-yellow width-200" @click="new_report_modal = true">
					<i class="fa fa-plus"></i>
					Send New BOI Link
				</button>

				<div class="table-card mt20">
					<div class="table-header">
						<span>
							<input v-model="quickFilterText" type="text" class="form-control form-control-sm width-200" placeholder="Search">
						</span>

						<div class="table-header-right">
							<span class="fs14 bold mr10">
								Status:
							</span>
							<span class="mr20">
								<select v-model="quickFilterCategory" class="form-select form-control-sm pointer width-200">
									<option value="">Display All</option>
								</select>
							</span>
							<span>
								<i class="fa fa-download text-blue pointer fs28 mt5" v-on:click="downloadCsv()"></i>
							</span>
						</div>
					</div>

					<ag-grid-vue
						style="width: 100%; height: 100%;"
						class="ag-theme-alpine"
						:columnDefs="columnDefs"
						@grid-ready="onGridReady"
						:suppressExcelExport="true"
						:rowData="reports"
						:quickFilterText="quickFilterText"
						:defaultColDef="defaultColDef"
						pagination="true"
					>
					</ag-grid-vue>
				</div>
			</div>
		</div>
	</div>

	<Modal
		v-model="new_report_modal"
		max-width="600px"
		:click-out="false"
	>
		<div class="modal-container" style="max-width: 600px;">
			<div v-if="loading" class="loading-overlay">
				<ClipLoader size="45px" :color="this.$root.color_primary"></ClipLoader>
			</div>

			<h5 class="pb15">
				New Report
			</h5>

			<p class="mt20">
				Sending a BOI link begins a new report. This link will allow your client to submit their beneficial ownership information. Please make a select below.
			</p>

			<table class="table mt30">
				<tr>
					<td class="pl15 pr15 pointer">
						<input 
							type="radio" 
							id="new_company" 
							v-model="new_or_existing" 
							name="new_or_existing"
							:checked="new_or_existing == 'new'"
							value="new"
						>
					</td>
					<td>
						<label class="fs16 pointer" for="new_company">
							New Company: <span>Send a link to a new company that has not yet received a link sent by this system. The system will create a new client company.</span>
						</label>
					</td>
				</tr>
				<tr><td>&ensp;</td></tr>
				<tr :class="companies.length == 0 ? 'div-disabled' : ''">
					<td class="pl15 pr15 pointer">
						<input 
							type="radio" 
							id="selected_company" 
							v-model="new_or_existing" 
							name="new_or_existing"
							:checked="new_or_existing == 'existing'"
							value="existing"
						>
					</td>
					<td>
						<label class="fs16 pointer" for="selected_company">
							Existing Company: <span>Send another link to an existing client company that has received a link by this system previously. The system will add this new report to their file.</span>
						</label>
					</td>
				</tr>
			</table>

			<div v-if="new_or_existing == 'new'">
				<p class="mt20">
					Please enter the name and email for the client company to send the link to collect beneficial ownership information.
				</p>

				<p class="bold mt20">
					Company Name
				</p>
				<input type="text" class="form-control fincen-input mt5" v-model="new_company_name">

				<p class="bold mt20">
					Company Email
				</p>
				<input type="email" class="form-control fincen-input mt5" v-model="new_company_email">

				<button class="btn btn-success form-control btn-inline ml0 mt40" @click="newCompanyAndReport">
					Send Link to Collect BOI
				</button>
			</div>

			<div v-else>
				<p class="mt20">
					Select the client company that will receive the link to submit their beneficial ownership information.
				</p>

				<p class="bold mt20">
					Select Client Company
				</p>
				<select 
					class="form-select fincen-input mt5 pointer" 
					v-model="selected_company"
					placeholder="Select"
				>
					<option 
						v-for="company in companies" 
						:value="company"
					>
						{{ company.pii_data.name }}
					</option>
				</select>

				<p class="bold mt20">
					Confirm Company Email
				</p>
				<input 
					type="email" 
					class="form-control fincen-input mt5" 
					v-model="selected_company.email"
				>

				<button class="btn btn-success form-control btn-inline ml0 mt40" @click="newReport">
					Send Link to Collect BOI
				</button>
			</div>

			<button class="btn btn-neutral form-control mt15 mb10" @click="new_report_modal = false">
				Cancel
			</button>
		</div>
	</Modal>
</template>