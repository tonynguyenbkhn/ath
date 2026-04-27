import {
	delegate,
	on,
	appendHtml,
	selectAll,
	addClass,
	removeClass,
	toggleClass,
	closest
} from 'lib/dom'
import { map } from 'lib/utils'

export default el => {
	const ACTIVE_CLASS = 'is-slideout-menu-opened'
	const ACTIVE_MENU_ITEM_CLASS = 'is-active'
	const body = document.body
	const parentMenuItems = selectAll('.menu-item-has-children', el)
	const menuTriggerEls = selectAll('.menu-item-has-children > a', el)
	const dropdownMenuEls = selectAll('.sub-menu', el)
	const open = () => {
		addClass(ACTIVE_CLASS, body)
	}

	const close = () => {
		removeClass(ACTIVE_CLASS, body)
	}

	const toggle = () => {
		toggleClass(ACTIVE_CLASS, body)
	}

	on('slideoutMenu.open', open, body)
	on('slideoutMenu.close', close, body)

	delegate('click', toggle, '.js-slideout-menu-open', body)

	delegate('click', toggle, '[data-slideout-menu-trigger]', body)

	// Rewrite a menu link with dropdown-toggle
	map(menuTriggerEl => {
		const menuName = menuTriggerEl.innerHTML
		menuTriggerEl.innerHTML = `<span>${menuName}</span><span class="dropdown-toggle" aria-hidden="true"></span>`
	}, menuTriggerEls)

	map(dropdownMenuEl => {
		const dropdownBackButton = `
		<li class="menu-item--back">
			<button class="dropdown-back-button">
				<span class="icon"></span>
				<span class="text">Quay lại</span>
			</button>
		</li>`

		appendHtml(dropdownMenuEl, dropdownBackButton)
	}, dropdownMenuEls)

	const openSubMenu = currentParentMenuItem => {
		map(parentMenuItem => {
			if (currentParentMenuItem === parentMenuItem) {
				toggleClass(ACTIVE_MENU_ITEM_CLASS, parentMenuItem)
			} else {
				const childActiveItems = selectAll('.is-active', parentMenuItem)

				if (!childActiveItems) {
					removeClass(ACTIVE_MENU_ITEM_CLASS, parentMenuItem)
				}
			}
		}, parentMenuItems)
	}

	if (menuTriggerEls) {
		on(
			'click',
			e => {
				e.preventDefault()

				const menuTriggerEl = e.target

				const currentParentMenuItem = closest(
					'.menu-item-has-children',
					menuTriggerEl
				)

				openSubMenu(currentParentMenuItem)
			},
			menuTriggerEls
		)
	}

	delegate(
		'click',
		e => {
			const parentMenuItem = closest('.menu-item-has-children', e.target)

			removeClass(ACTIVE_MENU_ITEM_CLASS, parentMenuItem)
		},
		'.dropdown-back-button',
		body
	)
}
