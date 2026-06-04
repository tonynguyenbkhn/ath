import gsap from 'gsap'
import ScrollTrigger from 'gsap/ScrollTrigger'

gsap.registerPlugin(ScrollTrigger)

function prefersReducedMotion() {
	return typeof window !== 'undefined' && window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches
}

function safeArray(selector) {
	try { return gsap.utils.toArray(selector) } catch (e) { return [] }
}

function initHero() {
	const hero = document.querySelector('.hero-banner')
	if (!hero) return

	if (prefersReducedMotion()) {
		// ensure elements are visible
		hero.querySelectorAll('*').forEach(el => el.style.opacity = 1)
		return
	}

	const items = hero.querySelectorAll('.hero-banner__left > *, .hero-banner__right > *, .hero-cta')
	if (!items.length) return

	const tl = gsap.timeline()
	tl.from(items, {
		y: 20,
		opacity: 0,
		duration: 0.6,
		stagger: 0.12,
		ease: 'power3.out'
	})
}

function initProductGrid() {
	const items = safeArray('.product-cat-grid__item')
	if (!items.length) return
	if (prefersReducedMotion()) {
		items.forEach(i => i.style.opacity = 1)
		return
	}

	gsap.from(items, {
		y: 18,
		opacity: 0,
		scale: 0.98,
		stagger: 0.12,
		duration: 0.6,
		ease: 'power2.out',
		scrollTrigger: {
			trigger: '.product-cat-grid',
			start: 'top 80%'
		}
	})
}

function initShapes() {
	const shapes = safeArray('.section-shape--square, .section-shape--triangle')
	if (!shapes.length) return
	if (prefersReducedMotion()) {
		shapes.forEach(s => s.style.opacity = 1)
		return
	}

	shapes.forEach((s, i) => {
		const dur = 6 + (i % 3)
		gsap.to(s, {
			y: '+=8',
			rotation: (i % 2 ? 2 : -2),
			repeat: -1,
			yoyo: true,
			ease: 'sine.inOut',
			duration: dur,
			force3D: true
		})
	})
}

function initAboutUs() {
	const section = document.querySelector('.about-us')
	if (!section) return

	const media = section.querySelector('.about-us__media')
	const content = section.querySelector('.about-us__content')
	const stats = section.querySelectorAll('.about-us__stats .stat-number')

	if (prefersReducedMotion()) {
		if (media) media.style.opacity = 1
		if (content) content.style.opacity = 1
		stats.forEach(s => s.style.opacity = 1)
		return
	}

	const tl = gsap.timeline({
		scrollTrigger: { trigger: section, start: 'top 85%' }
	})

	if (media) tl.from(media, { x: -30, opacity: 0, duration: 0.7, ease: 'power3.out' })
	if (content) tl.from(content, { x: 30, opacity: 0, duration: 0.7, ease: 'power3.out' }, '<')

	if (stats && stats.length) {
		stats.forEach(el => {
			const target = parseInt(el.textContent.replace(/[^0-9]/g, ''), 10) || 0
			if (!target) return
			const obj = { val: 0 }
			gsap.to(obj, {
				val: target,
				duration: 1.2,
				ease: 'power2.out',
				onUpdate() { el.textContent = Math.round(obj.val) }
			})
		})
	}
}

function initShowEvent() {
	const section = document.querySelector('.show-event')
	if (!section) return
	if (prefersReducedMotion()) {
		section.querySelectorAll('*').forEach(el => el.style.opacity = 1)
		return
	}

	const cards = safeArray('.show-event .event-card, .show-event .swiper-slide')
	if (!cards.length) return

	gsap.from(cards, {
		y: 18,
		opacity: 0,
		duration: 0.6,
		stagger: 0.12,
		ease: 'power2.out',
		scrollTrigger: { trigger: section, start: 'top 85%' }
	})
}

function initClassWorkshop() {
	const section = document.querySelector('.class-workshop-section')
	if (!section) return
	if (prefersReducedMotion()) {
		section.querySelectorAll('*').forEach(el => el.style.opacity = 1)
		return
	}

	const cards = safeArray('.class-workshop-section .workshop-card')
	if (cards.length) {
		gsap.from(cards, {
			y: 18,
			opacity: 0,
			duration: 0.6,
			stagger: 0.12,
			ease: 'power2.out',
			scrollTrigger: { trigger: section, start: 'top 85%' }
		})
	}

	// subtle parallax for decorative elements
	const deco = safeArray('.class-workshop-section .parallax-x')
	deco.forEach(el => {
		ScrollTrigger.create({
			trigger: section,
			start: 'top bottom',
			end: 'bottom top',
			scrub: 0.6,
			onUpdate(self) {
				const pct = self.progress - 0.5
				gsap.to(el, { x: pct * 30, overwrite: true, ease: 'none' })
			}
		})
	})
}

export default function initHomeAnimation() {
	if (typeof window === 'undefined') return
	try {
		initHero()
		initProductGrid()
		initShapes()
		initAboutUs()
		initShowEvent()
		initClassWorkshop()
	} catch (e) {
		// fail silently — animations are progressive enhancement
		// console.error('home-animation error', e)
	}
}

