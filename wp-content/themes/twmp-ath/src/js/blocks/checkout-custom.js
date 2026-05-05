import { select, on } from 'lib/dom'

export default el => {
	const checkoutBlock = select('[data-block="checkout-custom"]', el) || el
	
	if (!checkoutBlock) {
		return
	}
}
