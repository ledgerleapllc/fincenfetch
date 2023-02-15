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
			settings:   [],
			columnDefs: [
				{
					field: 'name',
					headerName: 'Setting',
					sortable: true
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
		this.loadSettings();
	},

	mounted() {
		let that = this;
	},

	watch: {
	},

	methods: {
		async loadSettings() {
			let fetch_bearer_token = this.$cookies.get('bearer_token');

			const response = await api(
				'GET',
				'admin/get-settings',
				{
					firm_guid: this.$parent.firm_guid
				},
				fetch_bearer_token
			);

			this.$root.catch401(response);

			if (response.status == 200) {
				console.log(response);
				this.settings = response.detail;
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
				fileName: `settings-${this.$parent.firm_guid}-${moment().format('YYYY-MM-DD')}`
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
						:rowData="settings"
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