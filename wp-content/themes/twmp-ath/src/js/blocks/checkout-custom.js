import { select, on } from 'lib/dom'

export default el => {
	const checkoutBlock = select('[data-block="checkout-custom"]', el) || el
	if (!checkoutBlock) {
		return
	}

	let settings = {}
	try {
		settings = JSON.parse(checkoutBlock.getAttribute('data-settings') || '{}')
	} catch (error) {
		settings = {}
	}

	const stage = select('[data-payment-stage]', checkoutBlock)
	const proofForm = select('[data-payment-proof-form]', checkoutBlock)
	const fileInput = select('[data-payment-file]', checkoutBlock)
	const fileLabel = select('[data-payment-file-label]', checkoutBlock)
	const submitButton = select('[data-payment-submit]', checkoutBlock)
	const notice = select('[data-payment-notice]', checkoutBlock)
	const statusBadge = select('[data-payment-status-badge]', checkoutBlock)
	const statusTitle = select('[data-payment-status-title]', checkoutBlock)
	const statusText = select('[data-payment-status-text]', checkoutBlock)

	if (!stage || !proofForm || !fileInput || !submitButton) {
		return
	}

	if (stage.getAttribute('data-payment-initialized') === '1') {
		return
	}

	stage.setAttribute('data-payment-initialized', '1')

	const ajaxUrl = settings.ajaxUrl || window.ajaxurl || '/wp-admin/admin-ajax.php'
	const orderId = stage.getAttribute('data-order-id') || settings.orderId || ''
	const orderKey = stage.getAttribute('data-order-key') || settings.orderKey || ''
	const nonce = stage.getAttribute('data-payment-nonce') || settings.nonce || ''
	const pollAction = settings.pollAction || 'twmp_checkout_poll_payment_status'
	const uploadAction = settings.uploadAction || 'twmp_checkout_upload_payment_proof'
	const pollInterval = Number.parseInt(settings.pollInterval || 15000, 10) || 15000
	const initialStatus = stage.getAttribute('data-payment-status') || ''
	let pollTimer = null
	let isUploading = false

	const setNotice = (message, type) => {
		if (!notice) {
			return
		}

		notice.textContent = message || ''
		notice.dataset.state = type || ''
		notice.classList.toggle('is-error', type === 'error')
		notice.classList.toggle('is-success', type === 'success')
		notice.classList.toggle('is-waiting', type === 'waiting')
	}

	const setFileLabel = fileName => {
		if (fileLabel) {
			fileLabel.textContent = fileName || settings.fileLabel || 'Choose bill file'
		}
	}

	const setButtonState = (disabled, label) => {
		submitButton.disabled = !!disabled
		submitButton.classList.toggle('is-loading', !!disabled && isUploading)

		if (label) {
			submitButton.textContent = label
		}
	}

	const getStatusPayload = response => {
		if (!response || !response.success || !response.data) {
			return null
		}

		return response.data.status || null
	}

	const applyStatus = payload => {
		if (!payload) {
			return
		}

		stage.setAttribute('data-payment-status', payload.proof_status || '')

		if (statusBadge) {
			statusBadge.textContent = payload.action_label || payload.status_label || ''
		}

		if (statusTitle) {
			statusTitle.textContent = payload.status_label || ''
		}

		if (statusText) {
			statusText.textContent = payload.status_text || ''
		}

		if (payload.proof_status === 'approved') {
			setNotice(payload.status_text || 'Payment confirmed.', 'success')
			setButtonState(true, settings.approvedLabel || 'Confirmed')
			fileInput.disabled = true
			if (pollTimer) {
				window.clearInterval(pollTimer)
				pollTimer = null
			}
			return
		}

		if (payload.proof_status === 'rejected') {
			setNotice(payload.review_note || payload.status_text || 'Bill rejected. Please upload again.', 'error')
			fileInput.disabled = false
			setButtonState(false, settings.uploadLabel || settings.billTitle || 'Upload bill')
			return
		}

		if (payload.proof_status === 'pending_review') {
			setNotice(payload.status_text || 'Waiting for admin review.', 'waiting')
			fileInput.disabled = true
			setButtonState(true, settings.waitingLabel || 'Waiting for confirmation')
			return
		}

		fileInput.disabled = false
		setButtonState(false, settings.uploadLabel || settings.billTitle || 'Upload bill')
		setNotice(payload.status_text || '', '')
	}

	const pollStatus = () => {
		if (!orderId || !orderKey || !nonce) {
			return
		}

		$.ajax({
			url: ajaxUrl,
			method: 'POST',
			dataType: 'json',
			data: {
				action: pollAction,
				order_id: orderId,
				order_key: orderKey,
				nonce: nonce,
			},
		}).done(response => {
			const payload = getStatusPayload(response)
			if (payload) {
				applyStatus(payload)
			}
		})
	}

	const startPolling = () => {
		if (pollTimer) {
			window.clearInterval(pollTimer)
		}

		pollTimer = window.setInterval(pollStatus, pollInterval)
	}

	const uploadBill = file => {
		if (!file) {
			setNotice(settings.noFileMessage || 'Please choose a bill file first.', 'error')
			return
		}

		const formData = new FormData()
		formData.append('action', uploadAction)
		formData.append('order_id', orderId)
		formData.append('order_key', orderKey)
		formData.append('nonce', nonce)
		formData.append('payment_bill', file)

		isUploading = true
		setNotice(settings.uploadingMessage || 'Uploading bill...', 'waiting')
		setButtonState(true, settings.uploadingLabel || 'Uploading...')
		fileInput.disabled = true

		$.ajax({
			url: ajaxUrl,
			method: 'POST',
			dataType: 'json',
			data: formData,
			processData: false,
			contentType: false,
		})
			.done(response => {
				const payload = getStatusPayload(response)
				if (!payload) {
					setNotice((response && response.data && response.data.message) || settings.uploadErrorMessage || 'Upload failed.', 'error')
					return
				}

				setNotice((response.data && response.data.message) || payload.status_text || settings.waitingMessage || 'Waiting for admin confirmation.', 'waiting')
				applyStatus(payload)
				startPolling()
			})
			.fail(xhr => {
				const message = (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) || settings.uploadErrorMessage || 'Upload failed.'
				setNotice(message, 'error')
				fileInput.disabled = false
				setButtonState(false, settings.uploadLabel || settings.billTitle || 'Upload bill')
			})
			.always(() => {
				isUploading = false
			})
	}

	on('submit', event => {
		event.preventDefault()
		uploadBill(fileInput.files && fileInput.files[0] ? fileInput.files[0] : null)
	}, proofForm)

	on('change', function () {
		const file = this.files && this.files[0] ? this.files[0] : null
		setFileLabel(file ? file.name : '')
		if (!file) {
			setNotice('', '')
			return
		}
		setNotice(settings.selectedFileMessage || file.name, '')
	}, fileInput)

	if (stage.getAttribute('data-payment-status') === 'approved') {
		setButtonState(true, settings.approvedLabel || 'Confirmed')
		fileInput.disabled = true
	} else if (stage.getAttribute('data-payment-status') === 'pending_review') {
		setButtonState(true, settings.waitingLabel || 'Waiting for confirmation')
		fileInput.disabled = true
	} else if (stage.getAttribute('data-payment-status') === 'rejected') {
		setButtonState(false, settings.uploadLabel || settings.billTitle || 'Upload bill')
		fileInput.disabled = false
	} else {
		setButtonState(false, settings.uploadLabel || settings.billTitle || 'Upload bill')
		fileInput.disabled = false
	}

	setFileLabel('')
	pollStatus()
	if (initialStatus !== 'approved') {
		startPolling()
	}

	/**
	 * Helper function to load xÃ£/phÆ°á»ng (wards)
	 * Usage: window.twmpLoadXaPhuong(maqh, selectedWard, '#ward-select')
	 */
	window.twmpLoadXaPhuong = function (maqh, selectedWard = null, selectorOrElement = '.xa-phuong-select') {
		if (!maqh) {
			console.warn('maqh (district code) is required')
			return
		}

		let nonceValue = ''
		try {
			nonceValue = JSON.parse(checkoutBlock.getAttribute('data-settings') || '{}').nonce || ''
		} catch (error) {
			nonceValue = ''
		}

		$.ajax({
			url: ajaxUrl,
			method: 'POST',
			data: {
				action: 'get_xa_phuong_by_maqh',
				maqh: maqh,
				nonce: nonceValue,
			},
			success: function (res) {
				if (res.success) {
					const $wardSelect = $(selectorOrElement)
					$wardSelect.empty()
					$wardSelect.append($('<option>', { value: '', text: '-- Chọn xã/phường --' }))

					res.data.forEach(function (item) {
						const option = $('<option>', {
							value: item.name,
							text: item.name,
						})

						if (item.name === selectedWard) {
							option.prop('selected', true)
						}

						$wardSelect.append(option)
					})
				}
			},
			error: function (xhr) {
				console.error('Failed to load xã/phường:', xhr)
			},
		})
	}
}
