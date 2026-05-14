export default el => {
	if (!el) {
		return null
	}

	const searchForm = el.querySelector('.woocommerce-product-search')
	const searchInput = searchForm?.querySelector('input[type="search"]')
	const categoriesFacet = el.querySelector('.facetwp-facet-categories')
	const filterMobileEvent = el.querySelector('.filter-mobile-event')
	const closeFilterButton = el.querySelector('.btn-close-filter')
	const wrapper = el.querySelector('.twmp-shop-layout__left-wrap') || el
	const shopLayoutWrapper = el.closest('.twmp-shop-layout-wrapper') || el
	const shopRightColumn = shopLayoutWrapper.querySelector('.twmp-shop-layout__right')
	let isFilterOpen = false

	const setFilterOpen = isOpen => {
		isFilterOpen = isOpen
		shopLayoutWrapper.classList.toggle('is-filter-open', isOpen)
		document.body.classList.toggle('is-shop-filter-lock', isOpen)
	}

	const closeFilter = () => {
		setFilterOpen(false)
	}

	const isClickOutsideFilter = eventTarget => {
		if (!eventTarget || !shopRightColumn) {
			return false
		}

		return !shopRightColumn.contains(eventTarget) && !filterMobileEvent?.contains(eventTarget)
	}

	filterMobileEvent?.addEventListener('click', () => {
		setFilterOpen(!isFilterOpen)
	})

	closeFilterButton?.addEventListener('click', () => {
		closeFilter()
	})

	document.addEventListener('click', event => {

		if (isClickOutsideFilter(event.target)) {
			closeFilter()
		}
	})

	if (!searchInput || !wrapper) {
		return null
	}

	const setSearchActive = isActive => {
		wrapper.classList.toggle('is-search-active', isActive)

		if (categoriesFacet) {
			categoriesFacet.hidden = isActive
		}

		searchInput.classList.toggle('is-expanded', isActive)
		searchInput.style.width = isActive ? '100%' : ''
	}

	const isActiveByFocus = () => document.activeElement === searchInput

	setSearchActive(isActiveByFocus())

	searchInput.addEventListener('focus', () => {
		setSearchActive(true)
	})

	searchInput.addEventListener('click', () => {
		setSearchActive(true)
	})

	searchInput.addEventListener('blur', () => {
		window.setTimeout(() => {
			setSearchActive(isActiveByFocus())
		}, 0)
	})

	document.addEventListener('facetwp-loaded', () => {
		const currentCategoriesFacet = el.querySelector('.facetwp-facet-categories')
		if (currentCategoriesFacet) {
			currentCategoriesFacet.hidden = wrapper.classList.contains('is-search-active')
		}
		setSearchActive(isActiveByFocus())
	})

	return null
}
