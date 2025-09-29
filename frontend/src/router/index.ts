import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      redirect: '/dashboard'
    },
    {
      path: '/login',
      name: 'login',
      component: () => import('@/views/LoginView.vue'),
      meta: { requiresGuest: true }
    },
    {
      path: '/dashboard',
      name: 'dashboard',
      component: () => import('@/views/DashboardView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/products',
      name: 'products',
      component: () => import('@/views/ProductsView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/customers',
      name: 'customers',
      component: () => import('@/views/CustomersView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/orders',
      name: 'orders',
      component: () => import('@/views/OrdersView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/orders/:id',
      name: 'order-details',
      component: () => import('@/views/OrderDetailsView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/sales',
      name: 'sales',
      component: () => import('@/views/SalesView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/receivables',
      name: 'receivables',
      component: () => import('@/views/ReceivablesView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/settings',
      name: 'settings',
      component: () => import('@/views/SettingsView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/users',
      name: 'users',
      component: () => import('@/views/UsersView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/roles',
      name: 'roles',
      component: () => import('@/views/RolesView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/permissions',
      name: 'permissions',
      component: () => import('@/views/PermissionsView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/vasilhames',
      name: 'vasilhames',
      component: () => import('@/views/VasilhamesView.vue'),
      meta: { requiresAuth: true }
    },
    // Service Orders Routes
    {
      path: '/service-orders',
      name: 'service-orders',
      component: () => import('@/views/ServiceOrdersView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/service-orders/new',
      name: 'service-order-new',
      component: () => import('@/views/ServiceOrderFormView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/service-orders/:id',
      name: 'service-order-details',
      component: () => import('@/views/ServiceOrderDetailsView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/service-orders/:id/edit',
      name: 'service-order-edit',
      component: () => import('@/views/ServiceOrderFormView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/service-orders/payments/reports',
      name: 'service-order-payments-reports',
      component: () => import('@/views/ServiceOrderPaymentsReportView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/service-orders/:id/print',
      name: 'service-order-print',
      component: () => import('@/views/ServiceOrderPrintView.vue'),
      meta: { requiresAuth: true }
    },
    // Services Routes
    {
      path: '/services',
      name: 'services',
      component: () => import('@/views/ServicesView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/services/new',
      name: 'service-new',
      component: () => import('@/views/ServiceFormView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/services/:id/edit',
      name: 'service-edit',
      component: () => import('@/views/ServiceFormView.vue'),
      meta: { requiresAuth: true }
    },
    // Vehicles Routes
    {
      path: '/vehicles',
      name: 'vehicles',
      component: () => import('@/views/VehiclesView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/vehicles/new',
      name: 'vehicle-new',
      component: () => import('@/views/VehicleFormView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/vehicles/:id',
      name: 'vehicle-details',
      component: () => import('@/views/VehicleDetailsView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/vehicles/:id/edit',
      name: 'vehicle-edit',
      component: () => import('@/views/VehicleFormView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/:pathMatch(.*)*',
      redirect: '/dashboard'
    }
  ]
})

// Navigation guards
router.beforeEach((to, _from, next) => {
  const authStore = useAuthStore()
  const isAuthenticated = authStore.isLoggedIn

  // Se a rota requer autenticação e o usuário não está logado
  if (to.meta.requiresAuth && !isAuthenticated) {
    next('/login')
    return
  }

  // Se a rota é para convidados (login) e o usuário está logado
  if (to.meta.requiresGuest && isAuthenticated) {
    next('/dashboard')
    return
  }

  next()
})

export default router
