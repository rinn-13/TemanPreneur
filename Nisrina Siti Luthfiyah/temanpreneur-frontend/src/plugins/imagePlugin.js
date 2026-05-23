import {
  normalizeImageUrl,
  onImageError,
  PLACEHOLDER_IMAGE,
  resolveProductImage,
  resolveBusinessLogo,
  resolveBusinessBanner,
  pickProductImageSource,
} from '@/utils/image'

/**
 * Global image URL helpers for Options API and Composition API.
 */
export default {
  install(app) {
    app.config.globalProperties.$normalizeImageUrl = normalizeImageUrl
    app.config.globalProperties.$onImageError = onImageError
    app.config.globalProperties.$resolveProductImage = resolveProductImage
    app.config.globalProperties.$resolveBusinessLogo = resolveBusinessLogo
    app.config.globalProperties.$resolveBusinessBanner = resolveBusinessBanner
    app.provide('normalizeImageUrl', normalizeImageUrl)
    app.provide('onImageError', onImageError)
    app.provide('resolveProductImage', resolveProductImage)
    app.provide('resolveBusinessLogo', resolveBusinessLogo)
  },
}
