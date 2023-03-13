<script>

import { api } from '../../../api.js';
import ClipLoader from 'vue-spinner/src/ClipLoader.vue';
import { Modal } from 'vue-neat-modal';
import DropZone from 'dropzone-vue';

import 'vue-neat-modal/dist/vue-neat-modal.css';

export default {
	data() {
		return {
			show_dropzone: true,
			file_url:      '',
			file_name:     '',
			owner:         '',
			applicant:     '',
			kind:          'owner'
			// enum(applicant, owner)
		}
	},

	components: {
		ClipLoader,
		Modal,
		DropZone
	},

	created() {
	},

	mounted() {
		let that = this;
	},

	watch: {
		'file_url': "saveIdentificationFiles"
	},

	methods: {
		async saveIdentificationDocs() {
			let params = {
				report_guid: this.$parent.report_guid,
				data_point:  'identification_docs',
				owners:      this.$parent.beneficial_owners_clone,
				applicants:  this.$parent.applicants_clone
			}

			this.$parent.saveProgress(params);
		},

		async saveIdentificationFiles() {
			let params = {
				report_guid: this.$parent.report_guid,
				data_point:  'identification_files',
				file_url:    this.file_url,
				file_name:   this.file_name,
				owner:       this.owner,
				applicant:   this.applicant,
				kind:        this.kind,
			}

			this.$parent.saveProgress(params);
		},

		async ownerDocUploaded(event, req) {
			let that = this;

			new Promise((resolve, reject) => {
				let i = 0;

				let f = setInterval(function() {
					if (req.response) {
						let j = JSON.parse(req.response);

						if (j.detail) {
							console.log(j.detail);
							that.file_name = j.detail.file_name;
							that.owner     = j.detail.owner;
							that.kind      = 'owner';
							that.file_url  = j.detail.file_url;
							clearInterval(f);
							resolve(j.detail);
						}
					}

					i += 1;

					if (i >= 50) {
						clearInterval(f);
						reject('');
					}
				}, 100);
			});
		},

		async applicantDocUploaded(event, req) {
			let that = this;

			new Promise((resolve, reject) => {
				let i = 0;

				let f = setInterval(function() {
					if (req.response) {
						let j = JSON.parse(req.response);

						if (j.detail) {
							console.log(j.detail);
							that.file_name = j.detail.file_name;
							that.owner     = j.detail.owner;
							that.kind      = 'applicant';
							that.file_url  = j.detail.file_url;
							clearInterval(f);
							resolve(j.detail);
						}
					}

					i += 1;

					if (i >= 50) {
						clearInterval(f);
						reject('');
					}
				}, 100);
			});
		},

		identificationDocUploadError(event) {
			console.log(event);
			this.$root.toast(
				'Oops',
				'There was a problem uploading document at this time',
				'error'
			);
		},

		openLink(link) {
			window.open(link);
		},

		removeIdentifyingDocument(url) {
			console.log(url);
		}
	}
};

</script>

