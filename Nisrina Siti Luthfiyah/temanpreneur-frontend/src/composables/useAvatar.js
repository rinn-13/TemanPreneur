import {
  normalizeImageUrl as normalizeUrl,
  onImageError,
  PLACEHOLDER_IMAGE,
} from '@/utils/image'

export { normalizeImageUrl, onImageError, PLACEHOLDER_IMAGE } from '@/utils/image'

/**
 * Avatar helper — selalu mengembalikan URL gambar valid (bukan inisial teks)
 */
const DEFAULT_AVATARS = {
  buyer: '/avatars/default-buyer.svg',
  seller: '/avatars/default-seller.svg',
  seller_premium: '/avatars/default-seller-premium.svg',
  admin: '/avatars/default-admin.svg',
  guest: '/avatars/default-buyer.svg',
}

export function getDefaultAvatar(role = 'buyer') {
  const key = ['seller_premium', 'seller', 'admin', 'buyer'].find(r => role?.includes?.(r) || role === r) || 'buyer'
  return DEFAULT_AVATARS[key] || DEFAULT_AVATARS.buyer
}

export function resolveAvatar(user, roleOverride = null) {
  const role = roleOverride || user?.role || user?.active_role || 'buyer'
  const photo =
    user?.photo ||
    user?.avatar ||
    user?.photo_url ||
    user?.profile_photo ||
    user?.avatar_url ||
    user?.profileImage

  const normalized = normalizeUrl(photo, null)
  return normalized || getDefaultAvatar(role)
}

export function useAvatar() {
  return { resolveAvatar, getDefaultAvatar, normalizeImageUrl: normalizeUrl, onImageError, PLACEHOLDER_IMAGE }
}

export default useAvatar
