// utils/roles.js

export function getActiveRole(user) {
  const activeRole = localStorage.getItem('activeRole')
  if (activeRole) return String(activeRole).toLowerCase()

  if (!user) return null
  if (typeof user.role === 'string' && user.role.trim()) {
    return user.role.toLowerCase()
  }

  if (Array.isArray(user.roles) && user.roles.length) {
    return String(user.roles[0]).toLowerCase()
  }

  return null
}

export function parseRoles(user) {
  const activeRole = getActiveRole(user)
  if (activeRole) return [activeRole]
  if (!user) return []

  let roles = user.roles || user.role

  if (typeof roles === 'string') {
    return [roles.toLowerCase()]
  }

  if (Array.isArray(roles)) {
    return roles.map(r => String(r).toLowerCase())
  }

  return []
}

export function hasRole(user, roleName) {
  const roles = parseRoles(user)
  return roles.includes(roleName.toLowerCase())
}

export function isBuyerOnly(user) {
  const roles = parseRoles(user)

  const isBuyer = roles.includes('buyer')
  const isAdmin = roles.includes('admin')
  const isSeller = roles.includes('seller')

  return isBuyer && !isAdmin && !isSeller
}