<script>

import App from '../../../App.vue';
import { api } from '../../api.js';
import ClipLoader from 'vue-spinner/src/ClipLoader.vue';
import Reports from './Reports/Reports.vue';
import Companies from './Companies/Companies.vue';
import Billing from './Billing/Billing.vue';
import Settings from './Settings/Settings.vue';
import ActivityLog from './ActivityLog/ActivityLog.vue';
import Team from './Team/Team.vue';

export default {
	data() {
		return {
			loading:       false,
			firm_guid:     this.$route.params.firm_guid,
			uri_category:  this.$route.params.category,
			selected_firm: {
				email:           null,
				status:          null,
				associated_at:   null,
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
		Companies,
		Billing,
		Settings,
		ActivityLog,
		Team
	},

	created() {
		this.loadFirm();
	},

	mounted() {
		let that = this;

		if (
			!this.firm_guid ||
			this.firm_guid == ''
		) {
			this.$root.routeTo(`/a/firm/`);
			return false;
		}

		if (
			this.uri_category != 'reports' &&
			this.uri_category != 'companies' &&
			this.uri_category != 'billing' &&
			this.uri_category != 'team' &&
			this.uri_category != 'activity-log' &&
			this.uri_category != 'settings'
		) {
			this.uri_category = 'reports';
			this.$root.routeTo(`/a/firm/${this.firm_guid}/reports`);
		}
	},

	watch: {
		'$route' (to, from) {
			this.firm_guid    = this.$route.params.firm_guid;
			this.uri_category = this.$route.params.category;
		},
	},

	methods: {
		async loadFirm() {
			let fetch_bearer_token = this.$cookies.get('bearer_token');

			const response = await api(
				'GET',
				'admin/get-firm',
				{
					firm_guid: this.firm_guid
				},
				fetch_bearer_token
			);

			this.$root.catch401(response);

			if (response.status == 200) {
				console.log(response);
				this.selected_firm = response.detail;
			} else {
				this.$root.routeTo('/a/firms');
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
				@click="this.$root.routeTo('/a/firm/'+firm_guid+'/reports')"
			>
				Reports
			</div>
			<div 
				class="sub-view-menu-item"
				:class="uri_category == 'companies' ? 'sub-view-menu-item-active' : ''"
				@click="this.$root.routeTo('/a/firm/'+firm_guid+'/companies')"
			>
				Companies
			</div>
			<div 
				class="sub-view-menu-item"
				:class="uri_category == 'billing' ? 'sub-view-menu-item-active' : ''"
				@click="this.$root.routeTo('/a/firm/'+firm_guid+'/billing')"
			>
				Billing & Plan
			</div>
			<div 
				class="sub-view-menu-item"
				:class="uri_category == 'team' ? 'sub-view-menu-item-active' : ''"
				@click="this.$root.routeTo('/a/firm/'+firm_guid+'/team')"
			>
				Manage Team
			</div>
			<div 
				class="sub-view-menu-item"
				:class="uri_category == 'activity-log' ? 'sub-view-menu-item-active' : ''"
				@click="this.$root.routeTo('/a/firm/'+firm_guid+'/activity-log')"
			>
				Activity Log
			</div>
			<div 
				class="sub-view-menu-item"
				:class="uri_category == 'settings' ? 'sub-view-menu-item-active' : ''"
				@click="this.$root.routeTo('/a/firm/'+firm_guid+'/settings')"
			>
				Info & Settings
			</div>
		</div>
		<div class="sub-view-right">
			<div class="sub-view-header">
				<p class="bold float-right fs14">
					Account Created:&ensp;
					<ClipLoader 
						v-if="selected_firm.associated_at === null" 
						size="12px" 
						:color="this.$root.color_primary"
						class="inline"
					></ClipLoader>
					<span v-else class="text-blue">
						{{ this.$root.dateTimeToDate(selected_firm.associated_at) }}
					</span>
				</p>

				<ClipLoader 
					v-if="selected_firm.pii_data.name === null" 
					size="30px" 
					:color="this.$root.color_primary"
					class="inline"
				></ClipLoader>
				<p v-else class="bold fs20">
					{{ selected_firm.pii_data.name }}
					<span class="bold fs14">
						- Firm ID: 
						<span class="text-blue">
							{{ firm_guid }}
						</span>
					</span>
				</p>

				<p class="bold mt20">
					Primary Email:&ensp;
					<ClipLoader 
						v-if="selected_firm.email === null" 
						size="15px" 
						:color="this.$root.color_primary"
						class="inline"
					></ClipLoader>
					<span v-else class="text-blue">
						{{ selected_firm.email }}
					</span>
				</p>

				<p class="bold mt5">
					Phone Number:&ensp;
					<ClipLoader 
						v-if="selected_firm.pii_data.phone === null" 
						size="15px" 
						:color="this.$root.color_primary"
						class="inline"
					></ClipLoader>
					<span v-else class="text-blue">
						{{ selected_firm.pii_data.phone }}
					</span>
				</p>

				<p class="bold mt5">
					Total Companies:&ensp;
					<ClipLoader 
						v-if="selected_firm.total_companies === null" 
						size="15px" 
						:color="this.$root.color_primary"
						class="inline"
					></ClipLoader>
					<span v-else class="text-blue">
						{{ selected_firm.companies.length }}
					</span>
				</p>

				<p class="bold mt5">
					Status:&ensp;
					<ClipLoader 
						v-if="selected_firm.status === null" 
						size="15px" 
						:color="this.$root.color_primary"
						class="inline"
					></ClipLoader>
					<span v-else class="text-blue">
						{{ this.$root.ucfirst(selected_firm.status) }}
					</span>
				</p>
			</div>

			<Reports v-if="uri_category == 'reports'"></Reports>
			<Companies v-if="uri_category == 'companies'"></Companies>
			<Billing v-if="uri_category == 'billing'"></Billing>
			<Team v-if="uri_category == 'team'"></Team>
			<ActivityLog v-if="uri_category == 'activity-log'"></ActivityLog>
			<Settings v-if="uri_category == 'settings'"></Settings>
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