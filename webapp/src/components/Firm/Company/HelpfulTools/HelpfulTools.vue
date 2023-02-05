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
			companies:  [],
			columnDefs: [
				{
					field: 'name',
					headerName: 'Company Name',
					sortable: true
				},
				{
					field: 'sent_at',
					headerName: 'First Link Sent',
					sortable: true
				},
				{
					field: 'opened_at',
					headerName: 'First Link Opened',
					sortable: true
				},
				{
					field: 'latest_sent_at',
					headerName: 'Latest Link Sent',
					sortable: true,
					sort: 'desc'
				},
				{
					field: 'latest_opened_at',
					headerName: 'Latest Link Opened',
					sortable: true
				},
				{
					field: 'total_reports',
					headerName: 'Total Reports',
					sortable: true
				},
				{
					field: '',
					headerName: 'Ready for Review',
					sortable: true,
					filter: true,
					cellRenderer: (event) => {
						let guid = event.data.guid;

						if (guid) {
							return `<button class="btn btn-yellow btn-sm fs13">Review</button>`;
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
	},

	mounted() {
		let that = this;
	},

	watch: {
	},

	methods: {

		onGridReady(params) {
			this.gridApi = params.api;
		},

		downloadCsv() {
			this.gridApi.exportDataAsCsv({
				fileName: `companies-${this.$parent.firm_guid}-${moment().format('YYYY-MM-DD')}`
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
		////
	</div>
</template>

<style scoped>



</style>