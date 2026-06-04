import gsap from 'gsap'
import { ScrollTrigger } from 'gsap/ScrollTrigger'

gsap.registerPlugin(ScrollTrigger)

const SECTION_SELECTOR = '.contact-us'

const toArray = v => Array.from(v || [])

const prefersReduced = () => window.matchMedia?.('(prefers-reduced-motion: reduce)')?.matches ?? false

const initContactSection = (section) => {
    // if (prefersReduced()) {
    //     section.querySelectorAll('*').forEach(el => el.style.opacity = 1)
    //     return
    // }

    const title = section.querySelector('.contact-us__title')
    const form = section.querySelector('.wpcf7-form')
    const infoCol = section.querySelector('.contact-us__col--info')
    const metaItems = toArray(section.querySelectorAll('.contact-us__meta-item'))
    const socials = section.querySelector('.contact-us__socials')
    const light = section.querySelector('.image__light')

    gsap.set(section, { autoAlpha: 0 })
    if (title) gsap.set(title, { autoAlpha: 0, y: 36 })
    if (form) gsap.set(form, { autoAlpha: 0, y: 48 })
    if (infoCol) gsap.set(infoCol, { autoAlpha: 0, x: -24 })
    if (metaItems.length) gsap.set(metaItems, { autoAlpha: 0, x: -16 })
    if (socials) gsap.set(socials, { autoAlpha: 0, y: 20 })
    if (light) gsap.set(light, { autoAlpha: 0, y: -24 })

    const tl = gsap.timeline({
        defaults: { duration: 1.0, ease: 'power3.out' },
        scrollTrigger: {
            trigger: section,
            start: 'top 85%',
            end: 'bottom 60%',
            toggleActions: 'play none none reverse',
            markers: false,
        }
    })

    tl.to(section, { autoAlpha: 1 }, 0)
    if (title) tl.to(title, { autoAlpha: 1, y: 0 }, 0.1)
    if (infoCol) tl.to(infoCol, { autoAlpha: 1, x: 0 }, 0.3)
    if (metaItems.length) tl.to(metaItems, { autoAlpha: 1, x: 0, stagger: 0.12 }, 0.5)
    if (form) tl.to(form, { autoAlpha: 1, y: 0 }, 0.7)
    if (socials) tl.to(socials, { autoAlpha: 1, y: 0 }, 0.9)
    if (light) tl.to(light, { autoAlpha: 1, y: 0, duration: 1.4 }, 0.2)

    if (light) {
        gsap.to(light, { y: '+=8', repeat: -1, yoyo: true, ease: 'sine.inOut', duration: 8 })
    }
}

const initContactUsMotion = () => {
    const sections = toArray(document.querySelectorAll(SECTION_SELECTOR))
    if (!sections.length) return null

    sections.forEach(initContactSection)
    window.addEventListener('load', () => ScrollTrigger.refresh(), { once: true })

    return null
}

export default initContactUsMotion
