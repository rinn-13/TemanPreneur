export const API_BASE_URL = (import.meta.env.VITE_API_URL || 'http://localhost:8000').replace(/\/+$/g, '')
export const STORAGE_URL = `${API_BASE_URL}/storage`