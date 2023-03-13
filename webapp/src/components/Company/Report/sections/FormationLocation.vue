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
		async saveFormationLocation() {
			let params = {
				report_guid:    this.$parent.report_guid,
				data_point:     'formation_location',
				state_or_tribe: this.$parent.state_or_tribe,

				company_origination_type: this.$parent.report.pii_data.company_origination_type,

				formation_date: this.$root.dateTimeToDate(
					this.$root.formatZDate(
						this.$parent.formation_date
					)
				)
			}

			if (this.$parent.report.pii_data.company_origination_type == 'domestic') {
				if (this.$parent.state_or_tribe == 'state') {
					params.state_of_formation = this.$parent.report.pii_data.state_of_formation;
				} else {
					params.tribe_of_formation = this.$parent.report.pii_data.tribe_of_formation;
				}

				if (
					(
						!params.state_of_formation ||
						params.state_of_formation  == ''
					) && (
						!params.tribe_of_formation ||
						params.tribe_of_formation  == ''
					)
				) {
					this.$root.toast(
						'',
						'Please select/enter a formation state/tribe',
						'error'
					);
					return false;
				}
			}

			if (this.$parent.report.pii_data.company_origination_type == 'foreign') {
				if (this.$parent.state_or_tribe == 'state') {
					params.state_of_registration = this.$parent.report.pii_data.state_of_registration;
				} else {
					params.tribe_of_registration = this.$parent.report.pii_data.tribe_of_registration;
				}

				if (
					(
						!params.state_of_registration ||
						params.state_of_registration  == ''
					) && (
						!params.tribe_of_registration ||
						params.tribe_of_registration  == ''
					)
				) {
					this.$root.toast(
						'',
						'Please select/enter a registration state/tribe',
						'error'
					);
					return false;
				}
			}

			if (
				this.$parent.report.pii_data.company_origination_type == 'domestic' &&
				this.$parent.report.pii_data.company_origination_type == 'foreign'
			) {
				this.$root.toast(
					'',
					'Please select where your company was formed',
					'error'
				);
				return false;
			}

			if (
				!params.formation_date ||
				params.formation_date  == ''
			) {
				this.$root.toast(
					'',
					'Please enter formation date',
					'error'
				);
				return false;
			}

			this.$parent.saveProgress(params);
		},

		switchToTribe() {
			this.$parent.state_or_tribe = 'tribe';
		},

		switchToState() {
			this.$parent.state_or_tribe = 'state';
		}
	}
};

</script>

<template>
	<p class="bold fs20">
		Location of Company Formation or Registration
	</p>

	<p class="mt20">
		Where was the company formed?
	</p>

	<div class="form-group mt15">
		<input 
			type="radio" 
			name="company_origination_type" 
			id="company_origination_type1" 
			class="form-radio pointer"
			v-model="this.$parent.report.pii_data.company_origination_type"
			value="domestic"
		>

		<label for="company_origination_type1" class="pointer pl10">
			<span class="fs15">
				Inside the U.S. (Domestic Company)
			</span>
		</label>
	</div>

	<div class="form-group mt5">
		<input 
			type="radio" 
			name="company_origination_type" 
			id="company_origination_type2" 
			class="form-radio pointer"
			v-model="this.$parent.report.pii_data.company_origination_type"
			value="foreign"
		>
		<label for="company_origination_type2" class="pointer pl10">
			<span class="fs15">
				Outside the U.S. and later registered in the U.S. (Foreign Company)
			</span>
		</label>
	</div>

	<p class="mt20">
		Please select the state (or tribal jurisdiction) where the business first submitted its documents to register the business such as articles of incorporation, organization, or similar formation documents. <span class="bold">This is generally the State where your business first registered.</span>
	</p>

	<p class="mt20">
		Note: For Foreign (non-U.S.) businesses, select the State where you registered as a foreign company to do business in the U.S.
	</p>

	<div v-if="this.$parent.report.pii_data.company_origination_type == 'domestic'">
		<div v-if="this.$parent.state_or_tribe == 'state'">
			<p class="op7 fs14 mt20">
				State of Formation
			</p>
			<select
				class="form-select fincen-input width-300 mt5 pointer" 
				v-model="this.$parent.report.pii_data.state_of_formation"
			>
				<option 
					v-for="state in this.$parent.us_states"
					:selected="this.$parent.report.pii_data.state_of_formation == state"
				>
					{{ state }}
				</option>
			</select>

			<p class="mt20">
				<span class="pointer text-blue underline" @click="switchToTribe">
					Enter Tribal Jurisdiction of Formation instead
				</span>
			</p>
		</div>

		<div v-else>
			<p class="op7 fs14 mt20">
				Tribe Jurisdiction of Formation
			</p>
			<input
				class="form-control fincen-input width-300 mt5" 
				v-model="this.$parent.report.pii_data.tribe_of_formation"
			>

			<p class="mt20">
				<span class="pointer text-blue underline" @click="switchToState">
					Enter State instead
				</span>
			</p>
		</div>
	</div>

	<div v-else>
		<div v-if="this.$parent.state_or_tribe == 'state'">
			<p class="op7 fs14 mt20">
				State of Registration
			</p>
			<select
				class="form-select fincen-input width-300 mt5 pointer" 
				v-model="this.$parent.report.pii_data.state_of_registration"
			>
				<option 
					v-for="state in this.$parent.us_states"
					:selected="this.$parent.report.pii_data.state_of_registration == state"
				>
					{{ state }}
				</option>
			</select>

			<p class="mt20">
				<span class="pointer text-blue underline" @click="switchToTribe">
					Enter Tribal Jurisdiction of Registration instead
				</span>
			</p>
		</div>

		<div v-else>
			<p class="op7 fs14 mt20">
				Tribe Jurisdiction of Registration
			</p>
			<input
				class="form-control fincen-input width-300 mt5" 
				v-model="this.$parent.report.pii_data.tribe_of_registration"
			>

			<p class="mt20">
				<span class="pointer text-blue underline" @click="switchToState">
					Enter State instead
				</span>
			</p>
		</div>
	</div>

	<p class="mt30">
		Enter the date your business submitted its formation documents. You can typically search for your business on your State’s Division of Corporations website to find this information if you do not have it available.
	</p>

	<p class="op7 fs14 mt20">
		Formation Date
	</p>
	<Datepicker 
		class=" width-400 mt5"
		:format="'yyyy/MM/dd'"
		:preview-format="'yyyy/MM/dd'"
		v-model="this.$parent.formation_date"
		utc
	></Datepicker>

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
			@click="this.saveFormationLocation"
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