import { select, on, addClass, removeClass, getData } from 'lib/dom'

export default el => {
    const form = select('.form', el)
    const resultWrapper = select('.show-search-order-lookup', el)
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

        console.log('123');
        

        const phone = phoneInput.value.trim()
        if (!phone) return

        addClass('button-loading', button) // Hiện hiệu ứng loading

        try {
            const response = await fetch(getRestUrl(), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    phone,
                    _wpnonce: twmpConfig.nonce
                })
            })

            const res = await response.json()
            removeClass('button-loading', button) // Tắt loading

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