<template>
	<p class="bold fs20">
		Identification Docs
	</p>

	<p class="mt20">
		Please upload a U.S. state-issued driver’s license or I.D. card or a U.S. Passport for each beneficial owner listed in the prior step. U.S. issued documents must be used if they are available. Tribal-issued documents may be used. A foreign passport may be used ONLY if the beneficial owner does not have a U.S. issued document.
	</p>

	<div v-for="(applicant, index) in this.$parent.applicants_clone">
		<div v-if="applicant.has_fincen_id === false">
			<p class="mt20 bold">
				Company Applicant #{{ index + 1 }}
			</p>

			<p class="op8 fs12">
				{{ applicant.first_name }} 
				{{ applicant.last_name }}
			</p>

			<DropZone 
				:url="`${this.$root.api_url}/user/upload-identification-doc?owner=${index}`"
				:uploadOnDrop="true"
				:multipleUpload="false"
				:headers='{"Authorization": "Bearer " + this.$root.bearer_token}'
				:maxFileSize="5000000"
				@sending="applicantDocUploaded"
				@errorUpload="identificationDocUploadError"
				dropzoneClassName="dropzone-wrap"
				:acceptedFiles="['pdf', 'png', 'jpg', 'jpeg', 'doc', 'docx']"
				ondragover="this.style.border='3px dashed var(--color-primary)';"
				ondragleave="this.style.border='3px dotted var(--color-primary)';"
				@errorAdd="this.$root.dropzone_error"
			></DropZone>

			<p v-if="applicant.identifying_document.file_url" class="mt20">
				<span class="pointer" @click="removeIdentifyingDocument(applicant.identifying_document.file_url)">
					<i class="text-red op7 fa fa-times"></i>
				</span>

				<span class="pointer underline ml5" @click="openLink(applicant.identifying_document.file_url)">
					{{ applicant.identifying_document.file_name }}
				</span>
			</p>

			<p class="op8 fs14 mt30">
				Document Type
			</p>
			<select
				class="form-select fincen-input width-300 mt5 pointer" 
				v-model="applicant.identifying_document.type"
			>
				<option
					v-for="(document_type, document_key) in this.$parent.document_types"
					:selected="applicant.identifying_document.type == document_key"
					:value="document_key"
				>
					{{ document_type }}
				</option>
			</select>
		</div>
	</div>

	<hr 
		v-if="
			this.$parent.applicants_requiring_docs > 0 &&
			this.$parent.beneficial_owners_requiring_docs != 0
		"
	>

	<div v-for="(owner, index) in this.$parent.beneficial_owners_clone">
		<div 
			v-if="
				owner.has_fincen_id ||
				owner.is_exempt_entity
			"
		></div>
		<div v-else>
			<p class="mt20 bold">
				Beneficial Owner #{{ index + 1 }}
			</p>

			<p class="op8 fs12">
				{{ owner.first_name }} 
				{{ owner.last_name }}
			</p>

			<DropZone 
				:url="`${this.$root.api_url}/user/upload-identification-doc?owner=${index}`"
				:uploadOnDrop="true"
				:multipleUpload="false"
				:headers='{"Authorization": "Bearer " + this.$root.bearer_token}'
				:maxFileSize="5000000"
				@sending="ownerDocUploaded"
				@errorUpload="identificationDocUploadError"
				dropzoneClassName="dropzone-wrap"
				:acceptedFiles="['pdf', 'png', 'jpg', 'jpeg', 'doc', 'docx']"
				ondragover="this.style.border='3px dashed var(--color-primary)';"
				ondragleave="this.style.border='3px dotted var(--color-primary)';"
				@errorAdd="this.$root.dropzone_error"
			></DropZone>

			<p v-if="owner.identifying_document.file_url" class="mt10">
				<span class="pointer" @click="removeIdentifyingDocument(owner.identifying_document.file_url)">
					<i class="text-red op7 fa fa-times"></i>
				</span>

				<span class="pointer underline ml5" @click="openLink(owner.identifying_document.file_url)">
					{{ owner.identifying_document.file_name }}
				</span>
			</p>

			<p class="op8 fs14 mt30">
				Document Type
			</p>
			<select
				class="form-select fincen-input width-300 mt5 pointer" 
				v-model="owner.identifying_document.type"
			>
				<option
					v-for="(document_type, document_key) in this.$parent.document_types"
					:selected="owner.identifying_document.type == document_key"
					:value="document_key"
				>
					{{ document_type }}
				</option>
			</select>

			<div v-if="owner.identifying_document.type == 'foreign_passport'">
				<p class="op8 fs14 mt20">
					Country Issued
				</p>
				<select
					class="form-select fincen-input width-300 mt5 pointer" 
					v-model="owner.identifying_document.foreign_passport_issuer"
				>
					<option
						v-for="country in this.$parent.countries"
						:selected="owner.identifying_document.foreign_passport_issuer == country"
					>
						{{ country }}
					</option>
				</select>
			</div>

			<div v-if="owner.identifying_document.type == 'drivers_license'">
				<p class="op8 fs14 mt20">
					State Issued
				</p>
				<select
					class="form-select fincen-input width-300 mt5 pointer" 
					v-model="owner.identifying_document.drivers_license_state"
				>
					<option 
						v-for="state in this.$parent.us_states"
						:selected="owner.identifying_document.drivers_license_state == state"
					>
						{{ state }}
					</option>
				</select>
			</div>

			<div 
				v-if="owner.identifying_document.type == 'state_or_tribe_id'"
			>
				<div v-if="owner.identifying_document.id_card_state_or_tribe == 'state'">
					<p class="op8 fs14 mt20">
						State Issued
					</p>
					<select
						class="form-select fincen-input width-300 mt5 pointer" 
						v-model="owner.identifying_document.id_card_state"
					>
						<option 
							v-for="state in this.$parent.us_states"
							:selected="owner.identifying_document.id_card_state == state"
						>
							{{ state }}
						</option>
					</select>

					<p 
						class="mt5 fs12 underline text-blue pointer"
						@click="owner.identifying_document.id_card_state_or_tribe = 'tribe'"
					>
						Enter a Tribe instead
					</p>
				</div>

				<div v-else>
					<p class="op8 fs14 mt20">
						Tribe Issued
					</p>
					<input
						type="text"
						class="form-control fincen-input width-300 mt5" 
						v-model="owner.identifying_document.id_card_tribe"
					>

					<p 
						class="mt5 fs12 underline text-blue pointer"
						@click="owner.identifying_document.id_card_state_or_tribe = 'state'"
					>
						Enter a State instead
					</p>
				</div>
			</div>

			<p class="op8 fs14 mt25">
				Document Number
			</p>
			<input 
				type="text" 
				class="form-control fincen-input width-300 mt5" 
				v-model="owner.identifying_document.document_number"
			>
		</div>
	</div>

	<!-- no docs for either applicants or exempt owners -->
	<div
		v-if="
			this.$parent.applicants_requiring_docs        == 0 &&
			this.$parent.beneficial_owners_requiring_docs == 0
		"
	>
		<p class="mt40 text-blue bold">
			You are not required to upload any documents at this time.
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
			@click="this.saveIdentificationDocs"
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

.dropzone-wrap {
	border: 3px dotted var(--color-primary); 
	color: var(--color-primary);
	cursor: pointer; 
	padding: 15px;
	margin-top: 10px; 
	overflow: hidden;
}

</style>