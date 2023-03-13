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
		async saveReview() {
			if (
				this.$parent.affirmation                   &&
				this.$parent.progress.intro                &&
				this.$parent.progress.company_name         &&
				this.$parent.progress.dbas                 &&
				this.$parent.progress.tax_number           &&
				this.$parent.progress.formation_location   &&
				this.$parent.progress.address              &&
				this.$parent.progress.applicants           &&
				this.$parent.progress.beneficial_owners    &&
				this.$parent.progress.identification_docs1 &&
				this.$parent.progress.identification_docs2
			) {
				let params = {
					report_guid: this.$parent.report_guid,
					data_point:  'review'
				}

				this.$parent.saveProgress(params);
			}
		},

		openLink(link) {
			window.open(link);
		}
	}
};

</script>

<template>
	<p class="bold fs20">
		Review
	</p>

	<p class="mt20">
		Please review all information below and complete any unfinished sections. You can click the make changes link for any section if updates are needed.
	</p>
	<p class="mt20 mb40">
		When your review is complete, we will alert your firm for them to complete a final review before filing your beneficial ownership report.
	</p>

	<hr>

	<p class="mt20 fs12 italic">
		Section 1
	</p>

	<table>
		<tr>
			<td class="tr-left bold">
				Introduction
			</td>
			<td 
				v-if="this.$parent.progress.intro" 
				class="tr-right text-darkgreen bold"
			>
				Complete
			</td>
			<td 
				v-else 
				class="tr-right text-red"
			>
				Incomplete
			</td>
		</tr>
	</table>

	<p class="mt40 fs12 italic">
		Section 2
	</p>

	<table>
		<tr>
			<td class="tr-left bold">
				Company Name
			</td>
			<td 
				v-if="this.$parent.progress.company_name" 
				class="tr-right text-darkgreen bold"
			>
				Complete
			</td>
			<td 
				v-else 
				class="tr-right text-red"
			>
				Incomplete
			</td>
		</tr>

		<tr>
			<td class="pt15 tr-left fs12">
				Company Name:
			</td>
			<td 
				v-if="this.$parent.progress.company_name"
				class="pt15 tr-right bold fs12"
			>
				{{ this.$parent.report.pii_data.company_name }}
			</td>
			<td 
				v-else
				class="pt15 tr-right bold fs12"
			>
				<label 
					class="fs12 underline pointer text-blue"
					@click="this.$root.routeTo('/c/report/'+this.$parent.report_guid+'/company-name')"
				>
					Return to section and finish
				</label>
			</td>
		</tr>

		<tr v-if="this.$parent.progress.company_name">
			<td class="pt5 tr-left fs12">
				Report Type:
			</td>
			<td class="pt5 tr-right bold fs12">
				{{
					this.$root.ucfirst(this.$parent.report.report_type)
				}} Report
			</td>
		</tr>

		<tr v-if="this.$parent.progress.company_name">
			<td class="pt5 tr-left fs12">
				Foreign pooled investment vehicle?
			</td>
			<td class="pt5 tr-right bold fs12">
				{{
					this.$parent.report.foreign_investment ?
					'Yes' :
					'No'
				}}
			</td>
		</tr>

		<tr v-if="this.$parent.progress.company_name">
			<td class="pt5 tr-left fs12">
				Is company exempt from reporting?
			</td>
			<td class="pt5 tr-right bold fs12">
				{{
					this.$parent.report.exempt ?
					'Yes' :
					'No'
				}}
			</td>
		</tr>
	</table>

	<p class="mt40 fs12 italic">
		Section 3
	</p>

	<table>
		<tr>
			<td class="tr-left bold">
				Business DBAs
			</td>
			<td 
				v-if="this.$parent.progress.dbas" 
				class="tr-right text-darkgreen bold"
			>
				Complete
			</td>
			<td 
				v-else 
				class="tr-right text-red"
			>
				Incomplete
			</td>
		</tr>

		<tr 
			v-if="this.$parent.report.pii_data.has_dbas === true"
			class="fs12"
			v-for="(dba, key, index) in this.$parent.report.pii_data.dbas"
		>
			<td class="tr-left pt5 fs12">
				&ensp;
			</td>
			<td class="tr-right pt5 fs12">
				{{ dba }}
			</td>
		</tr>

		<tr
			v-if="this.$parent.report.pii_data.has_dbas === false"
			class="fs12"
		>
			<td class="tr-left pt5 fs12">
				None Listed
			</td>
			<td class="tr-right pt5 fs12">
				&ensp;
			</td>
		</tr>

		<tr
			v-if="this.$parent.report.pii_data.has_dbas === null"
			class="fs12"
		>
			<td class="tr-left pt5 fs12">
				&ensp;
			</td>
			<td class="tr-right pt5 fs12">
				<label 
					class="fs12 underline pointer text-blue"
					@click="this.$root.routeTo('/c/report/'+this.$parent.report_guid+'/dbas')"
				>
					Return to section and finish
				</label>
			</td>
		</tr>
	</table>

	<p class="mt40 fs12 italic">
		Section 4
	</p>

	<table>
		<tr>
			<td class="tr-left bold">
				Business Tax Number
			</td>
			<td 
				v-if="this.$parent.progress.tax_number" 
				class="tr-right text-darkgreen bold"
			>
				Complete
			</td>
			<td 
				v-else 
				class="tr-right text-red"
			>
				Incomplete
			</td>
		</tr>

		<tr>
			<td class="pt15 tr-left fs12">
				Tax Number:
			</td>
			<td 
				v-if="this.$parent.progress.tax_number"
				class="pt15 tr-right bold fs12"
			>
				{{ this.$parent.report.pii_data.tax_number }}
			</td>
			<td 
				v-else
				class="tr-right pt5 fs12"
			>
				<label 
					class="fs12 underline pointer text-blue"
					@click="this.$root.routeTo('/c/report/'+this.$parent.report_guid+'/tax-number')"
				>
					Return to section and finish
				</label>
			</td>
		</tr>

		<tr v-if="this.$parent.progress.tax_number">
			<td class="tr-left fs12 pt5">
				Tax Number Type:
			</td>
			<td class="tr-right bold fs12 pt5">
				{{ this.$parent.report.pii_data.tax_number_type.toUpperCase() }}
			</td>
		</tr>

		<tr v-if="$parent.report.pii_data.tax_number_type == 'foreign'">
			<td class="tr-left fs pt5">
				Tax Number Foreign Country
			</td>
			<td class="tr-right bold fs12 pt5">
				{{ $parent.report.pii_data.foreign_tax_number_country }}
			</td>
		</tr>
	</table>

	<p class="mt40 fs12 italic">
		Section 5
	</p>

	<table>
		<tr>
			<td class="tr-left bold">
				Formation Location
			</td>
			<td 
				v-if="this.$parent.progress.formation_location" 
				class="tr-right text-darkgreen bold"
			>
				Complete
			</td>
			<td 
				v-else 
				class="tr-right text-red"
			>
				Incomplete
			</td>
		</tr>

		<tr>
			<td class="pt15 tr-left fs12">
				Formation Location:
			</td>
			<td 
				v-if="this.$parent.progress.formation_location"
				class="pt15 tr-right bold fs12"
			>
				{{ this.$parent.report.pii_data.state_of_formation }}
			</td>
			<td 
				v-else
				class="tr-right pt5 fs12"
			>
				<label 
					class="fs12 underline pointer text-blue"
					@click="this.$root.routeTo('/c/report/'+this.$parent.report_guid+'/formation-location')"
				>
					Return to section and finish
				</label>
			</td>
		</tr>

		<tr v-if="this.$parent.progress.formation_location">
			<td class="pt5 tr-left fs12">
				Formation Date:
			</td>
			<td class="pt5 tr-right bold fs12">
				{{ this.$parent.report.pii_data.formation_date }}
			</td>
		</tr>

		<tr v-if="this.$parent.progress.formation_location">
			<td class="pt5 tr-left fs12">
				Origination:
			</td>
			<td class="pt5 tr-right bold fs12">
				{{ this.$parent.report.pii_data.company_origination_type }}
			</td>
		</tr>
	</table>

	<p class="mt40 fs12 italic">
		Section 6
	</p>

	<table>
		<tr>
			<td class="tr-left bold">
				Business Address
			</td>
			<td 
				v-if="this.$parent.progress.address" 
				class="tr-right text-darkgreen bold"
			>
				Complete
			</td>
			<td 
				v-else 
				class="tr-right text-red"
			>
				Incomplete
			</td>
		</tr>

		<tr>
			<td class="pt15 tr-left fs12">
				Primary Address:
			</td>
			<td 
				v-if="this.$parent.progress.address"
				class="pt15 tr-right bold fs12"
			>
				{{ this.$parent.report.pii_data.us_office_address_1 }}
			</td>
			<td 
				v-else
				class="tr-right pt5 fs12"
			>
				<label 
					class="fs12 underline pointer text-blue"
					@click="this.$root.routeTo('/c/report/'+this.$parent.report_guid+'/address')"
				>
					Return to section and finish
				</label>
			</td>
		</tr>

		<tr v-if="this.$parent.report.pii_data.us_office_address_2">
			<td class="tr-left pt5 fs12">
				&ensp;
			</td>
			<td class="tr-right pt5 fs12 bold">
				{{ this.$parent.report.pii_data.us_office_address_2 }}
			</td>
		</tr>

		<tr v-if="this.$parent.report.pii_data.us_office_city">
			<td class="tr-left pt5 fs12">
				&ensp;
			</td>
			<td class="tr-right pt5 fs12 bold">
				{{ this.$parent.report.pii_data.us_office_city }}, 
				{{ this.$parent.report.pii_data.us_office_state }} 
				{{ this.$parent.report.pii_data.us_office_zip }}
			</td>
		</tr>
	</table>

	<p class="mt40 fs12 italic">
		Section 7
	</p>

	<table>
		<tr>
			<td class="tr-left bold">
				Applicants
			</td>
			<td 
				v-if="this.$parent.progress.applicants" 
				class="tr-right text-darkgreen bold"
			>
				Complete
			</td>
			<td 
				v-else 
				class="tr-right text-red"
			>
				Incomplete
			</td>
		</tr>
	</table>

	<table v-if="this.$parent.applicants_clone.length == 0">
		<tr>
			<td class="pt5 tr-left bold fs12">
				No applicants
			</td>
		</tr>
	</table>
	<table 
		v-else
		v-for="(applicant, index) in this.$parent.applicants_clone"
	>
		<tr>
			<td class="pt15 tr-left bold fs12">
				Applicant #{{ index + 1 }}
			</td>
		</tr>

		<tr v-if="applicant.has_fincen_id">
			<td class="pt5 tr-left fs12">
				FinCEN ID:
			</td>
			<td class="pt5 tr-right bold fs12">
				{{ applicant.fincen_id }} 
			</td>
		</tr>

		<tr v-if="!applicant.has_fincen_id">
			<td class="pt5 tr-left fs12">
				Full Name:
			</td>
			<td class="pt5 tr-right bold fs12">
				{{ applicant.first_name }} 
				{{ applicant.middle_name }} 
				{{ applicant.last_name }}
			</td>
		</tr>

		<tr v-if="!applicant.has_fincen_id">
			<td class="pt5 tr-left fs12">
				Country of Residence:
			</td>
			<td class="pt5 tr-right bold fs12">
				{{ applicant.country }}
			</td>
		</tr>

		<tr v-if="!applicant.has_fincen_id">
			<td class="pt5 tr-left fs12">
				Primary Address:
			</td>
			<td class="pt5 tr-right bold fs12">
				{{ applicant.us_address_1 }} 
			</td>
		</tr>

		<tr 
			v-if="
				applicant.us_address_2 &&
				!applicant.has_fincen_id
			"
		>
			<td class="tr-left fs12">
				&ensp;
			</td>
			<td class="tr-right bold fs12">
				{{ applicant.us_address_2 }} 
			</td>
		</tr>

		<tr 
			v-if="
				applicant.us_city &&
				!applicant.has_fincen_id
			"
		>
			<td class="tr-left fs12">
				&ensp;
			</td>
			<td class="tr-right bold fs12">
				{{ applicant.us_city }}, 
				{{ applicant.us_state }} 
				{{ applicant.us_zip }}
			</td>
		</tr>
	</table>

	<p class="mt40 fs12 italic">
		Section 8
	</p>

	<table>
		<tr>
			<td class="tr-left bold">
				Beneficial Owners
			</td>
			<td 
				v-if="this.$parent.progress.beneficial_owners" 
				class="tr-right text-darkgreen bold"
			>
				Complete
			</td>
			<td 
				v-else 
				class="tr-right text-red"
			>
				Incomplete
			</td>
		</tr>
	</table>

	<table
		v-if="!this.$parent.progress.beneficial_owners"
	>
		<tr>
			<td class="pt15 tr-left bold fs12">
				Beneficial Owner #1
			</td>
			<td class="pt5 tr-right bold fs12">
				<label 
					class="fs12 underline pointer text-blue"
					@click="this.$root.routeTo('/c/report/'+this.$parent.report_guid+'/beneficial-owners')"
				>
					Return to section and finish
				</label>
			</td>
		</tr>
	</table>
	<table
		v-else
		v-for="(owner, index) in this.$parent.beneficial_owners_clone"
	>
		<tr>
			<td class="pt15 tr-left bold fs12">
				Beneficial Owner #{{ index + 1 }}
			</td>
		</tr>

		<tr v-if="owner.has_fincen_id">
			<td class="pt5 tr-left fs12">
				FinCEN ID:
			</td>
			<td class="pt5 tr-right bold fs12">
				{{ owner.fincen_id }} 
			</td>
		</tr>

		<tr 
			v-if="
				owner.is_exempt_entity &&
				!owner.has_fincen_id
			"
		>
			<td class="pt5 tr-left fs12">
				Exempt Entity Name:
			</td>
			<td class="pt5 tr-right bold fs12">
				{{ owner.exempt_entity_name }} 
			</td>
		</tr>

		<tr 
			v-if="
				!owner.is_exempt_entity &&
				!owner.has_fincen_id
			"
		>
			<td class="pt5 tr-left fs12">
				Full Name:
			</td>
			<td class="pt5 tr-right bold fs12">
				{{ owner.first_name }} 
				{{ owner.middle_name }} 
				{{ owner.last_name }}
			</td>
		</tr>

		<tr v-if="!owner.has_fincen_id">
			<td class="pt5 tr-left fs12">
				Country of Residence:
			</td>
			<td class="pt5 tr-right bold fs12">
				{{ owner.country }}
			</td>
		</tr>

		<tr v-if="!owner.has_fincen_id">
			<td class="pt5 tr-left fs12">
				Primary Address:
			</td>
			<td class="pt5 tr-right bold fs12">
				{{ owner.us_address_1 }} 
			</td>
		</tr>

		<tr 
			v-if="
				owner.us_address_2 &&
				!owner.has_fincen_id
			"
		>
			<td class="tr-left fs12">
				&ensp;
			</td>
			<td class="tr-right bold fs12">
				{{ owner.us_address_2 }} 
			</td>
		</tr>

		<tr 
			v-if="
				owner.us_address_2 &&
				!owner.has_fincen_id
			"
		>
			<td class="tr-left fs12">
				&ensp;
			</td>
			<td class="tr-right bold fs12">
				{{ owner.us_city }}, 
				{{ owner.us_state }} 
				{{ owner.us_zip }}
			</td>
		</tr>
	</table>

	<p class="mt40 fs12 italic">
		Section 9
	</p>

	<table
		v-if="
			!this.$parent.progress.identification_docs1 ||
			!this.$parent.progress.identification_docs2
		"
	>
		<tr>
			<td class="tr-left bold">
				Identification Docs
			</td>
			<td class="tr-right text-red">
				Incomplete
			</td>
		</tr>

		<tr>
			<td class="pt15 tr-left bold fs12">
				Beneficial Owner #1
			</td>
			<td class="tr-right pt5 fs12">
				<label 
					class="fs12 underline pointer text-blue"
					@click="this.$root.routeTo('/c/report/'+this.$parent.report_guid+'/identification-docs')"
				>
					Return to section and finish
				</label>
			</td>
		</tr>
	</table>
	<table v-else>
		<tr>
			<td class="tr-left bold">
				Identification Docs
			</td>
			<td class="tr-right text-darkgreen bold">
				Complete
			</td>
		</tr>

		<tr v-for="(owner, index) in this.$parent.beneficial_owners_clone">
			<td class="pt15 tr-left bold fs12">
				Beneficial Owner #{{ index + 1 }}
			</td>

			<td 
				v-if="
					owner.is_exempt_entity ||
					owner.has_fincen_id
				"
				class="tr-right pt5 fs12"
			>
				Exempt
			</td>
			<td 
				v-else
				class="tr-right pt5 fs12"
			>
				Document Uploaded&ensp;
				<span class="pointer underline text-blue" @click="openLink(owner.identifying_document.file_url)">
					View
				</span>
			</td>
		</tr>

		<tr v-for="(applicant, index) in this.$parent.applicants_clone">
			<td class="pt15 tr-left bold fs12">
				Applicant #{{ index + 1 }}
			</td>

			<td 
				v-if="applicant.has_fincen_id"
				class="tr-right pt5 fs12"
			>
				Exempt
			</td>
			<td 
				v-else
				class="tr-right pt5 fs12"
			>
				Document Uploaded&ensp;
				<span class="pointer underline text-blue" @click="openLink(applicant.identifying_document.file_url)">
					View
				</span>
			</td>
		</tr>
	</table>

	<div v-if="this.$parent.progress.review">
		<p class="mt40 bold fs18 text-darkgreen">
			Report submitted
		</p>

		<p class="mt20 mb100 bold fs18">
			Awaiting firm review
		</p>
	</div>

	<div v-else>
		<p
			v-if="
				!this.$parent.progress.intro                ||
				!this.$parent.progress.company_name         ||
				!this.$parent.progress.dbas                 ||
				!this.$parent.progress.tax_number           ||
				!this.$parent.progress.formation_location   ||
				!this.$parent.progress.address              ||
				!this.$parent.progress.applicants           ||
				!this.$parent.progress.beneficial_owners    ||
				!this.$parent.progress.identification_docs1 ||
				!this.$parent.progress.identification_docs2
			" 
			class="mt40 text-red"
		>
			This report is NOT ready to submit. Please provide information in sections that display "INCOMPLETE" above by clicking the blue "Return to section and finish" link in those sections.
		</p>

		<table class="mt20">
			<tr>
				<td class="tr-check">
					<input type="checkbox" class="pointer" id="affirmation" v-model="this.$parent.affirmation">
				</td>
				<td>
					<label for="affirmation" class="pointer fs16">
						<span>
							I confirm that all information above is complete and correct, that no beneficial owners have been omitted from this report, and I acknowledge that my company, as the reporting company, is responsible for errors caused by incorrect or incomplete information.
						</span>
					</label>
				</td>
			</tr>
		</table>

		<div class="mt40 mb100 width-300">
			<ClipLoader 
				v-if="this.$parent.loading" 
				size="25px" 
				:color="this.$root.color_primary"
				class="pt10"
			></ClipLoader>

			<div v-else>
				<button
					class="mt30 mb100 btn width-200"
					:class="
						(
							this.$parent.affirmation                   &&
							this.$parent.progress.intro                &&
							this.$parent.progress.company_name         &&
							this.$parent.progress.dbas                 &&
							this.$parent.progress.tax_number           &&
							this.$parent.progress.formation_location   &&
							this.$parent.progress.address              &&
							this.$parent.progress.applicants           &&
							this.$parent.progress.beneficial_owners    &&
							this.$parent.progress.identification_docs1 &&
							this.$parent.progress.identification_docs2
						)
						? 'btn-yellow'
						: 'btn-black div-disabled'
					"
					@click="saveReview"
				>
					Submit to Filer
				</button>
			</div>
		</div>
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