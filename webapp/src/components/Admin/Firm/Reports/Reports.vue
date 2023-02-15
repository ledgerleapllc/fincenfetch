<script>

import { api } from '../../../api.js';
import ClipLoader from 'vue-spinner/src/ClipLoader.vue';
import moment from 'moment';
import { AgGridVue } from "ag-grid-vue3";

import "ag-grid-community/styles/ag-grid.css";
import "ag-grid-community/styles/ag-theme-alpine.css";
import "../../../ag-theme-custom.css";

export default {
	data() {
		return {
			loading:           false,
			reports:           [],
			reports_used:      0,
			remaining_credits: 0,
			columnDefs:        [
				{
					field: 'report_guid',
					headerName: 'Report Number',
					sortable: true
				},
				{
					field: 'status',
					headerName: 'Status',
					sortable: true,
					filter: true,
				},
				{
					field: 'company_name',
					headerName: 'Company Name',
					sortable: true
				},
				{
					field: 'type',
					headerName: 'Report Type',
					sortable: true
				},
				{
					field: 'sent_at',
					headerName: 'Link Sent',
					sortable: true,
					sort: 'desc'
				},
				{
					field: 'opened_at',
					headerName: 'Started',
					sortable: true
				},
				{
					field: 'progress',
					headerName: 'Progress',
					sortable: true
				},
				{
					field: 'reviewed_at',
					headerName: 'Returned to Firm',
					sortable: true
				},
				{
					field: 'complete',
					headerName: 'Review Complete',
					sortable: true,
					cellRenderer: (event) => {
						let guid = event.data.guid;

						if (guid) {
							return `<i class="fa fa-check"></i>`;
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
			}
		}
	},

	components: {
		ClipLoader,
		AgGridVue
	},

	created() {
		this.loadReports();
	},

	mounted() {
		let that = this;
	},

	watch: {
	},

	methods: {
		async loadReports() {
			let fetch_bearer_token = this.$cookies.get('bearer_token');

			const response = await api(
				'GET',
				'admin/get-reports',
				{
					firm_guid: this.$parent.firm_guid
				},
				fetch_bearer_token
			);

			this.$root.catch401(response);

			if (response.status == 200) {
				console.log(response);
				this.reports = response.detail;
			} else {
				this.$root.toast(
					'',
					response.message,
					'error'
				);
			}
		},

		onGridReady(params) {
			this.gridApi = params.api;
		},

		downloadCsv() {
			this.gridApi.exportDataAsCsv({
				fileName: `reports-${this.$parent.firm_guid}-${moment().format('YYYY-MM-DD')}`
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
	}
};

</script>

<template>
	<div class="container-fluid mt35">
		<div class="row">
			<div class="col-12">
				<p class="bold">
					Reports Used: 
					<span class="fs14">
						{{ reports_used }}
					</span>
					&ensp;
					&ensp;
					Remaining Credits: 
					<span class="fs14">
						{{ remaining_credits }}
					</span>
				</p>

				<div class="table-card mt20">
					<div class="table-header">
						<span>
							<input v-model="quickFilterText" type="text" class="form-control form-control-sm width-200" placeholder="Search">
						</span>

						<div class="table-header-right">
							<span class="fs14 bold mr10">
								Complete:
							</span>
							<span class="mr20">
								<select v-model="quickFilterCategory" class="form-select form-control-sm pointer width-200">
								<option value="">Display All</option>
								<option value="complete">Complete</option>
								<option value="incomplete">Incomplete</option>
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

<style scoped>



</style>