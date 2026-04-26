/**
 * Role-based permissions mapping
 * Each role has specific permissions for CRUD operations
 */
const ROLE_PERMISSIONS = {
  admin: {
    // Books module
    'books.view': true,
    'books.create': true,
    'books.edit': true,
    'books.delete': true,

    // Products module
    'products.view': true,
    'products.create': true,
    'products.edit': true,
    'products.delete': true,

    // Courses module
    'courses.view': true,
    'courses.create': true,
    'courses.edit': true,
    'courses.delete': true,

    // Users module
    'users.view': true,
    'users.create': true,
    'users.edit': true,
    'users.delete': true,
  },

  manager: {
    // Books module
    'books.view': true,
    'books.create': true,
    'books.edit': true,
    'books.delete': false,

    // Products module
    'products.view': true,
    'products.create': true,
    'products.edit': true,
    'products.delete': false,

    // Courses module
    'courses.view': true,
    'courses.create': true,
    'courses.edit': true,
    'courses.delete': false,

    // Users module (managers can't manage users)
    'users.view': false,
    'users.create': false,
    'users.edit': false,
    'users.delete': false,
  },

  user: {
    // Books module (view only)
    'books.view': true,
    'books.create': false,
    'books.edit': false,
    'books.delete': false,

    // Products module (view only)
    'products.view': true,
    'products.create': false,
    'products.edit': false,
    'products.delete': false,

    // Courses module (view only)
    'courses.view': true,
    'courses.create': false,
    'courses.edit': false,
    'courses.delete': false,

    // Users module (no access)
    'users.view': false,
    'users.create': false,
    'users.edit': false,
    'users.delete': false,
  },
}

/**
 * Get all modules accessible by a role
 */
export function getAccessibleModules(role) {
  if (!ROLE_PERMISSIONS[role]) {
    return []
  }

  const modules = new Set()
  const moduleRegex = /^(.+?)\.view$/

  Object.keys(ROLE_PERMISSIONS[role]).forEach((permission) => {
    if (ROLE_PERMISSIONS[role][permission]) {
      const match = permission.match(moduleRegex)
      if (match && match[1]) {
        modules.add(match[1])
      }
    }
  })

  return Array.from(modules)
}

/**
 * Check if a user with given role has permission for an action
 * @param {string} role - User role (admin, manager, user)
 * @param {string} permission - Permission key (e.g., 'books.create')
 * @returns {boolean}
 */
export function hasPermission(role, permission) {
  if (!ROLE_PERMISSIONS[role]) {
    return false
  }

  return ROLE_PERMISSIONS[role][permission] === true
}

/**
 * Check if a user can perform an action on a specific module
 * @param {string} role - User role
 * @param {string} module - Module name (books, products, courses, users)
 * @param {string} action - Action name (view, create, edit, delete)
 * @returns {boolean}
 */
export function canPerformAction(role, module, action) {
  const permission = `${module}.${action}`
  return hasPermission(role, permission)
}

/**
 * Get permitted actions for a module
 * @param {string} role - User role
 * @param {string} module - Module name
 * @returns {Object} Object with action permissions
 */
export function getModulePermissions(role, module) {
  const actions = ['view', 'create', 'edit', 'delete']
  const permissions = {}

  actions.forEach((action) => {
    permissions[action] = canPerformAction(role, module, action)
  })

  return permissions
}

/**
 * Get action buttons visibility for a role
 * @param {string} role - User role
 * @param {string} module - Module name
 * @returns {Object}
 */
export function getActionButtons(role, module) {
  return {
    view: canPerformAction(role, module, 'view'),
    create: canPerformAction(role, module, 'create'),
    edit: canPerformAction(role, module, 'edit'),
    delete: canPerformAction(role, module, 'delete'),
  }
}

/**
 * Check if user has any admin-level access
 */
export function isAdmin(role) {
  return role === 'admin'
}

/**
 * Check if user has manager-level access or higher
 */
export function isManagerOrAbove(role) {
  return role === 'manager' || role === 'admin'
}
