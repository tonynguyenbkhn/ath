const DESKTOP_QUERY = '(min-width: 1024px)'
const TOP_OFFSET = 24
const STOP_GAP = 24

const getAdminBarOffset = () => {
	const adminBar = document.getElementById('wpadminbar')

	return adminBar ? adminBar.offsetHeight : 0
}

const resetGallery = (gallery, placeholder) => {
	gallery.classList.remove('is-gallery-fixed', 'is-gallery-bottom')
	gallery.style.position = ''
	gallery.style.top = ''
	gallery.style.left = ''
	gallery.style.width = ''
	gallery.style.zIndex = ''

	if (placeholder) {
		placeholder.style.display = 'none'
		placeholder.style.height = ''
		placeholder.style.width = ''
	}
}

const setPlaceholderSize = (gallery, placeholder) => {
	if (!placeholder) {
		return
	}

	const rect = gallery.getBoundingClientRect()
	placeholder.style.display = 'block'
	placeholder.style.height = `${gallery.offsetHeight}px`
	placeholder.style.width = `${rect.width}px`
}

const initSingleProductGallerySticky = () => {
	const product = document.querySelector('.woocommerce.single-product .product__detail')
	const gallery = product?.querySelector('.woocommerce-product-gallery')
	const summary = product?.querySelector('.summary.entry-summary')
	const afterSummary = product?.querySelector('.woocommerce_after_single_product_summary')

	if (!product || !gallery || !summary || !afterSummary) {
		return
	}

	const mediaQuery = window.matchMedia(DESKTOP_QUERY)
	const placeholder = document.createElement('div')
	placeholder.className = 'single-product-gallery-placeholder'
	gallery.before(placeholder)

	let ticking = false

	const update = () => {
		ticking = false

		if (!mediaQuery.matches || summary.offsetHeight <= gallery.offsetHeight) {
			resetGallery(gallery, placeholder)
			return
		}

		const scrollY = window.scrollY || window.pageYOffset
		const topOffset = TOP_OFFSET + getAdminBarOffset()
		const productRect = product.getBoundingClientRect()
		const galleryRect = placeholder.style.display === 'block'
			? placeholder.getBoundingClientRect()
			: gallery.getBoundingClientRect()
		const afterSummaryTop = scrollY + afterSummary.getBoundingClientRect().top
		const galleryStart = scrollY + galleryRect.top
		const fixedBottom = scrollY + topOffset + gallery.offsetHeight
		const stopTop = afterSummaryTop - STOP_GAP

		if (scrollY + topOffset <= galleryStart) {
			resetGallery(gallery, placeholder)
			return
		}

		setPlaceholderSize(gallery, placeholder)

		if (fixedBottom >= stopTop) {
			gallery.classList.remove('is-gallery-fixed')
			gallery.classList.add('is-gallery-bottom')
			gallery.style.position = 'absolute'
			gallery.style.top = `${stopTop - (scrollY + productRect.top) - gallery.offsetHeight}px`
			gallery.style.left = '0'
			gallery.style.width = `${galleryRect.width}px`
			gallery.style.zIndex = '2'
			return
		}

		gallery.classList.remove('is-gallery-bottom')
		gallery.classList.add('is-gallery-fixed')
		gallery.style.position = 'fixed'
		gallery.style.top = `${topOffset}px`
		gallery.style.left = `${galleryRect.left}px`
		gallery.style.width = `${galleryRect.width}px`
		gallery.style.zIndex = '20'
	}

	const requestUpdate = () => {
		if (ticking) {
			return
		}

		ticking = true
		window.requestAnimationFrame(update)
	}

	window.addEventListener('scroll', requestUpdate, { passive: true })
	window.addEventListener('resize', requestUpdate)
	window.addEventListener('load', requestUpdate)

	if (typeof mediaQuery.addEventListener === 'function') {
		mediaQuery.addEventListener('change', requestUpdate)
	} else if (typeof mediaQuery.addListener === 'function') {
		mediaQuery.addListener(requestUpdate)
	}

	requestUpdate()
}

export default initSingleProductGallerySticky
