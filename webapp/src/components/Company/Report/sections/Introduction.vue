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
		async saveIntroduction() {
			let params = {
				report_guid: this.$parent.report_guid,
				data_point:  'intro',
				intro:       true
			}

			this.$parent.saveProgress(params);
		}
	}
};

</script>

<template>
	<!-- returning -->
	<div v-if="this.$parent.progress.intro">

		<!-- report is done and returned to firm -->
		<div v-if="this.$parent.report.report_returned">
			<p class="bold fs20">
				Well done!
			</p>

			<p class="mt20">
				You have completed the report information submission process and your filer should have everything they need to file your beneficial ownership report with FinCEN. This system has let them know your information is complete so they can take next steps on your behalf.
			</p>

			<p class="mt20">
				Please save your log in details for this site as you can view your reports again at a later time. Thanks for using FincenFetch!
			</p>

			<button class="btn btn-yellow mt40 mr10 form-control width-200 mb100" @click="this.$root.routeTo('/c/reports')">
				Back to My Reports
			</button>

			<button class="btn btn-success mt40 form-control width-200 mb100" @click="this.$root.routeTo('/c/logout')">
				Log Out
			</button>
		</div>

		<!-- not done yet -->
		<div v-else>
			<p class="bold fs20">
				Welcome back to your report
			</p>

			<p class="mt20">
				The navigation bar on the left shows all sections that you need to complete.
			</p>

			<p class="mt20">
				Please complete sections not yet marked with a blue check mark. You can click any of those to continue, or click the yellow button below to go to the first unfinished section.
			</p>

			<button class="btn btn-yellow mt40 width-300 mb100" @click="this.$parent.pickupBookmark">
				Go to first unfinished section
			</button>
		</div>
	</div>

	<!-- first time -->
	<div v-else>
		<p class="bold fs20">
			Welcome to the fastest way to file your beneficial ownership report
		</p>

		<p class="mt20">
			FincenFetch strives to make the filing process as painless as possible and get you through your filing process in 30 minutes or less for first time filers.
		</p>

		<p class="mt20">
			The following tips will help you use FincenFetch:
		</p>

		<ol>
			<li class="mt20" data-icon="1.">
				You can click the yellow button on the left to ask a question at any time. Our team will typically get you an answer within 24 hours.
			</li>
			<li class="mt20" data-icon="2.">
				You do not need to finish in one sitting. The blue button on the left will let you save your place and come back to this report later. 
			</li>
			<li class="mt20" data-icon="3.">
				We recommend completing the steps in order. Completed steps will be marked with a blue checkmark on the menu to the left. You can move between sections using this menu.
			</li>
			<li class="mt20" data-icon="4.">
				Each section will have important information you need to know in order to submit accurate information for your report. Please read those tooltips outlined in yellow as you complete your report.
			</li>
		</ol>

		<button class="btn btn-yellow mt40 width-200 mb100" @click="this.saveIntroduction">
			Begin Report
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