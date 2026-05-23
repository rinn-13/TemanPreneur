<template>
  <div class="form-group">
    <label v-if="label" :for="id" class="form-label">{{ label }}</label>
    <input
      :id="id"
      :type="type"
      :value="modelValue"
      :placeholder="placeholder"
      :disabled="disabled"
      :class="['form-input', { 'is-error': error }]"
      @input="$emit('update:modelValue', $event.target.value)"
      @blur="$emit('blur')"
      @focus="$emit('focus')"
    />
    <span v-if="error" class="form-error">{{ error }}</span>
    <small v-if="hint" class="form-hint">{{ hint }}</small>
  </div>
</template>

<script setup>
defineProps({
  id: String,
  type: { type: String, default: 'text' },
  label: String,
  modelValue: [String, Number],
  placeholder: String,
  error: String,
  hint: String,
  disabled: Boolean,
})

defineEmits(['update:modelValue', 'blur', 'focus'])
</script>

<style scoped>
.form-group {
  margin-bottom: 16px;
}

.form-label {
  display: block;
  font-size: 0.9rem;
  font-weight: 700;
  color: #111827;
  margin-bottom: 8px;
}

.form-input {
  width: 100%;
  padding: 10px 14px;
  border: 1.5px solid #e5e7eb;
  border-radius: 8px;
  font-family: 'Plus Jakarta Sans', sans-serif;
  font-size: 0.9rem;
  transition: border-color 0.18s;
}

.form-input:focus {
  outline: none;
  border-color: #e53e3e;
}

.form-input.is-error {
  border-color: #e53e3e;
  background: #fff5f5;
}

.form-input:disabled {
  background: #f3f4f6;
  cursor: not-allowed;
}

.form-error {
  display: block;
  font-size: 0.75rem;
  color: #e53e3e;
  margin-top: 4px;
  font-weight: 500;
}

.form-hint {
  display: block;
  font-size: 0.8rem;
  color: #9ca3af;
  margin-top: 4px;
}
</style>
