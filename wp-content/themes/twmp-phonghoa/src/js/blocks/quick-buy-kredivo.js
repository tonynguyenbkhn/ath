import {
	on,
	selectAll,
	select,
	getAttribute,
	setAttribute,
	closest
} from 'lib/dom'

export default el => {
	const formEl = closest('.cart', el)
	const quantityEl = select('input[name="quantity"]', formEl)
	const variableItemSelect = selectAll('.variations select', formEl)

	const submitBuyNowForm = () => {
		const addToCartUrl = twmpConfig.woocommerce.addToCartUrl
		const productIdEl = select('input[name="product_id"]', formEl)
		const variationIdEl = select('input[name="variation_id"]', formEl)
		const productId = Number(productIdEl?.value || 0)
		const variationId = Number(variationIdEl?.value || 0)
		const quantity = Number(quantityEl?.value || 1)
		const productToAdd = variationId || productId
		// Tạo form ẩn
		const tempForm = document.createElement('form')
		tempForm.method = 'POST'
		tempForm.action = `${addToCartUrl}/?add-to-cart=${productToAdd}&buy_now=1&pay=kredivo`
		tempForm.style.display = 'none'

		// Tạo input cần thiết
		const input = (name, value) => {
			const i = document.createElement('input')
			i.type = 'hidden'
			i.name = name
			i.value = value
			return i
		}

		// Append dữ liệu
		tempForm.appendChild(input('add-to-cart', variationId || productId))
		tempForm.appendChild(input('quantity', quantity))
		tempForm.appendChild(input('buy_now', '1'))
		tempForm.appendChild(input('pay', 'kredivo'))

		variableItemSelect.forEach(selectEl => {
			if (selectEl.value) {
				tempForm.appendChild(input(selectEl.name, selectEl.value))
			}
		})

		// Thêm form và submit
		document.body.appendChild(tempForm)
		tempForm.submit()
	}

	const checkStatusButton = () => {
		let allSelected = true

		variableItemSelect.forEach(selectEl => {
			if (!selectEl.value.trim()) {
				allSelected = false
			}
		})

		return allSelected
	}

	on('click', e => {
		e.preventDefault()

		if (checkStatusButton()) {
			submitBuyNowForm()
		}
	}, el)
}
