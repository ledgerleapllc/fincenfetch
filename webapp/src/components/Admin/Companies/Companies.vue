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
			companies:      [],
			columnDefs: [
				{
					field: 'name',
					headerName: 'Firm Name',
					sortable: true
				},
				{
					field: 'guid',
					headerName: 'User Number',
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
					headerName: 'Sign Up',
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
					field: 'total_paid',
					headerName: 'Total Paid',
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
							this.$root.routeTo(`/a/firm/${guid}/reports`);
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
			new_firm_name:  '',
			new_firm_phone: '',
			new_firm_email: ''
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
				'admin/get-companies',
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
		}
	}
};

</script>

<template>
	<div class="container-fluid mt35">
		<div class="row">
			<div class="col-12">
				<div class="table-card mt20">
					<div class="table-header">
						<span>
							<input v-model="quickFilterText" type="text" class="form-control form-control-sm width-200" placeholder="Search">
						</span>
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

</template>

<style scoped>

</style>