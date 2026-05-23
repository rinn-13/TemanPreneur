import { reactive, computed } from 'vue'

export function useFormValidation(initialValues = {}) {
  const values = reactive({ ...initialValues })
  const errors = reactive({})
  const touched = reactive({})

  const resetForm = () => {
    Object.keys(values).forEach(k => delete values[k])
    Object.assign(values, { ...initialValues })
    Object.keys(errors).forEach(k => delete errors[k])
    Object.keys(touched).forEach(k => delete touched[k])
  }

  const setFieldValue = (field, value) => {
    values[field] = value
  }

  const setFieldError = (field, error) => {
    errors[field] = error
  }

  const setFieldTouched = (field, isTouched = true) => {
    touched[field] = isTouched
  }

  const validateField = (field, value, rules = {}) => {
    let error = null

    // Required rule
    if (rules.required && (!value || (typeof value === 'string' && !value.trim()))) {
      error = rules.requiredMessage || `${field} harus diisi`
      setFieldError(field, error)
      return error
    }

    // Email rule
    if (rules.email && value && !isValidEmail(value)) {
      error = rules.emailMessage || 'Format email tidak valid'
      setFieldError(field, error)
      return error
    }

    // Min length rule
    if (rules.minLength && value) {
      const length = typeof value === 'string' ? value.trim().length : value.toString().length
      if (length < rules.minLength) {
        error = rules.minLengthMessage || `${field} minimal ${rules.minLength} karakter`
        setFieldError(field, error)
        return error
      }
    }

    // Max length rule
    if (rules.maxLength && value && value.toString().length > rules.maxLength) {
      error = rules.maxLengthMessage || `${field} maksimal ${rules.maxLength} karakter`
      setFieldError(field, error)
      return error
    }

    // Min value rule
    if (rules.min !== undefined && value < rules.min) {
      error = rules.minMessage || `${field} minimal ${rules.min}`
      setFieldError(field, error)
      return error
    }

    // Max value rule
    if (rules.max !== undefined && value > rules.max) {
      error = rules.maxMessage || `${field} maksimal ${rules.max}`
      setFieldError(field, error)
      return error
    }

    // Pattern rule (regex)
    if (rules.pattern && value && !rules.pattern.test(value)) {
      error = rules.patternMessage || `${field} format tidak valid`
      setFieldError(field, error)
      return error
    }

    // Custom rule
    if (rules.custom) {
      const customError = rules.custom(value)
      if (customError) {
        error = customError
        setFieldError(field, error)
        return error
      }
    }

    // Clear error if validation passes
    setFieldError(field, null)
    return null
  }

  const validateForm = (allRules) => {
    // clear existing errors
    Object.keys(errors).forEach(k => delete errors[k])
    let hasErrors = false

    for (const [field, rules] of Object.entries(allRules)) {
      const value = values[field]
      const error = validateField(field, value, rules)
      if (error) {
        hasErrors = true
      }
    }

    return !hasErrors
  }

  const handleBlur = (field) => {
    setFieldTouched(field, true)
  }

  const handleChange = (field, value) => {
    setFieldValue(field, value)
    // Clear error when user starts typing
    if (errors[field]) {
      setFieldError(field, null)
    }
  }

  const isFormValid = computed(() => Object.keys(errors).length === 0 && Object.keys(touched).length > 0)

  return {
    values,
    errors,
    touched,
    resetForm,
    setFieldValue,
    setFieldError,
    setFieldTouched,
    validateField,
    validateForm,
    handleBlur,
    handleChange,
    isFormValid,
  }
}

function isValidEmail(email) {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  return emailRegex.test(email)
}
