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
			firms:      [],
			columnDefs: [
				{
					field: 'name',
					headerName: 'Firm Name',
					sortable: true
				},
				{
					field: 'guid',
					headerName: '#',
					sortable: true
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
							return `<button class="btn btn-yellow btn-sm fs13">View Details</button>`;
						}
					},
					onCellClicked: (event) => {
						let guid = event.data.guid;

						if (guid) {
							console.log(guid);
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
			new_firm_modal: false,
			show_password:  false,
			new_firm_name:  '',
			new_firm_phone: '',
			new_firm_email: '',
			new_firm_pw:    ''
		}
	},

	components: {
		ClipLoader,
		AgGridVue,
		Modal
	},

	created() {
		//
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
				fileName: `firms-${moment().format('YYYY-MM-DD')}`
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

		async newFirm() {
			this.loading = true;
			let fetch_bearer_token = this.$cookies.get('bearer_token');

			const response = await api(
				'POST',
				'admin/create-firm',
				{
					firm_name:  this.new_firm_name,
					firm_phone: this.new_firm_phone,
					firm_email: this.new_firm_email,
					firm_pw:    this.new_firm_pw
				},
				fetch_bearer_token
			);

			this.$root.catch401(response);

			if (response.status == 200) {
				this.loading = false;
				console.log(response);
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
				<button class="btn btn-yellow width-150" @click="new_firm_modal = true">
					<i class="fa fa-plus"></i>
					New Firm
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
								<option value="trial">Trial</option>
								<option value="active">Active</option>
								<option value="cancelled">Cancelled</option>
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
						:rowData="firms"
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
		v-model="new_firm_modal"
		max-width="600px"
		:click-out="false"
	>
		<div class="modal-container" style="max-width: 600px;">
			<div v-if="loading" class="loading-overlay">
				<ClipLoader size="45px" :color="this.$root.color_primary"></ClipLoader>
			</div>

			<h5 class="pb15">
				New Firm
			</h5>

			<p class="mt20">
				This form will create an account in demo status and send an invitation email to the user containing the starting password.
			</p>

			<div class="form-group mt20">
				<p class="bold">
					Name of Firm
				</p>
				<input type="text" class="form-control fincen-input mt5" v-model="new_firm_name">

				<p class="bold mt20">
					Contact Phone Number
				</p>
				<input type="tel" class="form-control fincen-input mt5" v-model="new_firm_phone" :onkeydown="this.$root.inputPhoneFormat">

				<p class="bold mt20">
					Email
				</p>
				<input type="email" class="form-control fincen-input mt5" v-model="new_firm_email">

				<p class="bold mt20">
					Temporary Password
				</p>
				<input v-if="show_password" type="text" class="form-control fincen-input eyeball-input mt5" v-model="new_firm_pw">
				<input v-else type="password" class="form-control fincen-input eyeball-input mt5" v-model="new_firm_pw">
				<div class="eyeball" @click="show_password = !show_password">
					<img v-if="show_password" src="@/assets/images/eye.png">
					<img v-else src="@/assets/images/eyeclosed.png">
				</div>
			</div>

			<button class="btn btn-success form-control btn-inline ml0 mt40" @click="newFirm">
				Create Firm
			</button>

			<button class="btn btn-neutral form-control mt15 mb10" @click="new_firm_modal = false">
				Cancel
			</button>
		</div>
	</Modal>
</template>

<style scoped>

</style>