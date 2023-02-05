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
					field: 'name',
					headerName: 'Firm Name',
					sortable: true
				},
				{
					field: 'guid',
					headerName: '#',
					sortable: true,
					cellRenderer: (event) => {
						let guid = event.data.guid;

						if (guid) {
							return this.$root.shortGUID(guid);
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
					field: 'created_at',
					headerName: 'Created',
					sortable: true,
					sort: 'desc'
				},
				{
					field: 'plan',
					headerName: 'Current Plan',
					sortable: true
				},
				{
					field: 'total_reports',
					headerName: 'Total Reports',
					sortable: true
				},
				{
					field: 'total_companies',
					headerName: 'Total Companies',
					sortable: true
				},
				{
					field: 'total_invoiced',
					headerName: 'Total Invoiced',
					sortable: true
				},
				{
					field: '',
					headerName: 'Action',
					sortable: false,
					cellRenderer: (event) => {
						let guid = event.data.guid;

						if (guid) {
							return `<button class="btn btn-yellow btn-sm fs11">View Details</button>`;
						}
					},
					onCellClicked: (event) => {
						let guid = event.data.guid;

						if (guid) {
							this.$root.routeTo(`/f/report/${guid}`);
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
			new_company_phone: '',
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
				},
				total_reports: 0
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
			this.selected_company = data;
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
			console.log(this.new_company_phone);
			console.log(this.new_company_email);
			////
			this.loading = true;
			let fetch_bearer_token = this.$cookies.get('bearer_token');

			const response = await api(
				'POST',
				'user/create-report',
				{
					company_name:  this.new_company_name,
					company_phone: this.new_company_phone,
					company_email: this.new_company_email
				},
				fetch_bearer_token
			);

			this.$root.catch401(response);

			if (response.status == 200) {
				console.log(response);
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
			////
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
			} else {
				this.loading = false;
				this.$root.toast(
					'',
					response.message,
					'error'
				);
			}
		},
	}
};

</script>

<template>
	<div class="container-fluid mt35">
		<div class="row">
			<div class="col-12">
				<button class="btn btn-yellow width-200" @click="new_report_modal = true">
					<i class="fa fa-plus"></i>
					New Report
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
				<tr>
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
					Contact Phone Number
				</p>
				<input type="tel" class="form-control fincen-input mt5" v-model="new_company_phone" :onkeydown="this.$root.inputPhoneFormat">

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
				>
					<option value="">Select</option>
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