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
			report:            {
				report_guid:     '',
				company_guid:    '',
				firm_guid:       '',
				firm_email:      '',
				report_type:     '',
				status:          '',
				link_sent:       '',
				link_clicked:    '',
				report_started:  '',
				report_returned: '',
				report_reviewed: '',
				report_review_complete: '',
				marked_filed:    false,
				pii_data:      {
					company_name:               '',
					foreign_investment:         false,
					exempt:                     false,
					request_fincen_number:      false,
					has_dbas:                   null,
					dbas:                       [],
					tax_number_type:            '',
					tax_number:                 '',
					foreign_tax_number_country: '',
					company_origination_type:   '',
					state_of_formation:         '',
					tribe_of_formation:         '',
					state_of_registration:      '',
					tribe_of_registration:      '',
					formation_date:             '',
					us_office_address_1:        '',
					us_office_address_2:        '',
					us_office_city:             '',
					us_office_state:            '',
					us_office_zip:              '',
					company_before_2024:        null,
					applicant_needed:           null,
					applicants:                 [],
					beneficial_owners:          []
				},
				company:         {
					guid:        '',
					role:        'company',
					email:       '',
					pii_data:    {
						name:    '',
						type:    '',
						phone:   ''
					},
					status:      ''
				}
			},
			loading:        true,
			review_check_1: false,
			review_check_2: false,
			review_check_3: false,
			review_check_4: false,
			review_check_5: false,
			review_check_6: false,
			review_check_7: false,
			review_check_8: false,
			review_check_9: false,
			affirmation:    false
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
	},

	methods: {
		async getReport() {
			let fetch_bearer_token = this.$cookies.get('bearer_token');

			this.loading = true;

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
				// this.report  = response.detail;
				// this.loading = false;
				let that = this;
				setTimeout(function() {
					that.report = response.detail;
					that.loading = false;
				},2000);
			} else {
				this.loading = false;
				this.$root.toast(
					'',
					response.message,
					'error'
				);
			}
		},

		openLink(link) {
			window.open(link);
		}
	}
};

</script>

