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
		async saveDbas() {
			let params = {
				report_guid:  this.$parent.report_guid,
				data_point:   'dbas'
			}

			let ret_false = false;

			Object.values(this.$parent.dbas_clone).forEach((value, index) => {
				if (
					!value ||
					value  == ''
				) {
					this.$root.toast(
						'',
						'Please enter a DBA',
						'error'
					);
					ret_false = true;
				}
			});

			if (ret_false) {
				return false;
			}

			params.dbas = this.$parent.dbas_clone;
			this.$parent.saveProgress(params);
		}
	}
};

</script>

<template>
	<p class="bold fs20">
		DBAs for your company
	</p>

	<p class="mt20">
		If your business uses any DBAs (doing business as) or trade names, list these below. DBAs should be listed whether or not they are registered with your state.
	</p>

	<p class="mt20">
		If you do not use any DBAs, simple click below to continue to the next step.
	</p>

	<div
		class="width-400"
		v-for="(dba, index) in this.$parent.dbas_clone"
	>
		<p class="op7 fs14 mt20">
			Company Name
		</p>
		<input 
			type="text" 
			class="form-control fincen-input eyeball-input mt5"
			v-model="this.$parent.dbas_clone[index]"
		>
		<div class="eyeball" @click="this.$parent.removeDBA(index)">
			<i class="fa fa-trash text-red pointer"></i>
		</div>
	</div>

	<button class="btn btn-success width-150 mt20" @click="this.$parent.addDBA">
		<i class="fa fa-plus"></i>
		Add DBA
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
			@click="this.saveDbas"
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