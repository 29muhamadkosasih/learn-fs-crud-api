export function getActionLabel(action) {
    const labels = {
        'view': 'View',
        'create': 'Create',
        'edit': 'Edit',
        'delete': 'Delete',
    }
    return labels[action] || action
}

export function getActionBadgeColor(action) {
    const colors = {
        'view': 'info',
        'create': 'success',
        'edit': 'warning',
        'delete': 'danger',
    }
    return colors[action] || 'secondary'
}

export function formatPermissionName(name) {
    // Convert "books.view" to "Books - View"
    const [module, action] = name.split('.')
    return `${module.charAt(0).toUpperCase() + module.slice(1)} - ${getActionLabel(action)}`
}
