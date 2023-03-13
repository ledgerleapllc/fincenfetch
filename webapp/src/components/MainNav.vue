<script>

export default {
	data: () => ({
	})
};

</script>

<template>
	<div
		v-if="
			(
				this.$root.verified &&
				this.$root.password
			)
		"
	>
		<div
			v-if="
				this.$root.role == 'admin' ||
				this.$root.role == 'sub-admin'
			"
			class="main-nav"
		>
			<div class="main-nav-left">
				<img src="@/assets/images/logo.png" class="pointer" @click="this.$root.goHome">

				<div 
					class="main-nav-item"
					:class="
						this.$root.route.startsWith('/a/firm') ?
						'main-nav-item-active' : 
						''
					"
					@click="this.$root.routeTo('/a/firms')"
				>
					Firms
				</div>

				<div 
					class="main-nav-item"
					:class="
						this.$root.route.startsWith('/a/companies') ?
						'main-nav-item-active' : 
						''
					"
					@click="this.$root.routeTo('/a/companies')"
				>
					Companies
				</div>

				<div 
					class="main-nav-item"
					:class="
						this.$root.route.startsWith('/a/reports') ?
						'main-nav-item-active' : 
						''
					"
					@click="this.$root.routeTo('/a/reports')"
				>
					Reports
				</div>

				<div 
					class="main-nav-item"
					:class="
						this.$root.route.startsWith('/a/billing') ?
						'main-nav-item-active' : 
						''
					"
					@click="this.$root.routeTo('/a/billing')"
				>
					Billing
				</div>

				<div 
					class="main-nav-item"
					:class="
						this.$root.route.startsWith('/a/settings') ?
						'main-nav-item-active' : 
						''
					"
					@click="this.$root.routeTo('/a/settings/detail')"
				>
					Settings
				</div>
			</div>

			<div class="main-nav-logout" @click="this.$root.logout()">
				<i class="fa fa-sign-out"></i>
				Log out
			</div>
		</div>




		<div
			v-if="this.$root.role == 'firm'"
			class="main-nav"
		>
			<div class="main-nav-left">
				<img src="@/assets/images/logo.png" class="pointer" @click="this.$root.goHome">

				<div 
					class="main-nav-item"
					:class="
						this.$root.route.startsWith('/f/report') ?
						'main-nav-item-active' : 
						''
					"
					@click="this.$root.routeTo('/f/reports')"
				>
					Reports
				</div>

				<div 
					class="main-nav-item"
					:class="
						this.$root.route.startsWith('/f/compan') ?
						'main-nav-item-active' : 
						''
					"
					@click="this.$root.routeTo('/f/companies')"
				>
					Companies
				</div>

				<div 
					class="main-nav-item"
					:class="
						this.$root.route.startsWith('/f/team') ?
						'main-nav-item-active' : 
						''
					"
					@click="this.$root.routeTo('/f/team')"
				>
					My Team
				</div>

				<div 
					class="main-nav-item"
					:class="
						this.$root.route.startsWith('/f/billing') ?
						'main-nav-item-active' : 
						''
					"
					@click="this.$root.routeTo('/f/billing')"
				>
					Billing
				</div>

				<div 
					class="main-nav-item"
					:class="
						this.$root.route.startsWith('/f/settings') ?
						'main-nav-item-active' : 
						''
					"
					@click="this.$root.routeTo('/f/settings/detail')"
				>
					Settings
				</div>
			</div>

			<div class="main-nav-logout" @click="this.$root.logout()">
				<i class="fa fa-sign-out"></i>
				Log out
			</div>
		</div>




		<div
			v-if="this.$root.role == 'company'"
			class="main-nav"
		>
			<div class="main-nav-left">
				<img src="@/assets/images/logo.png" class="pointer" @click="this.$root.goHome">

				<div 
					class="main-nav-item"
					:class="
						this.$root.route.startsWith('/c/report') ?
						'main-nav-item-active' : 
						''
					"
					@click="this.$root.routeTo('/c/reports')"
				>
					My Reports
				</div>

				<div 
					class="main-nav-item"
					:class="
						this.$root.route.startsWith('/c/settings') ?
						'main-nav-item-active' : 
						''
					"
					@click="this.$root.routeTo('/c/settings/detail')"
				>
					Settings
				</div>
			</div>

			<div class="main-nav-logout" @click="this.$root.logout()">
				<i class="fa fa-sign-out"></i>
				Log out
			</div>
		</div>
	</div>
</template>

<style scoped>

.main-nav {
	height: var(--main-nav-height); 
	width: 100%; 
	padding-left: 25px;
	display: flex; 
	flex-direction: row; 
	align-items: center;
	justify-content: space-between;
	position: relative;
}

.main-nav-left {
	height: 100%;
	width: calc(100% - 130px);
	display: flex; 
	flex-direction: row; 
	align-items: center;
}

.main-nav-left img {
	height: 33px; 
	width: auto; 
	margin-right: 25px;
}

.main-nav-item {
	padding-top: 5px;
	margin-left: 12px;
	margin-right: 12px;
	font-weight: bold;
	height: 100%;
	width: auto;
	display: flex;
	justify-content: center;
	align-items: center;
	border-bottom: 3px solid transparent;
	cursor: pointer;
	transition: .3s ease;
}

.main-nav-item:hover,
.main-nav-item:active {
	border-bottom: 3px solid var(--color-primary);
	transition: .1s ease;
}

.main-nav-item-active {
	border-bottom: 3px solid var(--color-primary);
	color: var(--color-primary);
	transition: .1s ease;
}

.main-nav-logout {
	position: relative;
	height: 100%;
	width: 130px;
	padding-right: 25px;
	font-weight: bold;
	color: var(--color-primary);
	display: flex; 
	flex-direction: row; 
	align-items: center;
	justify-content: flex-end;
	cursor: pointer;
}

</style>