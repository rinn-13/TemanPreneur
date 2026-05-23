import { STORAGE_URL } from '@/config'

export const PLACEHOLDER_IMAGE = '/placeholder.svg'
export const PLACEHOLDER_PRODUCT = '/placeholder.svg'
export const DEFAULT_BUSINESS_LOGO = '/avatars/default-seller.svg'
export const DEFAULT_SELLER_PREMIUM_LOGO = '/avatars/default-seller-premium.svg'

const DEFAULT_AVATARS = {
  buyer: '/avatars/default-buyer.svg',
  seller: '/avatars/default-seller.svg',
  seller_premium: '/avatars/default-seller-premium.svg',
  admin: '/avatars/default-admin.svg',
  guest: '/avatars/default-buyer.svg',
}

export function getDefaultAvatar(role = 'buyer') {
  const key = ['seller_premium', 'seller', 'admin', 'buyer'].find((r) => role?.includes?.(r) || role === r) || 'buyer'
  return DEFAULT_AVATARS[key] || DEFAULT_AVATARS.buyer
}

/** User profile photo with role-based fallback */
export function resolveAvatar(user, roleOverride = null) {
  const role = roleOverride || user?.role || user?.active_role || 'buyer'
  const photo =
    user?.photo ||
    user?.avatar ||
    user?.photo_url ||
    user?.profile_photo ||
    user?.profile_photo_path ||
    user?.profile_picture ||
    user?.avatar_url ||
    user?.profileImage ||
    user?.image

  const normalized = normalizeImageUrl(photo, null)
  return normalized || getDefaultAvatar(role)
}

const DEFAULT_PLACEHOLDER = PLACEHOLDER_IMAGE

/** Category slugs hidden from admin performance charts */
export const EXCLUDED_CATEGORY_SLUGS = new Set(['lainnya', 'other', 'uncategorized', 'tanpa-kategori', ''])

/** Pick first usable image from product-like object */
export function pickProductImageSource(item) {
  if (!item || typeof item !== 'object') return null
  const gallery = Array.isArray(item.images) ? item.images.filter(Boolean) : []
  const fromGallery = Array.isArray(item.gallery) ? item.gallery.filter(Boolean) : []
  return (
    item.image ||
    item.gambar ||
    item.imageUrl ||
    item.image_url ||
    item.thumbnail ||
    item.thumb ||
    item.cover ||
    gallery[0] ||
    fromGallery[0] ||
    null
  )
}

/** Product thumbnail — always returns a displayable URL */
export function resolveProductImage(src, fallback = PLACEHOLDER_PRODUCT) {
  const picked = typeof src === 'object' && src !== null ? pickProductImageSource(src) : src
  return normalizeImageUrl(picked, fallback)
}

/** Business / store logo */
export function resolveBusinessLogo(src, isPremium = false) {
  const fallback = isPremium ? DEFAULT_SELLER_PREMIUM_LOGO : DEFAULT_BUSINESS_LOGO
  return normalizeImageUrl(src, fallback)
}

/** Business banner — falls back to logo then placeholder */
export function resolveBusinessBanner(src, logoFallback = null, isPremium = false) {
  const normalized = normalizeImageUrl(src, null)
  if (normalized) return normalized
  if (logoFallback) return resolveBusinessLogo(logoFallback, isPremium)
  return PLACEHOLDER_IMAGE
}

/**
 * Strip nested /storage/... and duplicate absolute URL segments.
 */
function extractStorageRelativePath(input) {
  let value = String(input).trim().replace(/\\/g, '/')

  const lastStorage = value.lastIndexOf('/storage/')
  if (lastStorage >= 0) {
    value = value.slice(lastStorage + '/storage/'.length)
  } else if (/^https?:\/\//i.test(value)) {
    try {
      const pathname = new URL(value).pathname || ''
      if (pathname.includes('/storage/')) {
        value = pathname.slice(pathname.indexOf('/storage/') + '/storage/'.length)
      } else {
        value = pathname.replace(/^\/+/, '')
      }
    } catch {
      // keep value as-is
    }
  }

  value = value.replace(/\/+/g, '/').replace(/^\/+/, '')
  value = value.replace(/^(storage\/)+/i, '')
  return value
}

/**
 * Normalize product/user/media paths for Laravel public/storage.
 * Handles: relative paths, /storage/..., full URLs, duplicated domains, null.
 */
export function normalizeImageUrl(src, fallback = DEFAULT_PLACEHOLDER) {
  if (src == null || src === '') {
    return fallback === null ? null : fallback
  }

  if (typeof src === 'object') {
    if (src?.url) {
      return normalizeImageUrl(src.url, fallback)
    }
    return fallback === null ? null : fallback
  }

  if (typeof src !== 'string') {
    return fallback === null ? null : fallback
  }

  let trimmed = String(src).trim()
  if (!trimmed) {
    return fallback === null ? null : fallback
  }

  if (trimmed.startsWith('data:') || trimmed.startsWith('blob:')) {
    return trimmed
  }

  // Site-root static assets (placeholders, avatars)
  if (trimmed.startsWith('/avatars/') || trimmed.startsWith('/placeholder')) {
    return trimmed
  }

  // Corrupted double-URL strings: keep the last http(s) segment then re-parse
  const httpMatches = trimmed.match(/https?:\/\/[^\s'"]+/gi)
  if (httpMatches && httpMatches.length > 1) {
    trimmed = httpMatches[httpMatches.length - 1]
  }

  // Absolute URL — always canonicalize /storage/ paths to current API base
  if (/^https?:\/\//i.test(trimmed) && !trimmed.includes('/storage/http')) {
    try {
      const path = new URL(trimmed).pathname || ''
      if (path.includes('/storage/')) {
        const rel = extractStorageRelativePath(trimmed)
        if (rel) return `${STORAGE_URL}/${rel}`
      }
      return trimmed
    } catch {
      return fallback === null ? null : fallback
    }
  }

  const relative = extractStorageRelativePath(trimmed)
  if (!relative) {
    return fallback === null ? null : fallback
  }

  if (relative.startsWith('avatars/') || relative.startsWith('placeholder')) {
    return `/${relative}`
  }

  return `${STORAGE_URL}/${relative}`
}

/**
 * Use on <img @error> to swap to fallback without broken icon.
 */
export function onImageError(event, fallback = DEFAULT_PLACEHOLDER) {
  const el = event?.target
  if (!el || el.dataset?.tpFallbackApplied === '1') return
  el.dataset.tpFallbackApplied = '1'
  el.src = fallback || DEFAULT_PLACEHOLDER
}

export default normalizeImageUrl
