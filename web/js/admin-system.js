$(document).ready(function () {
    updateTableRowVisibility();
    $('#siteconfig-verifyprovider').change(function () {
        updateTableRowVisibility();
    });
    $('#siteconfig-enableipinfo').change(function () {
        updateTableRowVisibility();
    });
});

function updateTableRowVisibility() {
    let currentValue = $('#siteconfig-verifyprovider').val();
    let ipinfoEnable = $('#siteconfig-enableipinfo').prop('checked');
    $('#tr-recaptchaSiteKey, #tr-recaptchaSecret, #tr-hcaptchaSiteKey, #tr-hcaptchaSecret, #tr-turnstileSiteKey, #tr-turnstileSecret,#tr-ipinfoToken').hide();
    if (currentValue === 'reCAPTCHA') {
        $('#tr-recaptchaSiteKey, #tr-recaptchaSecret').show();
    } else if (currentValue === 'hCaptcha') {
        $('#tr-hcaptchaSiteKey, #tr-hcaptchaSecret').show();
    } else if (currentValue === 'Turnstile') {
        $('#tr-turnstileSiteKey, #tr-turnstileSecret').show();
    }
    if (ipinfoEnable) {
        $('#tr-ipinfoToken').show();
    }
}