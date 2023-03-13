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
		async saveAddress() {
			let params = {
				report_guid:     this.$parent.report_guid,
				data_point:      'address',
				office_address1: this.$refs['address1'].value,
				office_address2: this.$refs['address2'].value,
				office_city:     this.$refs['city'].value,
				office_state:    this.$refs['state'].value,
				office_zip:      this.$refs['zip'].value
			}

			if (
				!params.office_address1 ||
				params.office_address1  == ''
			) {
				this.$root.toast(
					'',
					'Please enter primary office address',
					'error'
				);
				return false;
			}

			if (
				!params.office_city ||
				params.office_city  == ''
			) {
				this.$root.toast(
					'',
					'Please enter primary office city',
					'error'
				);
				return false;
			}

			if (
				!params.office_state ||
				params.office_state  == ''
			) {
				this.$root.toast(
					'',
					'Please select primary office state',
					'error'
				);
				return false;
			}

			if (
				!params.office_zip ||
				params.office_zip  == ''
			) {
				this.$root.toast(
					'',
					'Please enter primary office zip code',
					'error'
				);
				return false;
			}

			this.$parent.saveProgress(params);
		}
	}
};

</script>

<template>
	<p class="bold fs20">
		Primary address of business operations
	</p>

	<p class="mt20">
		Enter the U.S. address where the majority of your business is conducted. This can be a home address if that is the primary location where your business performs its work.
	</p>

	<p class="mt20 bold">
		Note: FinCEN does not allow submitting registered agent addresses, PO boxes, or other third party addresses. These may only be used by businesses based outside the U.S. that have no other U.S. address.
	</p>

	<p class="op7 fs14 mt20">
		Street Address
	</p>
	<input 
		type="text" 
		class="form-control fincen-input width-400 mt5" 
		ref="address1"
		:value="this.$parent.report.pii_data.us_office_address_1"
	>

	<p class="op7 fs14 mt20">
		Apt, Suite, Etc (optional)
	</p>
	<input 
		type="text" 
		class="form-control fincen-input width-400 mt5" 
		ref="address2"
		:value="this.$parent.report.pii_data.us_office_address_2"
	>

	<p class="op7 fs14 mt20">
		City
	</p>
	<input 
		type="text" 
		class="form-control fincen-input width-400 mt5" 
		ref="city"
		:value="this.$parent.report.pii_data.us_office_city"
	>

	<div class="form-group">
		<div class="inline mr10">
			<p class="op7 fs14 mt20">
				State
			</p>
			<select
				class="form-select fincen-input width-200 mt5 pointer" 
				ref="state"
				:value="this.$parent.report.pii_data.us_office_state"
			>
				<option value="this.$parent.report.pii_data.us_office_state">
					{{ this.$parent.report.pii_data.us_office_state }}
				</option>

				<option v-for="state in this.$parent.us_states">
					{{ state }}
				</option>
			</select>
		</div>

		<div class="inline">
			<p class="op7 fs14 mt20">
				Zip
			</p>
			<input 
				type="text" 
				class="form-control fincen-input width-200 mt5" 
				ref="zip"
				:value="this.$parent.report.pii_data.us_office_zip"
				:onkeydown="this.$root.inputIsZipCodeFormat"
			>
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
			@click="this.saveAddress"
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