import { createRouter, createWebHistory } from 'vue-router'
import LoginView from '../views/LoginView.vue'
import RegisterView from '../views/RegisterView.vue'
import AdminLayout from '../layouts/AdminLayout.vue'
import DashboardPage from '../modules/dashboard/pages/DashboardPage.vue'
import BooksIndexPage from '../modules/books/pages/BooksIndexPage.vue'
import BooksCreatePage from '../modules/books/pages/BooksCreatePage.vue'
import BooksEditPage from '../modules/books/pages/BooksEditPage.vue'
import ProductsIndexPage from '../modules/products/pages/ProductsIndexPage.vue'
import ProductsCreatePage from '../modules/products/pages/ProductsCreatePage.vue'
import ProductsEditPage from '../modules/products/pages/ProductsEditPage.vue'
import CoursesIndexPage from '../modules/courses/pages/CoursesIndexPage.vue'
import CoursesCreatePage from '../modules/courses/pages/CoursesCreatePage.vue'
import CoursesEditPage from '../modules/courses/pages/CoursesEditPage.vue'
import UserProfilePage from '../modules/users/pages/UserProfilePage.vue'
import UsersIndexPage from '../modules/users/pages/UsersIndexPage.vue'
import UsersCreatePage from '../modules/users/pages/UsersCreatePage.vue'
import UsersEditPage from '../modules/users/pages/UsersEditPage.vue'
import { initAuth, isAuthenticated, getUserRole } from '../services/auth'
import { canPerformAction } from '../services/permissions'

initAuth()

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/login',
      name: 'login',
      component: LoginView,
      meta: { guestOnly: true },
    },
    {
      path: '/register',
      name: 'register',
      component: RegisterView,
      meta: { guestOnly: true },
    },
    {
      path: '/',
      component: AdminLayout,
      meta: { requiresAuth: true },
      children: [
        {
          path: '',
          redirect: { name: 'dashboard' },
        },
        {
          path: 'dashboard',
          name: 'dashboard',
          component: DashboardPage,
        },
        {
          path: 'books',
          name: 'books.index',
          component: BooksIndexPage,
          meta: { requiredModule: 'books' },
        },
        {
          path: 'books/create',
          name: 'books.create',
          component: BooksCreatePage,
          meta: { requiredModule: 'books' },
        },
        {
          path: 'books/:id/edit',
          name: 'books.edit',
          component: BooksEditPage,
          meta: { requiredModule: 'books' },
        },
        {
          path: 'products',
          name: 'products.index',
          component: ProductsIndexPage,
          meta: { requiredModule: 'products' },
        },
        {
          path: 'products/create',
          name: 'products.create',
          component: ProductsCreatePage,
          meta: { requiredModule: 'products' },
        },
        {
          path: 'products/:id/edit',
          name: 'products.edit',
          component: ProductsEditPage,
          meta: { requiredModule: 'products' },
        },
        {
          path: 'courses',
          name: 'courses.index',
          component: CoursesIndexPage,
          meta: { requiredModule: 'courses' },
        },
        {
          path: 'courses/create',
          name: 'courses.create',
          component: CoursesCreatePage,
          meta: { requiredModule: 'courses' },
        },
        {
          path: 'courses/:id/edit',
          name: 'courses.edit',
          component: CoursesEditPage,
          meta: { requiredModule: 'courses' },
        },
        {
          path: 'user',
          name: 'user.profile',
          component: UserProfilePage,
        },
        {
          path: 'users',
          name: 'users.index',
          component: UsersIndexPage,
          meta: { requiredModule: 'users' },
        },
        {
          path: 'users/create',
          name: 'users.create',
          component: UsersCreatePage,
          meta: { requiredModule: 'users' },
        },
        {
          path: 'users/:id/edit',
          name: 'users.edit',
          component: UsersEditPage,
          meta: { requiredModule: 'users' },
        },
      ],
    },
  ],
})

router.beforeEach((to) => {
  const loggedIn = isAuthenticated()
  const userRole = getUserRole()

  if (to.meta.requiresAuth && !loggedIn) {
    return { name: 'login' }
  }

  if (to.meta.guestOnly && loggedIn) {
    return { name: 'dashboard' }
  }

  // Role-based access control
  if (loggedIn && to.meta.requiredModule) {
    const hasAccess = canPerformAction(userRole, to.meta.requiredModule, 'view')
    if (!hasAccess) {
      console.warn(`Access denied for module: ${to.meta.requiredModule}. User role: ${userRole}`)
      return { name: 'dashboard' }
    }
  }

  return true
})

export default router