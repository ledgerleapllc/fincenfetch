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
			report_guid: this.$route.params.report_guid,
			report:      {
				report_guid:  '',
				company_guid: '',
				pii_data:     {},
				filing_year:  '',
				created_at:   '',
				updated_at:   '',
				firm_guid:    '',
				report_type:  '',
				status:       '',
				company:      {
					guid:     '',
					role:     'company',
					email:    '',
					pii_data: {},
					status:   ''
				}
			},
			loading:     false,
			columnDefs:  [
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
		this.getReport();
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
				fileName: `report-${this.report_guid.format('YYYY-MM-DD')}`
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

		async getReport() {
			let fetch_bearer_token = this.$cookies.get('bearer_token');

			const response = await api(
				'GET',
				'user/get-report',
				{
					report_guid: this.report_guid
				},
				fetch_bearer_token
			);

			this.$root.catch401(response);

			if (response.status == 200) {
				console.log(response);
				this.report = response.detail;
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
					Company Report
				</p>

			</div>
		</div>
	</div>

</template>