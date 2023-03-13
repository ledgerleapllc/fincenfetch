<script>

import { api } from '../../../api.js';
import ClipLoader from 'vue-spinner/src/ClipLoader.vue';
import { Modal } from 'vue-neat-modal';

import 'vue-neat-modal/dist/vue-neat-modal.css';

export default {
	data() {
		return {
			tax_number_types: [
				'ein',
				'ssn'
			],
			selected_tax_number_type: this.$parent.report.pii_data.tax_number_type,
			selected_tax_country:     this.$parent.report.pii_data.foreign_tax_number_country
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
		async saveTaxNumber() {
			let params = {
				report_guid:     this.$parent.report_guid,
				data_point:      'tax_number',
				tax_number:      this.$refs['tax_number'].value,
				tax_number_type: this.selected_tax_number_type
			}

			if (this.selected_tax_number_type == 'foreign') {
				if (
					!this.selected_tax_country ||
					this.selected_tax_country == ''
				) {
					this.$root.toast(
						'',
						'Please select country of issuance',
						'error'
					);
					return false;
				}

				params.foreign_tax_number_country = this.selected_tax_country;
			}

			if (
				!params.tax_number ||
				params.tax_number  == ''
			) {
				this.$root.toast(
					'',
					'Please enter tax number',
					'error'
				);
				return false;
			}

			if (
				!params.tax_number_type ||
				params.tax_number_type  == ''
			) {
				this.$root.toast(
					'',
					'Please select a tax number type',
					'error'
				);
				return false;
			}

			this.$parent.saveProgress(params);
		},

		submitNonUSTaxNumber() {
			this.selected_tax_number_type = 'foreign';
		},

		submitUSTaxNumber() {
			this.selected_tax_number_type = this.$parent.report.pii_data.tax_number_type;
		}
	}
};

</script>

<template>
	<div v-if="selected_tax_number_type != 'foreign'">
		<p class="bold fs20">
			Tax Number
		</p>

		<p class="mt20">
			Please enter the tax identification number for your business such as your EIN (Employer Identification Number). FinCEN regulations require your business to obtain an EIN if you do not already have one prior to filing a beneficial ownership report. You can often get these immediately on the IRS website here if your business does not yet have one.
		</p>

		<p class="mt20">
			Note: In some rare cases, a foreign (non-U.S.) company will not be subject to U.S. tax based on the nature of their business and only in these cases may a tax identification number issued by a foreign nation be submitted.
		</p>

		<p class="op7 fs14 mt20">
			Tax Number Type
		</p>
		<select
			class="form-select fincen-input width-300 mt5 pointer" 
			v-model="selected_tax_number_type"
		>
			<option
				v-for="tax_number_type in tax_number_types"
				:selected="selected_tax_number_type == tax_number_type"
			>
				{{ tax_number_type }}
			</option>
		</select>

		<p class="op7 fs14 mt20">
			Tax Number
		</p>
		<input 
			type="text" 
			class="form-control fincen-input width-400 mt5" 
			ref="tax_number"
			:value="this.$parent.report.pii_data.tax_number"
		>

		<p class="mt20">
			<span class="pointer text-blue underline" @click="this.submitNonUSTaxNumber">
				Submit non-U.S. tax number instead
			</span>
		</p>
	</div>

	<div v-else>
		<p class="bold fs20">
			Tax Number (Foreign)
		</p>

		<p class="mt20">
			You have selected to enter a non-U.S. tax ID number. This is only allowed by FinCEN if the nature of your U.S. business activities does not require you to pay U.S. taxes or file returns and you currently have no U.S. EIN issued. 

		</p>

		<p class="mt20">
			Click the link below if you would like to submit a U.S. tax number instead.
		</p>

		<p class="op7 fs14 mt20">
			Country of Issuance
		</p>
		<select
			class="form-select fincen-input width-300 mt5 pointer" 
			v-model="selected_tax_country"
		>
			<option
				v-for="country in this.$parent.countries"
				:selected="selected_tax_country == country"
			>
				{{ country }}
			</option>
		</select>

		<p class="op7 fs14 mt20">
			Enter Tax Number
		</p>
		<input 
			type="text" 
			class="form-control fincen-input width-400 mt5" 
			ref="tax_number"
			:value="this.$parent.report.pii_data.tax_number"
		>

		<p class="mt20">
			<span class="pointer text-blue underline" @click="this.submitUSTaxNumber">
				Submit U.S. tax number instead
			</span>
		</p>
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
			@click="this.saveTaxNumber"
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