<template>
	<div class="container-fluid mt35" style="max-width: 1100px">
		<div class="row">
			<div class="col-12">
				<p class="fs14">
					<span 
						class="bold text-blue pointer"
						@click="this.$root.routeTo('/f/reports')"
					>
						<i class="fa fa-arrow-left"></i>
						Cancel Review
					</span>
				</p>

				<p class="bold fs20 mt20">
					Review Report
				</p>

				<p class="mt20">
					Please review all the sections below. You can click the make changes button to update any section if needed. When your review is complete, you will have all the necessary information needed to file with FinCEN.
				</p>
			</div>
		</div>

		<div class="row">
			<div class="col-12">
				<div class="firm-report-card">
					<div class="blue-header">
						<p class="fs22">
							<span class="bold">
								Report {{ this.$root.shortGUID(report_guid) }}
							</span>
							for
							<ClipLoader 
								v-if="loading" 
								size="25px" 
								color="white"
								class="inline"
							></ClipLoader>
							<span v-else class="bold fs20">
								{{ report.company.pii_data.name }}
							</span>
						</p>

						<p class="fs14 mt10">
							Requested: 
							<ClipLoader 
								v-if="loading" 
								size="15px" 
								color="white"
								class="inline"
							></ClipLoader>
							<span v-else class="bold">
								{{ this.$root.dateTimeToDate(report.link_sent) }}
							</span>
							by
							<ClipLoader 
								v-if="loading" 
								size="15px" 
								color="white"
								class="inline"
							></ClipLoader>
							<span v-else class="bold">
								{{ 'Wilson Legal Services' }}
							</span>
						</p>

						<p class="fs14">
							Submitted: 
							<ClipLoader 
								v-if="loading" 
								size="15px" 
								color="white"
								class="inline"
							></ClipLoader>
							<span v-else class="bold">
								{{ this.$root.dateTimeToDate(report.link_sent) }}
							</span>
							by
							<ClipLoader 
								v-if="loading" 
								size="15px" 
								color="white"
								class="inline"
							></ClipLoader>
							<span v-else class="bold">
								{{ report.company.email }}
							</span>
						</p>
					</div>

					<div class="container-fluid">
						<div class="row mt30">
							<div class="col-4 flex-col">
								<p class="mt20 op7">
									<input 
										type="checkbox" 
										class="pointer" 
										id="review-check-1" 
										v-model="review_check_1"
									>
									<label for="review-check-1" class="fs12 italic ml5 pointer">
										Reviewed
									</label>
								</p>

								<button class="btn btn-yellow mt15">
									Make Changes
								</button>
							</div>

							<div class="col-8">
								<p class="mt20 fs12 italic">
									Section 1
								</p>

								<table>
									<tr>
										<td class="tr-left bold pb20">
											Introduction
										</td>
										<ClipLoader 
											v-if="loading" 
											size="15px" 
											:color="this.$root.color_primary"
											class="inline"
										></ClipLoader>
										<div v-else>
											<td 
												v-if="report.report_started"
												class="tr-right bold text-darkgreen pb20"
											>
												Complete
											</td>
											<td 
												v-else
												class="tr-right bold text-red pb20"
											>
												Incomplete
											</td>
										</div>
									</tr>
								</table>
							</div>
						</div>

						<div class="row mt50">
							<div class="col-4 flex-col">
								<p class="mt20 op7">
									<input 
										type="checkbox" 
										class="pointer" 
										id="review-check-2" 
										v-model="review_check_2"
									>
									<label for="review-check-2" class="fs12 italic ml5 pointer">
										Reviewed
									</label>
								</p>

								<button class="btn btn-yellow mt15">
									Make Changes
								</button>
							</div>

							<div class="col-8">
								<p class="mt20 fs12 italic">
									Section 2
								</p>

								<table>
									<tr>
										<td class="tr-left bold pb20">
											Company Name
										</td>
										<ClipLoader 
											v-if="loading" 
											size="15px" 
											:color="this.$root.color_primary"
											class="inline"
										></ClipLoader>
										<div v-else>
											<td 
												v-if="report.pii_data.company_name"
												class="tr-right bold text-darkgreen pb20"
											>
												Complete
											</td>
											<td 
												v-else
												class="tr-right bold text-red pb20"
											>
												Incomplete
											</td>
										</div>
									</tr>

									<tr>
										<td class="tr-left">
											Company Name
										</td>

										<ClipLoader 
											v-if="loading" 
											size="15px" 
											:color="this.$root.color_primary"
											class="inline"
										></ClipLoader>
										<td 
											v-else 
											class="tr-right bold"
										>
											{{ report.pii_data.company_name }}
										</td>
									</tr>

									<tr>
										<td class="tr-left">
											Report Type
										</td>

										<ClipLoader 
											v-if="loading" 
											size="15px" 
											:color="this.$root.color_primary"
											class="inline"
										></ClipLoader>
										<td 
											v-else 
											class="tr-right bold"
										>
											{{
												this.$root.ucfirst(report.report_type)
											}} Report
										</td>
									</tr>

									<tr>
										<td class="tr-left fs15">
											Foreign pooled investment vehicle
										</td>

										<ClipLoader 
											v-if="loading" 
											size="15px" 
											:color="this.$root.color_primary"
											class="inline"
										></ClipLoader>
										<td 
											v-else 
											class="tr-right bold"
										>
											{{
												report.foreign_investment ?
												'Yes' :
												'No'
											}}
										</td>
									</tr>

									<tr>
										<td class="tr-left">
											Exempt from reporting
										</td>

										<ClipLoader 
											v-if="loading" 
											size="15px" 
											:color="this.$root.color_primary"
											class="inline"
										></ClipLoader>
										<td
											v-else
											class="tr-right bold"
										>
											{{
												report.exempt ?
												'Yes' :
												'No'
											}}
										</td>
									</tr>
								</table>
							</div>
						</div>

						<div class="row mt50">
							<div class="col-4 flex-col">
								<p class="mt20 op7">
									<input 
										type="checkbox" 
										class="pointer" 
										id="review-check-3" 
										v-model="review_check_3"
									>
									<label for="review-check-3" class="fs12 italic ml5 pointer">
										Reviewed
									</label>
								</p>

								<button class="btn btn-yellow mt15">
									Make Changes
								</button>
							</div>

							<div class="col-8">
								<p class="mt20 fs12 italic">
									Section 3
								</p>

								<table>
									<tr>
										<td class="tr-left bold pb20">
											DBAs
										</td>
										<ClipLoader 
											v-if="loading" 
											size="15px" 
											:color="this.$root.color_primary"
											class="inline"
										></ClipLoader>
										<div v-else>
											<td 
												v-if="report.pii_data.has_dbas === null"
												class="tr-right bold text-red pb20"
											>
												Incomplete
											</td>
											<td 
												v-else
												class="tr-right bold text-darkgreen pb20"
											>
												Complete
											</td>
										</div>
									</tr>

									<tr v-if="report.pii_data.has_dbas === false">
										<td class="tr-left">
											None Listed
										</td>
									</tr>

									<tr 
										v-if="report.pii_data.has_dbas === true"
										v-for="(dba, key, index) in report.pii_data.dbas"
									>
										<td class="tr-left">
											&ensp;
										</td>
										<td class="tr-right">
											{{ dba }}
										</td>
									</tr>
								</table>
							</div>
						</div>

						<div class="row mt50">
							<div class="col-4 flex-col">
								<p class="mt20 op7">
									<input 
										type="checkbox" 
										class="pointer" 
										id="review-check-4" 
										v-model="review_check_4"
									>
									<label for="review-check-4" class="fs12 italic ml5 pointer">
										Reviewed
									</label>
								</p>

								<button class="btn btn-yellow mt15">
									Make Changes
								</button>
							</div>

							<div class="col-8">
								<p class="mt20 fs12 italic">
									Section 4
								</p>

								<table>
									<tr>
										<td class="tr-left bold pb20">
											Business Tax Number
										</td>

										<ClipLoader 
											v-if="loading" 
											size="15px" 
											:color="this.$root.color_primary"
											class="inline"
										></ClipLoader>
										<div v-else>
											<td 
												v-if="report.pii_data.tax_number"
												class="tr-right bold text-darkgreen pb20"
											>
												Complete
											</td>
											<td 
												v-else
												class="tr-right bold text-red pb20"
											>
												Incomplete
											</td>
										</div>
									</tr>

									<tr>
										<td class="tr-left">
											Tax Number
										</td>
										<ClipLoader 
											v-if="loading" 
											size="15px" 
											:color="this.$root.color_primary"
											class="inline"
										></ClipLoader>
										<td 
											class="tr-right bold"
											v-else
										>
											{{ report.pii_data.tax_number }}
										</td>
									</tr>

									<tr>
										<td class="tr-left">
											Tax Number Type
										</td>
										<ClipLoader 
											v-if="loading" 
											size="15px" 
											:color="this.$root.color_primary"
											class="inline"
										></ClipLoader>
										<td 
											class="tr-right bold"
											v-else
										>
											{{ report.pii_data.tax_number_type }}
										</td>
									</tr>

									<tr v-if="report.pii_data.tax_number_type == 'foreign'">
										<td class="tr-left">
											Foreign Tax Number Country
										</td>
										<td 
											class="tr-right bold"
										>
											{{ report.pii_data.foreign_tax_number_country }}
										</td>
									</tr>
								</table>
							</div>
						</div>

						<div class="row mt50">
							<div class="col-4 flex-col">
								<p class="mt20 op7">
									<input 
										type="checkbox" 
										class="pointer" 
										id="review-check-5" 
										v-model="review_check_5"
									>
									<label for="review-check-5" class="fs12 italic ml5 pointer">
										Reviewed
									</label>
								</p>

								<button class="btn btn-yellow mt15">
									Make Changes
								</button>
							</div>

							<div class="col-8">
								<p class="mt20 fs12 italic">
									Section 5
								</p>

								<table>
									<tr>
										<td class="tr-left bold pb20">
											Formation Location
										</td>

										<ClipLoader 
											v-if="loading" 
											size="15px" 
											:color="this.$root.color_primary"
											class="inline"
										></ClipLoader>
										<div v-else>
											<td 
												v-if="report.pii_data.state_of_formation"
												class="tr-right bold text-darkgreen pb20"
											>
												Complete
											</td>
											<td 
												v-else
												class="tr-right bold text-red pb20"
											>
												Incomplete
											</td>
										</div>
									</tr>

									<tr>
										<td class="tr-left">
											Formation Location
										</td>
										<ClipLoader 
											v-if="loading" 
											size="15px" 
											:color="this.$root.color_primary"
											class="inline"
										></ClipLoader>
										<td 
											class="tr-right bold"
											v-else
										>
											{{ report.pii_data.state_of_formation }}
										</td>
									</tr>

									<tr>
										<td class="tr-left">
											Formation Date
										</td>
										<ClipLoader 
											v-if="loading" 
											size="15px" 
											:color="this.$root.color_primary"
											class="inline"
										></ClipLoader>
										<td 
											class="tr-right bold"
											v-else
										>
											{{ report.pii_data.formation_date }}
										</td>
									</tr>

									<tr>
										<td class="tr-left">
											Origination
										</td>
										<ClipLoader 
											v-if="loading" 
											size="15px" 
											:color="this.$root.color_primary"
											class="inline"
										></ClipLoader>
										<td 
											class="tr-right bold"
											v-else
										>
											{{ report.pii_data.company_origination_type }}
										</td>
									</tr>
								</table>
							</div>
						</div>

						<div class="row mt50">
							<div class="col-4 flex-col">
								<p class="mt20 op7">
									<input 
										type="checkbox" 
										class="pointer" 
										id="review-check-6" 
										v-model="review_check_6"
									>
									<label for="review-check-6" class="fs12 italic ml5 pointer">
										Reviewed
									</label>
								</p>

								<button class="btn btn-yellow mt15">
									Make Changes
								</button>
							</div>

							<div class="col-8">
								<p class="mt20 fs12 italic">
									Section 6
								</p>

								<table>
									<tr>
										<td class="tr-left bold pb20">
											Business Address
										</td>

										<ClipLoader 
											v-if="loading" 
											size="15px" 
											:color="this.$root.color_primary"
											class="inline"
										></ClipLoader>
										<div v-else>
											<td 
												v-if="report.pii_data.us_office_address_1"
												class="tr-right bold text-darkgreen pb20"
											>
												Complete
											</td>
											<td 
												v-else
												class="tr-right bold text-red pb20"
											>
												Incomplete
											</td>
										</div>
									</tr>

									<tr>
										<td class="tr-left">
											Address Line 1
										</td>
										<ClipLoader 
											v-if="loading" 
											size="15px" 
											:color="this.$root.color_primary"
											class="inline"
										></ClipLoader>
										<td 
											class="tr-right bold"
											v-else
										>
											{{ report.pii_data.us_office_address_1 }}
										</td>
									</tr>

									<tr v-if="report.pii_data.us_office_address_2">
										<td class="tr-left">
											Address Line 2
										</td>
										<td 
											class="tr-right bold"
										>
											{{ report.pii_data.us_office_address_2 }}
										</td>
									</tr>

									<tr>
										<td class="tr-left">
											City
										</td>
										<ClipLoader 
											v-if="loading" 
											size="15px" 
											:color="this.$root.color_primary"
											class="inline"
										></ClipLoader>
										<td 
											v-else
											class="tr-right bold"
										>
											{{ report.pii_data.us_office_city }}, 
										</td>
									</tr>

									<tr>
										<td class="tr-left">
											State
										</td>
										<ClipLoader 
											v-if="loading" 
											size="15px" 
											:color="this.$root.color_primary"
											class="inline"
										></ClipLoader>
										<td 
											v-else
											class="tr-right bold"
										>
											{{ report.pii_data.us_office_state }}, 
										</td>
									</tr>

									<tr>
										<td class="tr-left">
											Zip
										</td>
										<ClipLoader 
											v-if="loading" 
											size="15px" 
											:color="this.$root.color_primary"
											class="inline"
										></ClipLoader>
										<td 
											v-else
											class="tr-right bold"
										>
											{{ report.pii_data.us_office_zip }}, 
										</td>
									</tr>
								</table>
							</div>
						</div>

						<div class="row mt50">
							<div class="col-4 flex-col">
								<p class="mt20 op7">
									<input 
										type="checkbox" 
										class="pointer" 
										id="review-check-7" 
										v-model="review_check_7"
									>
									<label for="review-check-7" class="fs12 italic ml5 pointer">
										Reviewed
									</label>
								</p>

								<button class="btn btn-yellow mt15">
									Make Changes
								</button>
							</div>

							<div class="col-8">
								<p class="mt20 fs12 italic">
									Section 7
								</p>

								<table>
									<tr>
										<td class="tr-left bold pb20">
											Applicants
										</td>

										<ClipLoader 
											v-if="loading" 
											size="15px" 
											:color="this.$root.color_primary"
											class="inline"
										></ClipLoader>
										<div v-else>
											<td 
												v-if="
													report.pii_data.applicant_needed !== false &&
													report.pii_data.applicants.length == 0
												"
												class="tr-right bold text-red pb20"
											>
												Incomplete
											</td>
											<td 
												v-else
												class="tr-right bold text-darkgreen pb20"
											>
												Complete
											</td>
										</div>
									</tr>

									<tr v-if="report.pii_data.applicant_needed === false">
										<td class="tr-left">
											None Listed
										</td>
									</tr>
								</table>

								<table 
									v-for="(applicant, index) in report.applicants"
								>
									<tr>
										<td class="tr-left bold">
											Applicant #{{ index + 1 }}
										</td>
									</tr>

									<tr v-if="applicant.has_fincen_id">
										<td class="tr-left">
											FinCEN ID
										</td>
										<td class="tr-right bold">
											{{ applicant.fincen_id }} 
										</td>
									</tr>

									<tr v-if="!applicant.has_fincen_id">
										<td class="tr-left">
											First Name
										</td>
										<td class="tr-right bold">
											{{ applicant.first_name }} 
										</td>
									</tr>

									<tr v-if="!applicant.has_fincen_id">
										<td class="tr-left">
											Middle Name
										</td>
										<td class="tr-right bold">
											{{ applicant.middle_name }} 
										</td>
									</tr>

									<tr v-if="!applicant.has_fincen_id">
										<td class="tr-left">
											Last Name
										</td>
										<td class="tr-right bold">
											{{ applicant.last_name }}
										</td>
									</tr>

									<tr v-if="!applicant.has_fincen_id">
										<td class="tr-left">
											Suffix
										</td>
										<td class="tr-right bold">
											{{ applicant.suffix }}
										</td>
									</tr>

									<tr v-if="!applicant.has_fincen_id">
										<td class="tr-left">
											Country
										</td>
										<td class="tr-right bold">
											{{ applicant.country }}
										</td>
									</tr>

									<tr v-if="!applicant.has_fincen_id">
										<td class="tr-left">
											Address Type
										</td>
										<td class="tr-right bold">
											{{ applicant.address_type }} 
										</td>
									</tr>

									<tr v-if="!applicant.has_fincen_id">
										<td class="tr-left">
											Address Line 1
										</td>
										<td class="tr-right bold">
											{{ applicant.us_address_1 }} 
										</td>
									</tr>

									<tr 
										v-if="
											applicant.us_address_2 &&
											!applicant.has_fincen_id
										"
									>
										<td class="tr-left">
											Address Line 2
										</td>
										<td class="tr-right bold">
											{{ applicant.us_address_2 }} 
										</td>
									</tr>

									<tr 
										v-if="
											applicant.us_city &&
											!applicant.has_fincen_id
										"
									>
										<td class="tr-left">
											City
										</td>
										<td class="tr-right bold">
											{{ applicant.us_city }}
										</td>
									</tr>

									<tr 
										v-if="
											applicant.us_state &&
											!applicant.has_fincen_id
										"
									>
										<td class="tr-left">
											State
										</td>
										<td class="tr-right bold">
											{{ applicant.us_state }}
										</td>
									</tr>

									<tr 
										v-if="
											applicant.us_zip &&
											!applicant.has_fincen_id
										"
									>
										<td class="tr-left">
											Zip
										</td>
										<td class="tr-right bold">
											{{ applicant.us_zip }}
										</td>
									</tr>
								</table>
							</div>
						</div>

						<div class="row mt50">
							<div class="col-4 flex-col">
								<p class="mt20 op7">
									<input 
										type="checkbox" 
										class="pointer" 
										id="review-check-8" 
										v-model="review_check_8"
									>
									<label for="review-check-8" class="fs12 italic ml5 pointer">
										Reviewed
									</label>
								</p>

								<button class="btn btn-yellow mt15">
									Make Changes
								</button>
							</div>

							<div class="col-8">
								<p class="mt20 fs12 italic">
									Section 8
								</p>

								<table>
									<tr>
										<td class="tr-left bold">
											Beneficial Owners
										</td>

										<ClipLoader 
											v-if="loading" 
											size="15px" 
											:color="this.$root.color_primary"
											class="inline"
										></ClipLoader>
										<div v-else>
											<td 
												v-if="report.pii_data.beneficial_owners.length == 0"
												class="tr-right bold text-red"
											>
												Incomplete
											</td>
											<td 
												v-else
												class="tr-right bold text-darkgreen"
											>
												Complete
											</td>
										</div>
									</tr>
								</table>

								<table 
									v-for="(owner, key, index) in report.pii_data.beneficial_owners"
									class="mt20"
								>
									<tr>
										<td class="tr-left bold">
											Beneficial Owner #{{ index + 1 }}
										</td>
									</tr>

									<tr>
										<td class="tr-left">
											FinCEN ID
										</td>
										<td
											v-if="owner.has_fincen_id"
											class="tr-right bold"
										>
											{{ owner.fincen_id }} 
										</td>
										<td
											v-else
											class="tr-right bold"
										>
											No 
										</td>
									</tr>

									<tr>
										<td class="tr-left">
											Exempt Entity
										</td>
										<td 
											v-if="owner.has_fincen_id"
											class="tr-right bold"
										>
											Yes
										</td>
										<td 
											v-else
											class="tr-right bold"
										>
											No
										</td>
									</tr>

									<tr 
										v-if="
											owner.is_exempt_entity &&
											!owner.has_fincen_id
										"
									>
										<td class="tr-left">
											Exempt Entity Name
										</td>
										<td class="tr-right bold">
											{{ owner.exempt_entity_name }} 
										</td>
									</tr>

									<tr 
										v-if="
											!owner.is_exempt_entity &&
											!owner.has_fincen_id
										"
									>
										<td class="tr-left">
											First Name
										</td>
										<td class="tr-right bold">
											{{ owner.first_name }} 
										</td>
									</tr>

									<tr 
										v-if="
											!owner.is_exempt_entity &&
											!owner.has_fincen_id
										"
									>
										<td class="tr-left">
											Middle Name
										</td>
										<td class="tr-right bold">
											{{ owner.middle_name }} 
										</td>
									</tr>

									<tr 
										v-if="
											!owner.is_exempt_entity &&
											!owner.has_fincen_id
										"
									>
										<td class="tr-left">
											Last Name
										</td>
										<td class="tr-right bold">
											{{ owner.last_name }}
										</td>
									</tr>

									<tr 
										v-if="
											!owner.is_exempt_entity &&
											!owner.has_fincen_id
										"
									>
										<td class="tr-left">
											Suffix
										</td>
										<td class="tr-right bold">
											{{ owner.suffix }}
										</td>
									</tr>

									<tr 
										v-if="
											!owner.is_exempt_entity &&
											!owner.has_fincen_id
										"
									>
										<td class="tr-left">
											Date of Birth
										</td>
										<td class="tr-right bold">
											{{ owner.date_of_birth }}
										</td>
									</tr>

									<tr v-if="!owner.has_fincen_id">
										<td class="tr-left">
											Country
										</td>
										<td class="tr-right bold">
											{{ owner.country }}
										</td>
									</tr>

									<tr v-if="!owner.has_fincen_id">
										<td class="tr-left">
											Address Line 1
										</td>
										<td class="tr-right bold">
											{{ owner.us_address_1 }}
										</td>
									</tr>

									<tr 
										v-if="
											owner.us_address_2 &&
											!owner.has_fincen_id
										"
									>
										<td class="tr-left">
											Address Line 2
										</td>
										<td class="tr-right bold">
											{{ owner.us_address_2 }}
										</td>
									</tr>

									<tr 
										v-if="
											owner.us_city &&
											!owner.has_fincen_id
										"
									>
										<td class="tr-left">
											City
										</td>
										<td class="tr-right bold">
											{{ owner.us_city }}
										</td>
									</tr>

									<tr 
										v-if="
											owner.us_state &&
											!owner.has_fincen_id
										"
									>
										<td class="tr-left">
											State
										</td>
										<td class="tr-right bold">
											{{ owner.us_state }}
										</td>
									</tr>

									<tr 
										v-if="
											owner.us_zip &&
											!owner.has_fincen_id
										"
									>
										<td class="tr-left">
											Zip
										</td>
										<td class="tr-right bold">
											{{ owner.us_zip }}
										</td>
									</tr>
								</table>
							</div>
						</div>

						<div class="row mt50">
							<div class="col-4 flex-col">
								<p class="mt20 op7">
									<input 
										type="checkbox" 
										class="pointer" 
										id="review-check-9" 
										v-model="review_check_9"
									>
									<label for="review-check-9" class="fs12 italic ml5 pointer">
										Reviewed
									</label>
								</p>

								<button class="btn btn-yellow mt15">
									Make Changes
								</button>
							</div>

							<div class="col-8">
								<p class="mt20 fs12 italic">
									Section 9
								</p>

								<table>
									<tr>
										<td class="tr-left bold pb20">
											Identification Docs
										</td>

										<ClipLoader 
											v-if="loading" 
											size="15px" 
											:color="this.$root.color_primary"
											class="inline"
										></ClipLoader>
									</tr>

									<tr
										v-for="(applicant, key, index) in report.pii_data.applicants"
									>
										<td class="tr-left">
											Applicant #{{ index + 1 }}
										</td>

										<td 
											v-if="applicant.identifying_document.file_url"
											class="tr-right bold"
										>
											Document Uploaded 
											<span
												class="bold underline pointer text-blue"
												@click="openLink(applicant.identifying_document.file_url)"
											>
												View
											</span>
										</td>

										<td 
											v-else
											class="tr-right"
										>
											No Document Uploaded 
										</td>
									</tr>

									<tr
										v-for="(owner, key, index) in report.pii_data.beneficial_owners"
									>
										<td class="tr-left">
											Beneficial Owner #{{ index + 1 }}
										</td>

										<td 
											v-if="owner.identifying_document.file_url"
											class="tr-right bold"
										>
											Document Uploaded 
											<span
												class="bold underline pointer text-blue"
												@click="openLink(owner.identifying_document.file_url)"
											>
												View
											</span>
										</td>

										<td 
											v-else
											class="tr-right"
										>
											No Document Uploaded 
										</td>
									</tr>
								</table>
							</div>
						</div>
					</div>
				</div>

				<p class="pt10">
					<input 
						type="checkbox" 
						class="pointer" 
						id="affirmation"
						v-model="affirmation"
					>
					<label for="affirmation" class="ml10 pointer nostyle text-blue">
						I have reviewed all the information above and verified that it is correct.
					</label>
				</p>

				<button 
					class="btn btn-green mt30"
					:class="
						review_check_1 &&
						review_check_2 &&
						review_check_3 &&
						review_check_4 &&
						review_check_5 &&
						review_check_6 &&
						review_check_7 &&
						review_check_8 &&
						review_check_9 &&
						affirmation    ?
						'' :
						'div-disabled'
					"
				>
					Complete Review
				</button>

				<div class="pt100"></div>
			</div>
		</div>
	</div>

</template>

<style scoped>

.firm-report-card {
	box-shadow: 0px 1px 8px rgba(0,0,0,0.15);
	min-height: 80vh;
	padding-bottom: 50px;
	margin-bottom: 20px;
}

.blue-header {
	background-color: var(--color-primary);
	color: white;
	padding: 40px;
	margin-top: 30px;
}

.tr-left {
	width: 250px
}

.tr-right {
	width: auto;
	padding-left: 20px;
	padding-right: 20px;
}

</style>