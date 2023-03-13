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
			reports:    [],
			columnDefs: [
				{
					field: 'report_guid',
					headerName: 'Report #',
					sortable: true
				},
				{
					field: 'status',
					headerName: 'Status',
					sortable: true
				},
				{
					field: 'report_type',
					headerName: 'Report Type',
					sortable: true
				},
				{
					field: 'link_sent',
					headerName: 'Link Sent',
					sortable: true,
					sort: 'desc'
				},
				{
					field: 'report_started',
					headerName: 'Started',
					sortable: true
				},
				{
					field: 'progress',
					headerName: 'Progress',
					sortable: true,
					cellRenderer: (event) => {
						let report = event.data;
						let perc   = this.calculateProgress(report);

						return `
							<span style="position: absolute; top: -5px; font-size: 12px;">
								${perc}
							</span>
							<div class="progress2-wrap mt25">
								<div 
									class="progress2"
									style="width: ${perc}"
								>
								</div>
							</div>
						`;
					},
				},
				{
					field: 'report_returned',
					headerName: 'Returned to Firm',
					sortable: true,
					filter: true
				},
				{
					field: 'complete',
					headerName: 'Review Complete',
					sortable: true,
					cellRenderer: (event) => {
						let guid = event.data.guid;

						if (guid) {
							return `<button class="btn btn-yellow btn-sm fs13">Mark Complete</button>`;
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
				'user/get-reports',
				{},
				fetch_bearer_token
			);

			this.$root.catch401(response);

			if (response.status == 200) {
				console.log(response);
				this.reports = response.detail;
			} else {
				this.$root.toast('','error','error');
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

		calculateProgress(r) {
			// calculate progress by checking each section
			// there are 11 sections so far.

			var progress = {
				clicked:              false,
				intro:                false,
				company_name:         false,
				dbas:                 false,
				tax_number:           false,
				formation_location:   false,
				address:              false,
				applicants:           false,
				beneficial_owners:    false,
				identification_docs1: false,
				identification_docs2: false,
				review:               false
			};

			// 1. clicked
			if (r.clicked) {
				progress.clicked = true;
			}

			// 2. intro
			if (r.status != 'start') {
				progress.intro = true;
			}

			// 3. company_name
			if (
				r.pii_data.company_name &&
				r.pii_data.company_name != ''
			) {
				progress.company_name = true;
			}

			// 4. dbas
			if (r.pii_data.has_dbas === false) {
				progress.dbas = true;
			}

			if (r.pii_data.has_dbas === true) {
				if (
					r.pii_data.dbas &&
					Object.keys(r.pii_data.dbas).length > 0
				) {
					progress.dbas = true;
				}

				else {
					progress.dbas = false;
				}
			}

			// 5. tax_number
			if (
				r.pii_data.tax_number &&
				r.pii_data.tax_number != ''
			) {
				progress.tax_number = true;
			}

			// 6. formation_location
			if (
				r.pii_data.state_of_formation ||
				r.pii_data.tribe_of_formation ||
				r.pii_data.state_of_registration ||
				r.pii_data.tribe_of_registration
			) {
				progress.formation_location = true;
			}

			// 7. office address
			if (r.pii_data.us_office_address_1) {
				progress.address = true;
			}

			// 8. applicants
			let applicants = Object.values(r.pii_data.applicants);

			// applicants are optional
			if (r.pii_data.applicant_needed === false) {
				progress.applicants = true;
			}

			if (
				r.pii_data.applicant_needed &&
				applicants.length > 0
			) {
				progress.applicants = true;
			}

			// 9. beneficial_owners
			let owners = Object.values(r.pii_data.beneficial_owners);

			if (owners.length > 0) {
				progress.beneficial_owners = true;
			}

			// 10. identification docs

			// applicants docs
			if (r.pii_data.applicant_needed === false) {
				progress.identification_docs1 = true;
			}

			if (
				r.pii_data.applicant_needed &&
				applicants.length > 0
			) {
				// set to pass, and subtract
				let applicant_docs_ok = true;

				applicants.forEach((applicant, index) => {
					let id = applicant.identifying_document;

					if (
						!id.type ||
						!id.document_number
					) {
						applicant_docs_ok = false;
					}

					// revert to true if applicant is filled using fincen_id
					if (applicant.has_fincen_id) {
						applicant_docs_ok = true;
					}
				});

				progress.identification_docs1 = applicant_docs_ok;
			}

			// beneficial_owners docs
			if (owners.length > 0) {
				// set to pass, and subtract
				let owner_docs_ok = true;

				owners.forEach((owner, index) => {
					let id = owner.identifying_document;

					if (
						!id.type ||
						!id.document_number
					) {
						owner_docs_ok = false;
					}

					// revert to true if owner is exempt
					if (owner.is_exempt_entity) {
						owner_docs_ok = true;
					}

					// revert to true if owner is filled using fincen_id
					if (owner.has_fincen_id) {
						owner_docs_ok = true;
					}
				});

				progress.identification_docs2 = owner_docs_ok;
			}

			// 11. review
			if (r.report_returned) {
				progress.review = true;
			}

			// calculate
			let progress_keys = Object.keys(progress);
			let numerator     = 0;

			progress_keys.forEach((key, index) => {
				if (progress[key] === true) {
					numerator++;
				}
			});

			let progress_total = (
				numerator / 
				progress_keys.length
			 * 100).toFixed(0);

			return progress_total + '%';
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