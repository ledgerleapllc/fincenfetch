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
			team:       [],
			columnDefs: [
				{
					field: 'guid',
					headerName: 'User Number',
					sortable: true,
					cellRenderer: (event) => {
						return this.$root.shortGUID(event.data.guid)
					},
				},
				{
					field: 'email',
					headerName: 'Email',
					sortable: true,
					cellRenderer: (event) => {
						if (event.data.primary) {
							return `${event.data.email} (Primary)`
						} else {
							return event.data.email;
						}
					},
				},
				{
					field: 'pii_data.name',
					headerName: 'Name',
					sortable: true
				},
				{
					field: 'created_at',
					headerName: 'Created',
					sortable: true,
					sort: 'desc',
					cellRenderer: (event) => {
						return this.$root.dateTimeToDate(event.data.created_at)
					},
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
		this.loadTeam();
	},

	mounted() {
		let that = this;
	},

	watch: {
	},

	methods: {
		async loadTeam() {
			let fetch_bearer_token = this.$cookies.get('bearer_token');

			const response = await api(
				'GET',
				'admin/get-team',
				{
					firm_guid: this.$parent.firm_guid
				},
				fetch_bearer_token
			);

			this.$root.catch401(response);

			if (response.status == 200) {
				console.log(response);
				this.team = response.detail;
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
				fileName: `team-${this.$parent.firm_guid}-${moment().format('YYYY-MM-DD')}`
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
						:rowData="team"
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