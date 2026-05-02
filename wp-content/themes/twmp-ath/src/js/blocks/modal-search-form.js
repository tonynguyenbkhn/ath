import Modal from 'lib/modal'
import { select, setStyle } from 'lib/dom'

export default el => {
	const mainMenu = select('.header__main .header__nav .main-menu')

	el.addEventListener('activate', () => {
		if (mainMenu) {
			setStyle('opacity', '0', mainMenu)
		}
	})

	el.addEventListener('deactivate', () => {
		if (mainMenu) {
			setStyle('opacity', '1', mainMenu)
		}
	})

	Modal(el, {
		id: 'modal-search-form'
	})
}
