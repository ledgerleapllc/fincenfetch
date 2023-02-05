<script>

import App from '../../../App.vue';
import { api } from '../../api.js';
import ClipLoader from 'vue-spinner/src/ClipLoader.vue';
import Reports from './Reports/Reports.vue';
import LinksSent from './LinksSent/LinksSent.vue';
import AccessLog from './AccessLog/AccessLog.vue';
import HelpfulTools from './HelpfulTools/HelpfulTools.vue';

export default {
	data() {
		return {
			loading:       false,
			company_guid:  this.$route.params.company_guid,
			uri_category:  this.$route.params.category,
			selected_company: {
				email:           null,
				status:          null,
				created_at:      null,
				companies:       [],
				pii_data: {
					name:        null,
					phone:       null
				}
			}
		}
	},

	components: {
		ClipLoader,
		Reports,
		LinksSent,
		AccessLog,
		HelpfulTools
	},

	created() {
		this.loadCompany();
	},

	mounted() {
		let that = this;

		if (
			!this.company_guid ||
			this.company_guid == ''
		) {
			this.$root.routeTo(`/f/companies/`);
			return false;
		}

		if (
			this.uri_category != 'reports' &&
			this.uri_category != 'links-sent' &&
			this.uri_category != 'access-log' &&
			this.uri_category != 'helpful-tools'
		) {
			this.uri_category = 'reports';
			this.$root.routeTo(`/f/company/${this.company_guid}/reports`);
		}
	},

	watch: {
		'$route' (to, from) {
			this.company_guid = this.$route.params.company_guid;
			this.uri_category = this.$route.params.category;
		},
	},

	methods: {
		async loadCompany() {
			let fetch_bearer_token = this.$cookies.get('bearer_token');

			const response = await api(
				'GET',
				'user/get-company',
				{
					company_guid: this.company_guid
				},
				fetch_bearer_token
			);

			this.$root.catch401(response);

			if (response.status == 200) {
				console.log(response);
				this.selected_company = response.detail;
			} else {
				// this.$root.routeTo('/f/companies');
			}
		}
	}
};

</script>

<template>
	<div class="sub-view mt10">
		<div class="sub-view-left">
			<div 
				class="sub-view-menu-item"
				:class="uri_category == 'reports' ? 'sub-view-menu-item-active' : ''"
				@click="this.$root.routeTo('/f/company/'+company_guid+'/reports')"
			>
				Reports
			</div>
			<div 
				class="sub-view-menu-item"
				:class="uri_category == 'links-sent' ? 'sub-view-menu-item-active' : ''"
				@click="this.$root.routeTo('/f/company/'+company_guid+'/links-sent')"
			>
				Links Sent
			</div>
			<div 
				class="sub-view-menu-item"
				:class="uri_category == 'access-log' ? 'sub-view-menu-item-active' : ''"
				@click="this.$root.routeTo('/f/company/'+company_guid+'/access-log')"
			>
				Access Log
			</div>
			<div 
				class="sub-view-menu-item"
				:class="uri_category == 'helpful-tools' ? 'sub-view-menu-item-active' : ''"
				@click="this.$root.routeTo('/f/company/'+company_guid+'/helpful-tools')"
			>
				Helpful Tools
			</div>
		</div>
		<div class="sub-view-right">
			<div class="sub-view-header">
				<p class="bold float-right fs14">
					Account Created:&ensp;
					<ClipLoader 
						v-if="selected_company.created_at === null" 
						size="12px" 
						:color="this.$root.color_primary"
						class="inline"
					></ClipLoader>
					<span v-else class="text-blue">
						{{ this.$root.dateTimeToDate(selected_company.created_at) }}
					</span>
				</p>

				<ClipLoader 
					v-if="selected_company.pii_data.name === null" 
					size="30px" 
					:color="this.$root.color_primary"
					class="inline"
				></ClipLoader>
				<p v-else class="bold fs20">
					{{ selected_company.pii_data.name }}
					<span class="bold fs14">
						- User ID: 
						<span class="text-blue">
							{{ company_guid }}
						</span>
					</span>
				</p>

				<p class="bold mt20">
					Primary Email:&ensp;
					<ClipLoader 
						v-if="selected_company.email === null" 
						size="15px" 
						:color="this.$root.color_primary"
						class="inline"
					></ClipLoader>
					<span v-else class="text-blue">
						{{ selected_company.email }}
					</span>
				</p>

				<p class="bold mt5">
					Phone Number:&ensp;
					<ClipLoader 
						v-if="selected_company.pii_data.phone === null" 
						size="15px" 
						:color="this.$root.color_primary"
						class="inline"
					></ClipLoader>
					<span v-else class="text-blue">
						{{ selected_company.pii_data.phone }}
					</span>
				</p>

				<p class="bold mt5">
					Total Companies:&ensp;
					<ClipLoader 
						v-if="selected_company.total_companies === null" 
						size="15px" 
						:color="this.$root.color_primary"
						class="inline"
					></ClipLoader>
					<span v-else class="text-blue">
						{{ selected_company.companies.length }}
					</span>
				</p>

				<p class="bold mt5">
					Status:&ensp;
					<ClipLoader 
						v-if="selected_company.status === null" 
						size="15px" 
						:color="this.$root.color_primary"
						class="inline"
					></ClipLoader>
					<span v-else class="text-blue">
						{{ this.$root.ucfirst(selected_company.status) }}
					</span>
				</p>
			</div>

			<Reports v-if="uri_category == 'reports'"></Reports>
			<LinksSent v-if="uri_category == 'links-sent'"></LinksSent>
			<AccessLog v-if="uri_category == 'access-log'"></AccessLog>
			<HelpfulTools v-if="uri_category == 'helpful-tools'"></HelpfulTools>
		</div>
	</div>
</template>

<style scoped>

.sub-view {
	display: flex;
	flex-direction: row;
	width: 100%;
	position: relative;
}

.sub-view-left {
	width: 250px;
	min-height: calc(100vh - 170px);
	height: 100%;
	border-right: 1px solid #e0e0e0;
	padding: 20px;
}

.sub-view-right {
	width: calc(100% - 250px);
	height: 100%;
	position: relative;
}

.sub-view-header {
	width: 100%;
	padding: 25px;
	background-color: #fafafa;
	border-bottom: 1px solid #eee;
}

.sub-view-menu-item {
	display: flex;
	align-items: center;
	width: 100%;
	height: 40px;
	padding-left: 15px;
	padding-right: 15px;
	font-weight: bold;
	border-left: 4px solid transparent;
	cursor: pointer;
	transition: 0.3s;
}

.sub-view-menu-item:hover,
.sub-view-menu-item:active {
	background-color: var(--color-primary-transparent);
	color: var(--color-primary);
	transition: 0.1s;
}

.sub-view-menu-item-active {
	border-left: 4px solid var(--color-primary);
	background-color: var(--color-primary-transparent);
	color: var(--color-primary);
}

</style>