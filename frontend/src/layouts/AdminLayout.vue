<script setup>
    import {
        computed,
        onBeforeUnmount,
        onMounted,
        ref,
        watch
    } from 'vue'
    import {
        useRoute,
        useRouter
    } from 'vue-router'
    import api from '../services/api'
    import {
        clearToken,
        getUserRole,
        getStoredUserData
    } from '../services/auth'
    import { canPerformAction } from '../services/permissions'
    import { MODULES } from '../services/permissionCatalog'

    const router = useRouter()
    const route = useRoute()
    const isSidebarOpen = ref(false)
    const isUserMenuOpen = ref(false)
    const profileName = ref('Administrator')
    const userRole = ref('user')
    const topbarMenuRef = ref(null)

    const allMenus = [{
            routeName: 'dashboard',
            label: 'Dashboard',
            icon: 'fas fa-fw fa-home'
        },
        {
            routeName: 'books.index',
            label: 'Books',
            icon: 'fas fa-fw fa-book',
            module: 'books'
        },
        {
            routeName: 'products.index',
            label: 'Products',
            icon: 'fas fa-fw fa-box-open',
            module: 'products'
        },
        {
            routeName: 'courses.index',
            label: 'Courses',
            icon: 'fas fa-fw fa-graduation-cap',
            module: 'courses'
        },
        {
            routeName: 'reports.index',
            label: 'Reports',
            icon: 'fas fa-fw fa-chart-bar',
            module: 'reports'
        },
        {
            routeName: 'users.index',
            label: 'User Management',
            icon: 'fas fa-fw fa-users',
            module: 'users'
        },
        {
            routeName: 'role-permissions.index',
            label: 'Role Permissions',
            icon: 'fas fa-fw fa-user-shield',
            adminOnly: true
        },
    ]

    const menus = computed(() => {
        return allMenus.filter((menu) => {
            // Dashboard is always visible
            if (!menu.module) {
                return true
            }

            if (menu.adminOnly) {
                return userRole.value === 'admin'
            }

            // Check if user has access to this module
            return canPerformAction(userRole.value, menu.module, 'view')
        })
    })

    function isMenuActive(routeName) {
        const currentName = String(route.name || '')
        const prefix = routeName.split('.')[0]

        return currentName === routeName || currentName.startsWith(`${prefix}.`)
    }

    async function logout() {
        try {
            await api.post('/logout')
        } catch {} finally {
            clearToken()
            await router.push({
                name: 'login'
            })
        }
    }

    function toggleSidebar() {
        isSidebarOpen.value = !isSidebarOpen.value
    }

    function closeSidebar() {
        isSidebarOpen.value = false
    }

    function toggleUserMenu() {
        isUserMenuOpen.value = !isUserMenuOpen.value
    }

    function closeUserMenu() {
        isUserMenuOpen.value = false
    }

    async function goToProfile() {
        closeUserMenu()
        await router.push({
            name: 'user.profile'
        })
    }

    const profileInitials = computed(() => {
        return profileName.value
            .split(' ')
            .filter(Boolean)
            .slice(0, 2)
            .map((word) => word[0]?.toUpperCase())
            .join('')
    })

    function onDocumentClick(event) {
        if (!isUserMenuOpen.value) {
            return
        }

        if (topbarMenuRef.value && !topbarMenuRef.value.contains(event.target)) {
            closeUserMenu()
        }
    }

    onMounted(() => {
        document.addEventListener('click', onDocumentClick)
        
        // Load user data and role
        const userData = getStoredUserData()
        if (userData) {
            profileName.value = userData.name || 'Administrator'
            userRole.value = userData.role || 'user'
        } else {
            userRole.value = getUserRole()
        }
    })

    onBeforeUnmount(() => {
        document.removeEventListener('click', onDocumentClick)
    })

    watch(
        () => route.fullPath,
        () => {
            closeSidebar()
            closeUserMenu()
        },
    )
</script>

<template>
    <div id="wrapper">
        <div v-if="isSidebarOpen" class="sidebar-overlay" @click="closeSidebar"></div>

        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion"
            :class="{ 'mobile-open': isSidebarOpen }" id="accordionSidebar">
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="#">
                   <div class="sidebar-brand-icon rotate-n-15">
                    <i class="fas fa-laugh-wink"></i>
                </div>
                <div class="sidebar-brand-text mx-3">CRUD Admin</div>
            </a>

            <hr class="sidebar-divider my-0" />

            <li class="nav-item" v-for="menu in menus" :key="menu.routeName"
                :class="{ active: isMenuActive(menu.routeName) }">
                <RouterLink :to="{ name: menu.routeName }" class="nav-link" @click="closeSidebar">
                    <i :class="menu.icon"></i>
                    <span>{{ menu.label }}</span>
                </RouterLink>
            </li>
        </ul>

        <div id="content-wrapper" class="d-flex flex-column mt-2">
            <div id="content">
                <nav class="navbar navbar-expand navbar-light bg-primary topbar app-navbar mb-4 static-top">
                    <div class="container-fluid app-navbar-inner">
                        <div class="d-flex align-items-center topbar-left-group">
                            <button class="btn btn-link text-white d-md-none rounded-circle mr-2"
                                @click="toggleSidebar">
                                <i class="fas fa-bars"></i>
                            </button>
                            <div>
                                <h4 class="page-title mb-0 text-white">Dashboard</h4>
                                <small class="text-white-50 d-none d-sm-inline">Panel Administrasi</small>
                            </div>

                        </div>

                        <div class="ml-auto d-flex align-items-center topbar-actions position-relative" ref="topbarMenuRef">
                            <button class="btn navbar-user-toggle" @click="toggleUserMenu">
                                <span class="navbar-user-name mr-2">{{ profileName }}</span>
                                <span class="navbar-avatar">{{ profileInitials }}</span>
                            </button>

                            <div v-if="isUserMenuOpen" class="navbar-user-menu">
                                <button class="dropdown-item py-2" type="button" @click="goToProfile">
                                    <i class="fas fa-user mr-2 text-gray-700"></i>
                                    Profil Saya
                                </button>
                                <div class="dropdown-divider my-0"></div>
                                <button class="dropdown-item py-2 text-danger" type="button" @click="logout">
                                    <i class="fas fa-sign-out-alt mr-2"></i>
                                    Logout
                                </button>
                            </div>
                        </div>
                    </div>
                </nav>

                <div class="container-fluid">
                    <RouterView />
                </div>
            </div>
        </div>
    </div>
</template>
