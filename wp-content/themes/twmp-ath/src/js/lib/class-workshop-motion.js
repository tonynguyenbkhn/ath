import gsap from 'gsap'
import ScrollTrigger from 'gsap/ScrollTrigger'

gsap.registerPlugin(ScrollTrigger)

const SECTION_SELECTOR = '.class-workshop-section'
const PREVIOUS_SECTION_SELECTOR = '.show-event'

const toArray = value => Array.from(value || [])

const hasReducedMotion = () =>
	window.matchMedia?.('(prefers-reduced-motion: reduce)')?.matches ?? false

const getPreviousSection = section => {
	const previous = section.previousElementSibling

	if (previous?.matches(PREVIOUS_SECTION_SELECTOR)) {
		return previous
	}

	return null
}

const getTargets = section => {
	// const control = section.querySelector('.class-section-control')
	const title = section.querySelector('.heading__title.class-section__title')
	const description = section.querySelector('.heading__description.class-section__description')
	const shape = section.querySelector('.section-shape')
	const actions = section.querySelector('.class-section__actions')
	const slides = toArray(section.querySelectorAll('.class-section__slide'))
	const activeSlide = slides.find(slide => slide.classList.contains('swiper-slide-active')) || slides[0]
	const orderedSlides = activeSlide
		? [activeSlide, ...slides.filter(slide => slide !== activeSlide)]
		: slides

	return {
		// control,
		title,
		description,
		shape,
		actions,
		slides: orderedSlides,
	}
}

const removeSnapshot = snapshot => {
	if (snapshot?.parentNode) {
		snapshot.parentNode.removeChild(snapshot)
	}
}

const createPreviousSectionSnapshot = previousSection => {
	const rect = previousSection.getBoundingClientRect()
	const snapshot = previousSection.cloneNode(true)

	snapshot.removeAttribute('id')
	snapshot.querySelectorAll('[id]').forEach(element => element.removeAttribute('id'))
	snapshot.setAttribute('aria-hidden', 'true')
	snapshot.classList.add('class-workshop-previous-snapshot')

	gsap.set(snapshot, {
		position: 'fixed',
		top: rect.top,
		left: rect.left,
		width: rect.width,
		height: rect.height,
		margin: 0,
		zIndex: 2,
		pointerEvents: 'none',
		overflow: 'hidden',
	})

	previousSection.parentNode.insertBefore(snapshot, previousSection.nextSibling)

	return snapshot
}

const initPreviousSnapshot = (section, previousSection) => {
	if (!previousSection) {
		return
	}

	let snapshot = null
	let previousVisibility = ''
	let previousIsHidden = false

	const hidePreviousSection = () => {
		if (previousIsHidden) {
			return
		}

		previousVisibility = previousSection.style.visibility
		previousSection.style.visibility = 'hidden'
		previousIsHidden = true
	}

	const showPreviousSection = () => {
		if (!previousIsHidden) {
			return
		}

		previousSection.style.visibility = previousVisibility
		previousIsHidden = false
	}

	const showSnapshot = () => {
		removeSnapshot(snapshot)
		snapshot = createPreviousSectionSnapshot(previousSection)
		hidePreviousSection()
	}
	const hideSnapshot = () => {
		showPreviousSection()
		removeSnapshot(snapshot)
		snapshot = null
	}

	ScrollTrigger.create({
		trigger: section,
		start: 'top bottom',
		end: 'top top',
		onEnter: showSnapshot,
		onEnterBack: showSnapshot,
		onLeave: hideSnapshot,
		onLeaveBack: hideSnapshot,
		onRefreshInit: hideSnapshot,
		invalidateOnRefresh: true,
	})
}

const initSectionMotion = section => {
	const previousSection = getPreviousSection(section)
	const { title, description, shape, actions, slides } = getTargets(section)
	const targets = [title, description, shape, actions, ...slides].filter(Boolean)

	if (!targets.length) {
		return
	}

	gsap.set(section, {
		position: 'relative',
		zIndex: 3,
		autoAlpha: 0,
		y: 120,
	})

	if (title) {
		gsap.set(title, {
			autoAlpha: 0,
			y: 72,
		})
	}

	if (description) {
		gsap.set(description, {
			autoAlpha: 0,
			y: 72,
		})
	}

	if (shape) {
		gsap.set(shape, {
			autoAlpha: 0,
			y: -96,
		})
	}

	if (actions) {
		gsap.set(actions, {
			autoAlpha: 0,
			y: 72,
		})
	}

	if (slides.length) {
		gsap.set(slides, {
			autoAlpha: 0,
			y: 72,
		})
	}

	initPreviousSnapshot(section, previousSection)

	const timeline = gsap.timeline({
		defaults: {
			duration: 0.75,
			ease: 'power3.out',
			overwrite: 'auto',
		},
		scrollTrigger: {
			trigger: section,
			start: 'top 82%',
			once: true,
			invalidateOnRefresh: true,
		},
	})

	timeline.to(section, {
		autoAlpha: 1,
		y: 0,
	}, 0)

	if (title) {
		timeline.to(title, {
			autoAlpha: 1,
			y: 0,
		}, 0.4)
	}

	if (shape) {
		timeline.to(shape, {
			autoAlpha: 1,
			y: 0,
		}, 0.4)
	}

	if (description) {
		timeline.to(description, {
			autoAlpha: 1,
			y: 0,
		}, 0.6)
	}

	if (actions) {
		timeline.to(actions, {
			autoAlpha: 1,
			y: 0,
		}, 0.6)
	}

	if (slides.length) {
		timeline.to(slides, {
			autoAlpha: 1,
			y: 0,
			stagger: 0.1,
		}, 0.6)
	}
}

const initClassWorkshopMotion = () => {
	const sections = toArray(document.querySelectorAll(SECTION_SELECTOR))

	if (!sections.length) {
		return null
	}

	if (hasReducedMotion()) {
		gsap.set(sections, {
			autoAlpha: 1,
			y: 0,
		})

		return null
	}

	sections.forEach(initSectionMotion)

	window.addEventListener('load', () => {
		ScrollTrigger.refresh()
	}, { once: true })

	return null
}

export default initClassWorkshopMotion
