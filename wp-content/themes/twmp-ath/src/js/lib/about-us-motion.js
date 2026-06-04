import gsap from 'gsap'
import ScrollTrigger from 'gsap/ScrollTrigger'

gsap.registerPlugin(ScrollTrigger)

const ABOUT_SELECTOR = '.about-us'
const INTRO_OFFSET = 200
const DETAIL_OFFSET = 200

const toArray = value => Array.from(value || [])

const hasReducedMotion = () =>
	window.matchMedia?.('(prefers-reduced-motion: reduce)')?.matches ?? false

const getSectionTargets = section => {
	const media = section.querySelector('.about-us__media')
	const headers = toArray(section.querySelectorAll('.about-us__header')).filter(
		header => !media || !media.contains(header)
	)
	const introTargets = [...headers, media].filter(Boolean)
	const stats = section.querySelector('.about-us__stats')
	const actions = section.querySelector('.about-us__actions')
	const detailTargets = [stats, actions].filter(Boolean)

	return {
		introTargets,
		detailTargets,
		stats,
	}
}

const initSectionMotion = section => {
	const { introTargets, detailTargets, stats } = getSectionTargets(section)

	if (!introTargets.length && !detailTargets.length) {
		return
	}

	gsap.set(section, {
		zIndex: 3,
	})

	if (introTargets.length) {
		gsap.set(introTargets, {
			autoAlpha: 0,
			y: INTRO_OFFSET,
		})
	}

	if (detailTargets.length) {
		gsap.set(detailTargets, {
			autoAlpha: 0,
			y: DETAIL_OFFSET,
		})
	}

	const introTimeline = gsap.timeline({
		paused: true,
		defaults: {
			duration: 1.5,
			ease: 'power3.out',
			overwrite: 'auto',
		},
	})

	introTimeline.to(introTargets, {
		autoAlpha: 1,
		y: 0,
		stagger: 0.2,
	})

	ScrollTrigger.create({
		trigger: section,
		start: 'top 75%',
		once: true,
		onEnter: () => {
			introTimeline.play(0)
		},
	})

	if (!detailTargets.length) {
		return
	}

	const detailTimeline = gsap.timeline({
		paused: true,
		defaults: {
			duration: 1.5,
			ease: 'power3.out',
			overwrite: 'auto',
		},
	})

	detailTimeline.to(detailTargets, {
		autoAlpha: 1,
		y: 0,
		stagger: 0.2,
	})

	ScrollTrigger.create({
		trigger: stats || section,
		start: 'top 82%',
		once: true,
		onEnter: () => {
			detailTimeline.play(0)
		},
	})
}

const initAboutUsMotion = () => {
	const sections = toArray(document.querySelectorAll(ABOUT_SELECTOR))

	if (!sections.length) {
		return null
	}

	// if (hasReducedMotion()) {
	// 	sections.forEach(section => {
	// 		const { introTargets, detailTargets } = getSectionTargets(section)

	// 		gsap.set(section, {
	// 			clearProps: 'zIndex',
	// 		})

	// 		gsap.set([...introTargets, ...detailTargets], {
	// 			clearProps: 'all',
	// 		})
	// 	})

	// 	return null
	// }

	sections.forEach(initSectionMotion)

	window.addEventListener('load', () => {
		ScrollTrigger.refresh()
	}, { once: true })

	return null
}

export default initAboutUsMotion
