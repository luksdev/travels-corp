import {createRouter, createWebHistory} from 'vue-router'
import {useAuthStore} from '../stores/auth'
import LoginView from '../views/LoginView.vue'
import RegisterView from '../views/RegisterView.vue'
import DashboardView from '../views/DashboardView.vue'
import TravelRequestDetailView from '../views/TravelRequestDetailView.vue'

const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '/',
            redirect: '/dashboard'
        },
        {
            path: '/login',
            name: 'Login',
            component: LoginView,
            meta: {requiresGuest: true}
        },
        {
            path: '/register',
            name: 'Register',
            component: RegisterView,
            meta: {requiresGuest: true}
        },
        {
            path: '/dashboard',
            name: 'Dashboard',
            component: DashboardView,
            meta: {requiresAuth: true}
        },
        {
            path: '/trips/:id',
            name: 'TripDetail',
            component: TravelRequestDetailView,
            meta: {requiresAuth: true}
        },
    ]
})

router.beforeEach((to, _from, next) => {
    const authStore = useAuthStore()

    // Verify authentication from stored tokens
    const isAuthenticated = authStore.checkAuth()

    if (to.meta.requiresAuth && !isAuthenticated) {
        next('/login')
    } else if (to.meta.requiresGuest && isAuthenticated) {
        next('/dashboard')
    } else {
        next()
    }
})

export default router