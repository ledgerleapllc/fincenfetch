<script>

import { api } from '../../../api.js';
import ClipLoader from 'vue-spinner/src/ClipLoader.vue';
import { Modal } from 'vue-neat-modal';
import Datepicker from '@vuepic/vue-datepicker';

import '@vuepic/vue-datepicker/dist/main.css'

import 'vue-neat-modal/dist/vue-neat-modal.css';

export default {
	data() {
		return {
		}
	},

	components: {
		ClipLoader,
		Modal,
		Datepicker
	},

	created() {
	},

	mounted() {
		let that = this;
	},

	watch: {
	},

	methods: {
		async saveApplicants() {
			let params = {
				report_guid: this.$parent.report_guid,
				data_point:  'applicants',
				company_before_2024: this.$parent.report.pii_data.company_before_2024,
				applicant_needed:    this.$parent.report.pii_data.applicant_needed
			}

			if (
				this.$parent.report.pii_data.company_before_2024 !== true &&
				this.$parent.report.pii_data.company_before_2024 !== false
			) {
				this.$root.toast(
					'',
					'Please select if your company was formed before or after January 1st, 2024',
					'error'
				);
				return false;
			}

			if (this.$parent.report.pii_data.company_before_2024 === false) {
				if (
					this.$parent.applicant_has_fincen_id &&
					this.$parent.applicant_fincen_id == '' &&
					this.applicant_needed
				) {
					this.$root.toast(
						'',
						'Please enter applicant FinCEN ID number',
						'error'
					);
					return false;
				}
			}

			params.applicants = this.$parent.applicants_clone;

			if (
				params.applicant_needed === true &&
				params.applicants.length == 0
			) {
				this.$root.toast(
					'',
					'Please add and specify a company applicant if you received assistance from one',
					'error'
				);
				return false;
			}

			if (params.applicants.length > 0) {
				Object.values(params.applicants).forEach((applicant, index) => {
					if (applicant.date_of_birth) {
						applicant.date_of_birth = applicant.date_of_birth.replace(/\//g, '-');
					}

					applicant.has_fincen_id = this.$parent.applicant_has_fincen_id;
					applicant.fincen_id = this.$parent.applicant_fincen_id;
				});
			}

			this.$parent.saveProgress(params);
		}
	}
};

</script>

<template>
	<p class="bold fs20">
		Company applicant information
	</p>

	<p class="mt20">
		Company applicants are individuals who help to set up the business but do not go on to become an owner or decision maker. These individuals could be law firm staff members, for example, that only assisted with creating the company. If you formed or registered your company by yourself, then you do not need to provide any company applicant details.
	</p>

	<p class="mt20">
		Note: If your company was formed prior to January 1st, 2024, then you DO NOT need to provide company applicant information.
	</p>

	<p class="mt20">
		Was the company formed or registered before January 1st, 2024?
	</p>

	<div class="form-group mt15">
		<input 
			type="radio" 
			name="company_before_2024" 
			id="company_before_2024_1" 
			class="form-radio pointer"
			v-model="this.$parent.report.pii_data.company_before_2024"
			:value="true"
		>

		<label for="company_before_2024_1" class="pointer pl10">
			<span>
				Yes
			</span>
		</label>
	</div>

	<div class="form-group">
		<input 
			type="radio" 
			name="company_before_2024" 
			id="company_before_2024_2" 
			class="form-radio pointer"
			v-model="this.$parent.report.pii_data.company_before_2024"
			:value="false"
		>
		<label for="company_before_2024_2" class="pointer pl10">
			<span>
				No
			</span>
		</label>
	</div>

	<p 
		v-if="this.$parent.report.pii_data.company_before_2024 === true"
		class="bold italic mt20" 
	>
		Thank you. You do not need to provide company applicant information.
	</p>

	<div v-if="this.$parent.report.pii_data.company_before_2024 === false">
		<p class="mt20">
			Did any non-owner assist you in forming or registering your company, such as a law firm?
		</p>

		<div class="form-group mt15">
			<input 
				type="radio" 
				name="applicant_needed" 
				id="applicant_needed_1" 
				class="form-radio pointer"
				v-model="this.$parent.report.pii_data.applicant_needed"
				:value="true"
			>

			<label for="applicant_needed_1" class="pointer pl10">
				<span>
					Yes
				</span>
			</label>
		</div>

		<div class="form-group">
			<input 
				type="radio" 
				name="applicant_needed" 
				id="applicant_needed_2" 
				class="form-radio pointer"
				v-model="this.$parent.report.pii_data.applicant_needed"
				:value="false"
			>
			<label for="applicant_needed_2" class="pointer pl10">
				<span>
					No
				</span>
			</label>
		</div>

		<p 
			v-if="this.$parent.report.pii_data.applicant_needed === false"
			class="bold italic mt20" 
		>
			Thank you. You do not need to provide company applicant information.
		</p>

		<div v-if="this.$parent.report.pii_data.applicant_needed">
			<p class="mt20">
				Has your company applicant provided you with a FinCEN ID?
			</p>

			<div class="form-group mt15">
				<input 
					type="radio" 
					name="applicant_has_id" 
					id="applicant_has_id_1" 
					class="form-radio pointer"
					v-model="this.$parent.applicant_has_fincen_id"
					:value="true"
				>

				<label for="applicant_has_id_1" class="pointer pl10">
					<span>
						Yes
					</span>
				</label>
			</div>

			<div class="form-group">
				<input 
					type="radio" 
					name="applicant_has_id" 
					id="applicant_has_id_2" 
					class="form-radio pointer"
					v-model="this.$parent.applicant_has_fincen_id"
					:value="false"
				>
				<label for="applicant_has_id_2" class="pointer pl10">
					<span>
						No
					</span>
				</label>
			</div>

			<div v-if="this.$parent.applicant_has_fincen_id">
				<p class="op7 fs14 mt20">
					Enter their FinCEN ID
				</p>
				<input 
					type="text" 
					class="form-control fincen-input width-600 mt5" 
					v-model="this.$parent.applicant_fincen_id"
				>
			</div>

			<div v-else>
				<div v-for="(applicant, index) in this.$parent.applicants_clone">
					<p class="mt20 bold fs20">
						Company Applicant #{{ index + 1 }}
					</p>

					<p class="mt20">
						Enter the information for the company applicant.
					</p>

					<p class="op7 fs14 mt20">
						First Name
					</p>
					<input 
						type="text" 
						class="form-control fincen-input width-600 mt5" 
						v-model="this.$parent.applicants_clone[index].first_name"
					>

					<p class="op7 fs14 mt20">
						Middle Name / Initial (optional)
					</p>
					<input 
						type="text" 
						class="form-control fincen-input width-600 mt5" 
						v-model="this.$parent.applicants_clone[index].middle_name"
					>

					<p class="op7 fs14 mt20">
						Last Name
					</p>
					<input 
						type="text" 
						class="form-control fincen-input width-600 mt5" 
						v-model="this.$parent.applicants_clone[index].last_name"
					>

					<div class="sub-view width-600">
						<div style="width: 50%;">
							<p class="op7 fs14 mt20">
								Suffix
							</p>
							<select 
								class="form-select fincen-input mt5 pointer" 
								v-model="this.$parent.applicants_clone[index].suffix"
							>
								<option 
									v-for="suffix in this.$parent.suffixs"
									:selected="this.$parent.applicants_clone[index].suffix == suffix"
								>
									{{ suffix }}
								</option>
							</select>
						</div>

						<div style="width: calc(50% - 10px); margin-left: 10px;">
							<p class="op7 fs14 mt20">
								Date of Birth
							</p>
							<input 
								type="text" 
								class="form-control fincen-input width-600 mt5" 
								v-model="this.$parent.applicants_clone[index].date_of_birth"
								placeholder="YYYY/mm/dd"
								:onkeydown="this.$root.inputIsDateFormat" 
							>
						</div>
					</div>

					<div class="sub-view width-600">
						<div style="width: 50%;">
							<p class="op7 fs14 mt20">
								Country
							</p>
							<select 
								class="form-select fincen-input mt5 pointer" 
								v-model="this.$parent.applicants_clone[index].country"
							>
								<option 
									v-for="country in this.$parent.countries"
									:selected="this.$parent.applicants_clone[index].country == country"
								>
									{{ country }}
								</option>
							</select>
						</div>

						<div style="width: calc(50% - 10px); margin-left: 10px;">
							<p class="op7 fs14 mt20">
								Address Type
							</p>
							<select 
								class="form-select fincen-input mt5 pointer" 
								v-model="this.$parent.applicants_clone[index].address_type"
							>
								<option 
									v-for="address_type in this.$parent.address_types"
									:selected="this.$parent.applicants_clone[index].address_type == address_type"
								>
									{{ address_type }}
								</option>
							</select>
						</div>
					</div>

					<p class="op7 fs14 mt20">
						Street Address
					</p>
					<input 
						type="text" 
						class="form-control fincen-input width-600 mt5" 
						v-model="this.$parent.applicants_clone[index].us_address_1"
					>

					<p class="op7 fs14 mt20">
						Apt. Suite. Etc (optional)
					</p>
					<input 
						type="text" 
						class="form-control fincen-input width-600 mt5" 
						v-model="this.$parent.applicants_clone[index].us_address_2"
					>

					<p class="op7 fs14 mt20">
						City
					</p>
					<input 
						type="text" 
						class="form-control fincen-input width-600 mt5" 
						v-model="this.$parent.applicants_clone[index].us_city"
					>

					<div class="sub-view width-600">
						<div style="width: 50%;">
							<p class="op7 fs14 mt20">
								State
							</p>
							<select
								class="form-select fincen-input mt5 pointer" 
								v-model="this.$parent.applicants_clone[index].us_state"
							>
								<option 
									v-for="state in this.$parent.us_states"
									:selected="this.$parent.applicants_clone[index].us_state == state"
								>
									{{ state }}
								</option>
							</select>
						</div>

						<div style="width: calc(50% - 10px); margin-left: 10px;">
							<p class="op7 fs14 mt20">
								Zip
							</p>
							<input 
								type="text" 
								class="form-control fincen-input mt5"
								:onkeydown="this.$root.inputIsZipCodeFormat" 
								v-model="this.$parent.applicants_clone[index].us_zip"
							>
						</div>
					</div>

					<div class="btn btn-danger mt20" @click="this.$parent.removeApplicant(index)">
						Remove
					</div>
				</div>

				<button 
					v-if="this.$parent.applicants_clone.length < 2"
					class="btn btn-success width-300 mt40" 
					@click="this.$parent.addApplicant"
				>
					<i class="fa fa-plus"></i>
					Add Company Applicant
				</button>
			</div>
		</div>
	</div>

	<div class="mt40 mb100 width-300">
		<ClipLoader 
			v-if="this.$parent.loading" 
			size="25px" 
			:color="this.$root.color_primary"
			class="pt10"
		></ClipLoader>
		<button 
			v-else 
			class="btn btn-yellow form-control" 
			@click="this.saveApplicants"
		>
			Continue to next step
		</button>
	</div>
</template>

<style scoped>

.tr-left {
	width: 250px
}

.tr-right {
	width: auto;
	padding-left: 20px;
	padding-right: 20px;
}

.tr-check {
	width: 30px;
	vertical-align: top;
}

ol {
	margin: 0;
	margin-left: 20px;
	padding: 0;
	list-style: none;
	display: grid;
}

ol li {
	display: grid;
	grid-template-columns: 0 1fr;
	gap: 1.75em;
	align-items: start;
}

ol li::before {
	content: attr(data-icon);
	font-size: 18px;
	font-weight: bold;
	color: var(--color-primary);
	margin-top: -2px;
}

</style>