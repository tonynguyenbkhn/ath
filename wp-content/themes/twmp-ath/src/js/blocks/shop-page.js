export default el => {
	if (!el) {
		return null
	}

	const searchForm = el.querySelector('.woocommerce-product-search')
	const searchInput = searchForm?.querySelector('input[type="search"]')
	const categoriesFacet = el.querySelector('.facetwp-facet-categories')
	const wrapper = el.querySelector('.twmp-shop-layout__left-wrap') || el

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
