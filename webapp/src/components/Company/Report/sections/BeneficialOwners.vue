<script>

import { api } from '../../../api.js';
import ClipLoader from 'vue-spinner/src/ClipLoader.vue';
import { Modal } from 'vue-neat-modal';

import 'vue-neat-modal/dist/vue-neat-modal.css';

export default {
	data() {
		return {
		}
	},

	components: {
		ClipLoader,
		Modal
	},

	created() {
	},

	mounted() {
		let that = this;
	},

	watch: {
	},

	methods: {
		async saveBeneficialOwners() {
			let params = {
				report_guid: this.$parent.report_guid,
				data_point:  'beneficial_owners'
			}

			if (this.$parent.beneficial_owners_clone.length > 0) {
				Object.values(this.$parent.beneficial_owners_clone).forEach((owner, index) => {
					if (
						owner.has_fincen_id &&
						owner.fincen_id == ''
					) {
						this.$root.toast(
							'',
							'Please enter a beneficial owner FinCEN ID',
							'error'
						);
						return false;
					}

					owner.date_of_birth = owner.date_of_birth.replace(/\//g, '-');
				});
			}

			else {
				this.$root.toast(
					'',
					'Please enter a beneficial owner',
					'error'
				);
				return false;
			}

			params.beneficial_owners = this.$parent.beneficial_owners_clone;

			this.$parent.saveProgress(params);
		}
	}
};

</script>

<template>
	<p class="bold fs20">
		Beneficial owners of the business
	</p>

	<p class="mt20">
		List all beneficial owners of the business entity. A beneficial owner is defined as an individual that meets any of the following criteria:
	</p>

	<div class="mt20 p25 bg-yellow">
		<ul>
			<li>
				Owns 25% of more of the reporting company, regardless if ownership is through shares, management units, or other structures leading to 25% or more ownership, profit distributions, or power over voting.
			</li>
			<li class="mt20">
				Has substantial control of the company by serving as a senior officer of the reporting company.
			</li>
			<li class="mt20">
				Has authority over the appointment or removal of any senior officer or a majority of the board of directors (or similar body) of the reporting company.
			</li>
			<li class="mt20">
				Can direct, determine or create substantial influence over important matters of the reporting company, including, for example, the reorganization, dissolution or merger of the reporting company, the selection or termination of business lines or ventures of the reporting company and the amendment of any governance documents of the reporting company.
			</li>
			<li class="mt20">
				Has any other form of substantial control over the reporting company.
			</li>
		</ul>
	</div>

	<p class="mt20">
		FinCEN’s published rules for determined beneficial ownership are broad. If you are uncertain whether or not an individual is a beneficial owner, we recommend including them on this page as there is no penalty for over-reporting, but penalties do exist for excluding beneficial owners. More information to help determine beneficial owners can be found here.
	</p>

	<div v-for="(owner, index) in this.$parent.beneficial_owners_clone">
		<p class="mt20 bold fs20">
			Beneficial Owner #{{ index + 1 }}
		</p>

		<p class="mt20">
			Enter the legal name, date of birth, and current residential address of the beneficial owner below. Addresses must be residential addresses. PO boxes or office addresses are not allowed by FinCEN.
		</p>

		<p class="mt20">
			Does this beneficial owner have a FinCEN ID?
		</p>

		<div class="form-group mt15">
			<input 
				type="radio" 
				:name="'has_fincen_id_'+index" 
				:id="'has_fincen_id_true_'+index" 
				class="form-radio pointer"
				v-model="this.$parent.beneficial_owners_clone[index].has_fincen_id"
				:value="true"
			>

			<label :for="'has_fincen_id_true_'+index" class="pointer pl10">
				<span>
					Yes
				</span>
			</label>
		</div>

		<div class="form-group">
			<input 
				type="radio" 
				:name="'has_fincen_id_'+index" 
				:id="'has_fincen_id_false_'+index" 
				class="form-radio pointer"
				v-model="this.$parent.beneficial_owners_clone[index].has_fincen_id"
				:value="false"
			>
			<label :for="'has_fincen_id_false_'+index" class="pointer pl10">
				<span>
					No
				</span>
			</label>
		</div>

		<div v-if="owner.has_fincen_id">
			<p class="op7 fs14 mt20">
				Enter their FinCEN ID
			</p>
			<input 
				type="text" 
				class="form-control fincen-input width-600 mt5" 
				v-model="owner.fincen_id"
			>
		</div>

		<div v-else>
			<p class="mt20">
				Is this beneficial owner an Exempt Entity?
			</p>

			<div class="form-group mt15">
				<input 
					type="radio" 
					:name="'exempt_entity_'+index" 
					:id="'exempt_entity_true_'+index" 
					class="form-radio pointer"
					v-model="this.$parent.beneficial_owners_clone[index].is_exempt_entity"
					:value="true"
				>

				<label :for="'exempt_entity_true_'+index" class="pointer pl10">
					<span>
						Yes
					</span>
				</label>
			</div>

			<div class="form-group">
				<input 
					type="radio" 
					:name="'exempt_entity_'+index" 
					:id="'exempt_entity_false_'+index" 
					class="form-radio pointer"
					v-model="this.$parent.beneficial_owners_clone[index].is_exempt_entity"
					:value="false"
				>
				<label :for="'exempt_entity_false_'+index" class="pointer pl10">
					<span>
						No
					</span>
				</label>
			</div>

			<div v-if="owner.is_exempt_entity">
				<p class="op7 fs14 mt20">
					Company Name (Exempt entity)
				</p>
				<input 
					type="text" 
					class="form-control fincen-input width-600 mt5" 
					v-model="this.$parent.beneficial_owners_clone[index].exempt_entity_name"
				>
			</div>

			<div v-else>
				<p class="op7 fs14 mt20">
					First Name
				</p>
				<input 
					type="text" 
					class="form-control fincen-input width-600 mt5" 
					v-model="this.$parent.beneficial_owners_clone[index].first_name"
				>

				<p class="op7 fs14 mt20">
					Middle Name / Initial (optional)
				</p>
				<input 
					type="text" 
					class="form-control fincen-input width-600 mt5" 
					v-model="this.$parent.beneficial_owners_clone[index].middle_name"
				>

				<p class="op7 fs14 mt20">
					Last Name
				</p>
				<input 
					type="text" 
					class="form-control fincen-input width-600 mt5" 
					v-model="this.$parent.beneficial_owners_clone[index].last_name"
				>

				<div class="sub-view width-600">
					<div style="width: 50%;">
						<p class="op7 fs14 mt20">
							Suffix
						</p>
						<select 
							class="form-select fincen-input mt5 pointer" 
							v-model="this.$parent.beneficial_owners_clone[index].suffix"
						>
							<option 
								v-for="suffix in this.$parent.suffixs"
								:selected="this.$parent.beneficial_owners_clone[index].suffix == suffix"
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
							v-model="this.$parent.beneficial_owners_clone[index].date_of_birth"
							placeholder="YYYY/mm/dd"
							:onkeydown="this.$root.inputIsDateFormat" 
						>
					</div>
				</div>
			</div>

			<div class="sub-view width-600">
				<div style="width: 50%;">
					<p class="op7 fs14 mt20">
						Country
					</p>
					<select 
						class="form-select fincen-input mt5 pointer" 
						v-model="this.$parent.beneficial_owners_clone[index].country"
					>
						<option 
							v-for="country in this.$parent.countries"
							:selected="this.$parent.beneficial_owners_clone[index].country == country"
						>
							{{ country }}
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
				v-model="this.$parent.beneficial_owners_clone[index].us_address_1"
			>

			<p class="op7 fs14 mt20">
				Apt. Suite. Etc (optional)
			</p>
			<input 
				type="text" 
				class="form-control fincen-input width-600 mt5" 
				v-model="this.$parent.beneficial_owners_clone[index].us_address_2"
			>

			<p class="op7 fs14 mt20">
				City
			</p>
			<input 
				type="text" 
				class="form-control fincen-input width-600 mt5" 
				v-model="this.$parent.beneficial_owners_clone[index].us_city"
			>

			<div class="form-group width-600">
				<div style="width: calc(50% - 5px); display: inline-block;">
					<p class="op7 fs14 mt20">
						State
					</p>
					<select
						class="form-select fincen-input mt5 pointer" 
						v-model="this.$parent.beneficial_owners_clone[index].us_state"
					>
						<option value="">
							{{ this.$parent.beneficial_owners_clone[index].us_state }}
						</option>

						<option v-for="state in this.$parent.us_states">
							{{ state }}
						</option>
					</select>
				</div>

				<div style="width: calc(50% - 5px); display: inline-block; margin-left: 10px;">
					<p class="op7 fs14 mt20">
						Zip
					</p>
					<input 
						type="text" 
						class="form-control fincen-input mt5"
						:onkeydown="this.$root.inputIsZipCodeFormat" 
						v-model="this.$parent.beneficial_owners_clone[index].us_zip"
					>
				</div>
			</div>
		</div>

		<div class="btn btn-danger mt20" @click="this.$parent.removeBeneficialOwner(index)">
			Remove
		</div>
	</div>

	<button class="btn btn-success width-300 mt40" @click="this.$parent.addBeneficialOwner">
		<i class="fa fa-plus"></i>
		Add Beneficial Owner
	</button>

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
			@click="this.saveBeneficialOwners"
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