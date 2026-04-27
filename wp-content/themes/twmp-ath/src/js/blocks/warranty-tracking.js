import { select, on, addClass, removeClass, getData } from 'lib/dom'

export default el => {
    const form = select('.form', el)
    const resultWrapper = select('.show-search-warranty-lookup', el)
    const phoneInput = select('.search-phone', form)
    const button = form.querySelector('button')

    const settings = getData('settings', el)
        ? JSON.parse(getData('settings', el))
        : {}

    const getRestUrl = () => {
        return `${twmpConfig.ajax.restUrl}/${settings.endpoint}`
    }

    on('submit', async (e) => {
        e.preventDefault()

        const phone = phoneInput.value.trim()
        if (!phone) return

        addClass('button-loading', button)

        try {
            const response = await fetch(getRestUrl(), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ phone })
            })

            const res = await response.json()
            removeClass('button-loading', button)

            if (res.success) {
                resultWrapper.innerHTML = res.html
                resultWrapper.style.display = 'block'
            } else {
                resultWrapper.innerHTML = `<p>${twmpConfig.message.notfound}</p>`
                resultWrapper.style.display = 'block'
            }
        } catch (error) {
            removeClass('button-loading', button)
            alert(twmpConfig.message.error)
        }
    }, form)
}