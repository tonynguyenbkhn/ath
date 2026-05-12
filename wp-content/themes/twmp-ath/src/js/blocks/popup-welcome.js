import Modal from 'lib/modal'
import { trigger } from 'lib/dom'

const DEFAULT_FWP_PREFIX = '_'
const SEARCH_FACETS = {
	date: 'date_time',
	type: 'event_type',
	location: 'location',
}
const SEARCH_REQUIRED_FIELDS = [
	{
		name: 'ath_date',
		label: 'Date',
	},
	{
		name: 'ath_show_type',
		label: 'Type of show/event',
	},
	{
		name: 'ath_location',
		label: 'Location',
	},
]
const SEARCH_ERROR_MESSAGE = 'Please fill in date, type of show/event, and location.'
const FIELD_REQUIRED_MESSAGE = 'This field is required.'

const getFacetPrefix = () => {
	return window.FWP_JSON?.prefix || DEFAULT_FWP_PREFIX
}

const normalizeFacetValue = value =>
	String(value || '')
		.trim()
		.toLowerCase()
		.replace(/['"]/g, '')
		.replace(/&/g, 'and')
		.replace(/[^a-z0-9]+/g, '-')
		.replace(/^-+|-+$/g, '')

const getShopUrl = () => {
	const shopUrl = window.twmpConfig?.woocommerce?.shopUrl || ''

	if (shopUrl) {
		return shopUrl
	}

	return `${window.location.origin}/shop/`
}

const getFieldControl = (form, fieldName) => {
	return form.querySelector(`[name="${fieldName}"]`)
}

const getResponseOutput = form => {
	return form.querySelector('.wpcf7-response-output')
}

const getFieldErrorElement = field => {
	if (!field) {
		return null
	}

	const fieldWrapper = field.closest('.ath-field, .register-form__field')

	if (!fieldWrapper) {
		return null
	}

	let errorElement = fieldWrapper.querySelector('.ath-field-error')

	if (!errorElement) {
		errorElement = document.createElement('div')
		errorElement.className = 'ath-field-error'
		errorElement.setAttribute('aria-live', 'polite')
		errorElement.setAttribute('aria-hidden', 'true')
		fieldWrapper.prepend(errorElement)
	}

	return errorElement
}

const setFieldError = (field, message) => {
	const errorElement = getFieldErrorElement(field)

	if (!errorElement) {
		return
	}

	errorElement.textContent = message || ''
	errorElement.classList.toggle('is-visible', Boolean(message))
	errorElement.setAttribute('aria-hidden', message ? 'false' : 'true')
}

const getFieldWrapper = field => field?.closest('.ath-field, .register-form__field')

const setResponseMessage = (form, message, isError = true) => {
	const output = getResponseOutput(form)

	if (!output) {
		return
	}

	output.textContent = message
	output.setAttribute('aria-hidden', message ? 'false' : 'true')
	output.classList.toggle('is-visible', Boolean(message))
	output.classList.toggle('is-error', Boolean(message) && isError)
}

const clearFieldError = field => {
	if (!field) {
		return
	}

	field.setAttribute('aria-invalid', 'false')
	field.setCustomValidity('')
	field.classList.remove('wpcf7-not-valid')
	getFieldWrapper(field)?.classList.remove('is-invalid')
	setFieldError(field, '')
}

const markFieldError = field => {
	if (!field) {
		return
	}

	field.setAttribute('aria-invalid', 'true')
	field.setCustomValidity(field.dataset.requiredMessage || FIELD_REQUIRED_MESSAGE)
	field.classList.add('wpcf7-not-valid')
	getFieldWrapper(field)?.classList.add('is-invalid')
	setFieldError(field, field.dataset.requiredMessage || FIELD_REQUIRED_MESSAGE)
}

const syncRequiredState = form => {
	SEARCH_REQUIRED_FIELDS.forEach(({ name, label }) => {
		const field = getFieldControl(form, name)

		if (!field) {
			return
		}

		field.required = true
		field.setAttribute('aria-required', 'true')
		field.dataset.requiredMessage = `${label} is required.`
		getFieldWrapper(field)?.classList.add('ath-field-wrap')
		setFieldError(field, '')
	})
}

const clearSearchErrors = form => {
	SEARCH_REQUIRED_FIELDS.forEach(({ name }) => {
		clearFieldError(getFieldControl(form, name))
	})

	setResponseMessage(form, '', false)
}

const validateSearchForm = form => {
	let firstInvalidField = null

	SEARCH_REQUIRED_FIELDS.forEach(({ name }) => {
		const field = getFieldControl(form, name)
		const value = String(field?.value || '').trim()

		if (!field) {
			return
		}

		if (value) {
			clearFieldError(field)
			return
		}

		markFieldError(field)

		if (!firstInvalidField) {
			firstInvalidField = field
		}
	})

	if (!firstInvalidField) {
		setResponseMessage(form, '', false)
		return true
	}

	setResponseMessage(form, SEARCH_ERROR_MESSAGE, true)
	firstInvalidField.focus({ preventScroll: false })

	if (typeof firstInvalidField.reportValidity === 'function') {
		firstInvalidField.reportValidity()
	}

	return false
}

const buildSearchUrl = form => {
	const url = new URL(getShopUrl(), window.location.origin)
	const prefix = getFacetPrefix()
	const dateValue = form.querySelector('[name="ath_date"]')?.value?.trim() || ''
	const typeValue = form.querySelector('[name="ath_show_type"]')?.value?.trim() || ''
	const locationValue = form.querySelector('[name="ath_location"]')?.value?.trim() || ''

	if (dateValue) {
		url.searchParams.set(`${prefix}${SEARCH_FACETS.date}`, `${dateValue},${dateValue}`)
	}

	if (typeValue) {
		url.searchParams.set(`${prefix}${SEARCH_FACETS.type}`, normalizeFacetValue(typeValue))
	}

	if (locationValue) {
		url.searchParams.set(`${prefix}${SEARCH_FACETS.location}`, normalizeFacetValue(locationValue))
	}

	return url.toString()
}

const bindSearchSubmit = form => {
	if (!form || form.dataset.twmpPopupSearchBound === '1') {
		return
	}

	form.dataset.twmpPopupSearchBound = '1'
	syncRequiredState(form)

	SEARCH_REQUIRED_FIELDS.forEach(({ name }) => {
		const field = getFieldControl(form, name)

		if (!field) {
			return
		}

		field.addEventListener('input', () => {
			clearFieldError(field)
			setResponseMessage(form, '', false)
		})

		field.addEventListener('change', () => {
			clearFieldError(field)
			setResponseMessage(form, '', false)
		})
	})

	form.addEventListener(
		'submit',
		event => {
			event.preventDefault()
			event.stopImmediatePropagation()

			if (!validateSearchForm(form)) {
				return
			}

			window.location.href = buildSearchUrl(form)
		},
		true
	)
}

export default el => {
	Modal(el, {
		id: 'modal-popup-welcome'
	})

	const form = el.querySelector('.wpcf7-form')

	bindSearchSubmit(form)
	// trigger('activate', el)
}
