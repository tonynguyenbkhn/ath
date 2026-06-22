import './scss/frontend.scss'
import 'swiper/css'
import 'swiper/css/navigation'
import 'swiper/css/pagination'
import 'swiper/css/grid'

import { Fancybox } from "@fancyapps/ui";
import "@fancyapps/ui/dist/fancybox/fancybox.css";

import init from 'lib/init-blocks'
import initCommon from 'lib/common'
import initAboutUsMotion from 'lib/about-us-motion'
import initHeroBannerMotion from 'lib/hero-banner-motion'
import initHeroProductStackMotion from 'lib/hero-product-stack-motion'
import initShowEventMotion from 'lib/show-event-motion'
import initClassWorkshopMotion from 'lib/class-workshop-motion'
import initForSchoolMotion from 'lib/for-school-motion'
import initForCompanyMotion from 'lib/for-company-motion'
import initContactUsMotion from 'lib/contact-us-motion'
import initFooterMotion from 'lib/footer-motion'
import initSingleProductGallerySticky from 'lib/single-product-gallery-sticky'

initHeroBannerMotion()
initAboutUsMotion()
initHeroProductStackMotion()
initShowEventMotion()
initClassWorkshopMotion()
initForSchoolMotion()
initForCompanyMotion()
initContactUsMotion()
initFooterMotion()

document.addEventListener('DOMContentLoaded', () => {
    Fancybox.bind("[data-fancybox]", {});
    initCommon()
    initSingleProductGallerySticky()

    init({
        block: 'blocks'
    }).mount()
})
