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
			loading:    false,
			billing:    [],
			plan:       'Plus',
			credits:    100,
			price:      '$499/month',
			next_bill:  '2022-03-31',
			columnDefs: [
				{
					field: 'created_at',
					headerName: 'Date',
					sortable: true
				},
				{
					field: 'type',
					headerName: 'Type',
					sortable: true
				},
				{
					field: 'invoice_number',
					headerName: 'Invoice Number',
					sortable: true
				},
				{
					field: 'plan',
					headerName: 'Plan Tier',
					sortable: true
				},
				{
					field: 'credits',
					headerName: 'Credits',
					sortable: true
				},
				{
					field: 'amount_billed',
					headerName: 'Amount Billed',
					sortable: true
				},
				{
					field: 'approval_code',
					headerName: 'Approval Code',
					sortable: true
				}
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
		this.loadBilling();
	},

	mounted() {
		let that = this;
	},

	watch: {
	},

	methods: {
		async loadBilling() {
			let fetch_bearer_token = this.$cookies.get('bearer_token');

			const response = await api(
				'GET',
				'admin/get-billing',
				{
					firm_guid: this.$parent.firm_guid
				},
				fetch_bearer_token
			);

			this.$root.catch401(response);

			if (response.status == 200) {
				console.log(response);
				this.billing = response.detail;
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
				fileName: `billing-${this.$parent.firm_guid}-${moment().format('YYYY-MM-DD')}`
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
				<div class="float-right">
					<button class="btn btn-yellow width-200 ml15 mb10">
						Change Plan
					</button>
					<button class="btn btn-success width-200 ml15 mb10">
						Add Report Credits
					</button>
				</div>

				<table>
					<tr>
						<td class="td-left">
							Current Plan:&ensp;
						</td>
						<td class="td-right">
							{{ plan }}
						</td>
					</tr>
					<tr>
						<td class="td-left">
							&ensp;
						</td>
						<td class="td-right">
							{{ credits }} Report Credits
						</td>
					</tr>
					<tr>
						<td class="td-left">
							&ensp;
						</td>
						<td class="td-right">
							{{ price }}
						</td>
					</tr>
					<tr>
						<td class="td-left pt10">
							Next Bill Date:&ensp;
						</td>
						<td class="td-right pt10">
							{{ next_bill }}
						</td>
					</tr>
				</table>

				<div class="table-card mt20">
					<div class="table-header">
						<span>
							<input v-model="quickFilterText" type="text" class="form-control form-control-sm width-200" placeholder="Search">
						</span>

						<div class="table-header-right">
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
						:rowData="billing"
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

.td-left {
	font-weight: bold;
	width: 150px;
	padding-bottom: 5px;
	white-space: nowrap;
}

.td-right {
	font-size: 14px;
	padding-bottom: 5px;
	width: 150px;
	white-space: nowrap;
}

</style>