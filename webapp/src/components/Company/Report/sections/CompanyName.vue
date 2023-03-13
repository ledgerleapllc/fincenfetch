<script>

import { api } from '../../../api.js';
import ClipLoader from 'vue-spinner/src/ClipLoader.vue';
import { Modal } from 'vue-neat-modal';
import Popper from 'vue3-popper';

import 'vue-neat-modal/dist/vue-neat-modal.css';

export default {
	data() {
		return {
		}
	},

	components: {
		ClipLoader,
		Modal,
		Popper
	},

	created() {
	},

	mounted() {
		let that = this;
	},

	watch: {
	},

	methods: {
		async saveCompanyName() {
			let params = {
				report_guid:  this.$parent.report_guid,
				data_point:   'company_name',
				company_name: this.$refs['company_name'].value,
				report_type:  this.$refs['report_type'].value
			}

			let foreign_investment1 = this.$refs['foreign_investment1'].checked;
			let foreign_investment2 = this.$refs['foreign_investment2'].checked;

			if (foreign_investment1) {
				params.foreign_investment = true;
			}

			if (foreign_investment2) {
				params.foreign_investment = false;
			}

			let exempt1 = this.$refs['exempt1'].checked;
			let exempt2 = this.$refs['exempt2'].checked;

			if (exempt1) {
				params.exempt = true;
			}

			if (exempt2) {
				params.exempt = false;
			}

			if (
				!params.company_name ||
				params.company_name == ''
			) {
				this.$root.toast(
					'',
					'Please enter company name to continue',
					'error'
				);
				return false;
			}

			if (
				params.report_type != 'initial' &&
				params.report_type != 'updated' &&
				params.report_type != 'correction'
			) {
				this.$root.toast(
					'',
					'Please select a report type option to continue',
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
		Company Name
	</p>

	<p class="mt20">
		Please verify the registered legal name of your business is listed in the box below. Make changes if needed.
	</p>

	<p class="op7 fs14 mt20">
		Company Name
	</p>
	<input 
		type="text" 
		class="form-control fincen-input width-400 mt5" 
		ref="company_name"
		:value="
			this.$parent.report.pii_data.company_name
			? this.$parent.report.pii_data.company_name
			: this.$parent.report.company.pii_data.name
		"
	>

	<p class="mt20">
		Is this an updated report or correction of a previously filed report?
	</p>

	<select
		class="form-select fincen-input width-400 mt5 pointer" 
		ref="report_type"
		:value="this.$parent.report.report_type"
	>
		<option
			v-for="report_type in this.$parent.report_types"
			:value="report_type"
		>
			{{
				report_type == 'initial' ? 'No' : this.$root.ucfirst(report_type)
			}}
		</option>
	</select>

	<p class="mt40">
		Is the company a foreign Pooled Investment Vehicle? 
		<Popper hover arrow placement="right" class="fs12" content="Is this company a foreign pooled investment company?">
			<i class="fa fa-info-circle text-blue pointer ml5 fs16"></i>
		</Popper>
	</p>

	<div class="form-group mt15">
		<input 
			type="radio" 
			name="foreign_investment" 
			id="foreign_investment1" 
			class="form-radio pointer"
			ref="foreign_investment1"
			:checked="this.$parent.report.pii_data.foreign_investment"
		>

		<label for="foreign_investment1" class="pointer pl10">
			<span>
				Yes
			</span>
		</label>
	</div>

	<div class="form-group">
		<input 
			type="radio" 
			name="foreign_investment" 
			id="foreign_investment2" 
			class="form-radio pointer"
			ref="foreign_investment2"
			:checked="!this.$parent.report.pii_data.foreign_investment"
		>
		<label for="foreign_investment2" class="pointer pl10">
			<span>
				No
			</span>
		</label>
	</div>

	<p class="mt40">
		Is this company now exempt from reporting beneficial ownership information? 
		<Popper hover arrow placement="right" class="fs12" content="Is this company now exempt from reporting beneficial ownership information?">
			<i class="fa fa-info-circle text-blue pointer ml5 fs16"></i>
		</Popper>
	</p>

	<div class="form-group mt15">
		<input 
			type="radio" 
			name="exempt" 
			id="exempt1" 
			class="form-radio pointer"
			ref="exempt1"
			:checked="this.$parent.report.pii_data.exempt"
		>

		<label for="exempt1" class="pointer pl10">
			<span>
				Yes
			</span>
		</label>
	</div>

	<div class="form-group">
		<input 
			type="radio" 
			name="exempt" 
			id="exempt2" 
			class="form-radio pointer"
			ref="exempt2"
			:checked="!this.$parent.report.pii_data.exempt"
		>
		<label for="exempt2" class="pointer pl10">
			<span>
				No
			</span>
		</label>
	</div>

	<p class="mt40">
		Would you like to request a FinCEN identification number (FinCEN ID)?
		<Popper hover arrow placement="bottom" class="fs12" content="FinCEN identification numbers are references to pre-checked registrants">
			<i class="fa fa-info-circle text-blue pointer ml5 fs16"></i>
		</Popper>
	</p>

	<div class="form-group mt15">
		<input 
			type="radio" 
			name="request_fincen_number" 
			id="request_fincen_number1" 
			class="form-radio pointer"
			ref="request_fincen_number1"
			:checked="this.$parent.report.pii_data.request_fincen_number"
		>

		<label for="request_fincen_number1" class="pointer pl10">
			<span>
				Yes
			</span>
		</label>
	</div>

	<div class="form-group">
		<input 
			type="radio" 
			name="request_fincen_number" 
			id="request_fincen_number2" 
			class="form-radio pointer"
			ref="request_fincen_number2"
			:checked="!this.$parent.report.pii_data.request_fincen_number"
		>
		<label for="request_fincen_number2" class="pointer pl10">
			<span>
				No
			</span>
		</label>
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
			@click="this.saveCompanyName"
		>
			Confirm Name & Continue
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