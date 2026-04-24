import { createRouter, createWebHistory } from 'vue-router'
import LoginView from '../views/LoginView.vue'
import AdminLayout from '../layouts/AdminLayout.vue'
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
import { initAuth, isAuthenticated } from '../services/auth'

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
      path: '/',
      component: AdminLayout,
      meta: { requiresAuth: true },
      children: [
        {
          path: '',
          redirect: { name: 'books.index' },
        },
        {
          path: 'books',
          name: 'books.index',
          component: BooksIndexPage,
        },
        {
          path: 'books/create',
          name: 'books.create',
          component: BooksCreatePage,
        },
        {
          path: 'books/:id/edit',
          name: 'books.edit',
          component: BooksEditPage,
        },
        {
          path: 'products',
          name: 'products.index',
          component: ProductsIndexPage,
        },
        {
          path: 'products/create',
          name: 'products.create',
          component: ProductsCreatePage,
        },
        {
          path: 'products/:id/edit',
          name: 'products.edit',
          component: ProductsEditPage,
        },
        {
          path: 'courses',
          name: 'courses.index',
          component: CoursesIndexPage,
        },
        {
          path: 'courses/create',
          name: 'courses.create',
          component: CoursesCreatePage,
        },
        {
          path: 'courses/:id/edit',
          name: 'courses.edit',
          component: CoursesEditPage,
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
        },
        {
          path: 'users/create',
          name: 'users.create',
          component: UsersCreatePage,
        },
        {
          path: 'users/:id/edit',
          name: 'users.edit',
          component: UsersEditPage,
        },
      ],
    },
  ],
})

router.beforeEach((to) => {
  const loggedIn = isAuthenticated()

  if (to.meta.requiresAuth && !loggedIn) {
    return { name: 'login' }
  }

  if (to.meta.guestOnly && loggedIn) {
    return { name: 'books.index' }
  }

  return true
})

export default router