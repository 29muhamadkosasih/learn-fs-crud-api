import { getStoredUserData } from './auth'
import { MODULES, ACTIONS, DEFAULT_ROLE_PERMISSIONS } from './permissionCatalog'

function getPermissionOverrides(role) {
  const userData = getStoredUserData()

  if (!userData || userData.role !== role || !userData.permissions || typeof userData.permissions !== 'object') {
    return null
  }

  return userData.permissions
}

/**
 * Get all modules accessible by a role
 */
export function getAccessibleModules(role) {
  const overrides = getPermissionOverrides(role)

  if (overrides) {
    const modules = new Set()

    Object.keys(overrides).forEach((permission) => {
      if (overrides[permission]) {
        const [moduleName, action] = permission.split('.')
        if (moduleName && action === 'view') {
          modules.add(moduleName)
        }
      }
    })

    return Array.from(modules)
  }

  if (!DEFAULT_ROLE_PERMISSIONS[role]) {
    return []
  }

  const modules = new Set()
  const moduleRegex = /^(.+?)\.view$/

  Object.keys(DEFAULT_ROLE_PERMISSIONS[role]).forEach((permission) => {
    if (DEFAULT_ROLE_PERMISSIONS[role][permission]) {
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
  const overrides = getPermissionOverrides(role)

  if (overrides && Object.prototype.hasOwnProperty.call(overrides, permission)) {
    return overrides[permission] === true
  }

  if (!DEFAULT_ROLE_PERMISSIONS[role]) {
    return false
  }

  return DEFAULT_ROLE_PERMISSIONS[role][permission] === true
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
