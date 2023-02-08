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
					headerName: 'Report Number',
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
					sortable: true,
					cellRenderer: (event) => {
						let status = event.data.status;

						if (status == 'start') {
							return 'Not started';
						}

						if (status == 'resume') {
							return 'Started';
						}

						if (status == 'view') {
							return 'Complete';
						}
					}
				},
				{
					field: 'created_at',
					headerName: 'Date Requested',
					sortable: true,
					sort: 'desc'
				},
				{
					field: 'firm_guid',
					headerName: 'Requested By',
					sortable: true,
					cellRenderer: (event) => {
						let firm_guid = event.data.firm_guid;

						if (firm_guid) {
							return this.$root.shortGUID(firm_guid);
						}
					},
				},
				{
					field: 'report_type',
					headerName: 'Type',
					sortable: true,
					cellRenderer: (event) => {
						let report_type = event.data.report_type;

						return `${this.$root.ucfirst(event.data.report_type)} Report`;
					},
				},
				{
					field: '',
					headerName: '',
					sortable: false,
					cellRenderer: (event) => {
						let status = event.data.status;

						if (status == 'start') {
							return `<button class="btn btn-sm btn-green fs11 width-100">Start</button>`;
						}

						if (status == 'resume') {
							return `<button class="btn btn-sm btn-yellow fs11 width-100">Resume</button>`;
						}

						if (status == 'view') {
							return `<button class="btn btn-sm btn-grey fs11 width-100">View</button>`;
						}
					},

					onCellClicked: (event) => {
						let report_guid = event.data.report_guid;

						if (report_guid) {
							this.$root.routeTo(`/c/report/${report_guid}`);
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
		}
	},

	components: {
		ClipLoader,
		AgGridVue,
		Modal
	},

	created() {
		this.getReports();
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

				// cookie check for auto-redirect to report flow
				let cook = this.$cookies.get('start_action');

				this.$cookies.remove('start_action');

				if (cook && cook != 'undefined') {
					this.$root.routeTo(`/c/report/${cook}`);
				}
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
				<p class="text-blue bold fs20">
					My Reports
				</p>

				<p class="mt5">
					This page will store all reports you need to complete and your completed reports.
				</p>

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

</template>