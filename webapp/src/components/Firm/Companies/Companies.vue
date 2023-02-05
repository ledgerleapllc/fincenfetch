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
			companies:  [],
			columnDefs: [
				{
					field: 'pii_data.name',
					headerName: 'Businesses',
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
					field: 'created_at',
					headerName: 'First Link Sent',
					filter: true,
					sortable: true
				},
				{
					field: 'clicked_invite_at',
					headerName: 'First Link Access',
					sortable: true
				},
				{
					field: 'latest_link_sent',
					headerName: 'Latest Link Sent',
					sortable: true,
					sort: 'desc'
				},
				{
					field: 'latest_link_access',
					headerName: 'Latest Link Access',
					sortable: true
				},
				{
					field: 'total_reports',
					headerName: 'Total Reports',
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
							this.$root.routeTo(`/f/company/${guid}/reports`);
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
			new_company_modal: false,
			new_company_name:  '',
			new_company_phone: '',
			new_company_email: ''
		}
	},

	components: {
		ClipLoader,
		AgGridVue,
		Modal
	},

	created() {
		this.getCompanies();
	},

	mounted() {
		let that = this;
	},

	watch: {
		quickFilterCategory: "quickFilterCategorySelect"
	},

	methods: {
		onGridReady(params) {
			this.gridApi = params.api;
		},

		downloadCsv() {
			this.gridApi.exportDataAsCsv({
				fileName: `companies-${moment().format('YYYY-MM-DD')}`
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

		async getCompanies() {
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

		async newCompany() {
			this.loading = true;
			let fetch_bearer_token = this.$cookies.get('bearer_token');

			const response = await api(
				'POST',
				'user/invite-company',
				{
					company_name:  this.new_company_name,
					company_phone: this.new_company_phone,
					company_email: this.new_company_email
				},
				fetch_bearer_token
			);

			this.$root.catch401(response);

			if (response.status == 200) {
				this.loading          = false;
				this.new_company_modal = false;
				this.getCompanies();

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
		}
	}
};

</script>

<template>
	<div class="container-fluid mt35">
		<div class="row">
			<div class="col-12">
				<button class="btn btn-yellow width-200" @click="new_company_modal = true">
					<i class="fa fa-plus"></i>
					Invite Company
				</button>

				<!-- <div style="display: flex; flex-direction: row; position: relative; width: 400px;">
					<div style="min-width: 100px;">
						aisd ak dkak sd
					</div>
					<div style="width: 40px; min-width: 40px; text-align: center;">
						>
					</div>
					<div style="min-width: 100px;">
						asjkd 
					</div>
				</div> -->

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
						:rowData="companies"
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
		v-model="new_company_modal"
		max-width="600px"
		:click-out="false"
	>
		<div class="modal-container" style="max-width: 600px;">
			<div v-if="loading" class="loading-overlay">
				<ClipLoader size="45px" :color="this.$root.color_primary"></ClipLoader>
			</div>

			<h5 class="pb15">
				Invite Company
			</h5>

			<p class="mt20">
				This form will create an account for your client and send an invitation email to the user containing a signup link.
			</p>

			<div class="form-group mt20">
				<p class="bold">
					Company Name
				</p>
				<input type="text" class="form-control fincen-input mt5" v-model="new_company_name">

				<p class="bold mt20">
					Contact Phone Number
				</p>
				<input type="tel" class="form-control fincen-input mt5" v-model="new_company_phone" :onkeydown="this.$root.inputPhoneFormat">

				<p class="bold mt20">
					Email
				</p>
				<input type="email" class="form-control fincen-input mt5" v-model="new_company_email">
			</div>

			<button class="btn btn-success form-control btn-inline ml0 mt40" @click="newCompany">
				Send Invitation
			</button>

			<button class="btn btn-neutral form-control mt15 mb10" @click="new_company_modal = false">
				Cancel
			</button>
		</div>
	</Modal>
</template>