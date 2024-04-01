$(document).ready(function () {
    updateTableRowVisibility();
    $('#siteconfig-verifyprovider').change(function () {
        updateTableRowVisibility();
    });
    $('#siteconfig-enableipinfo').change(function () {
        updateTableRowVisibility();
    });
    $('#siteconfig-clarityenabled').change(function () {
        updateTableRowVisibility();
    });
    $('#siteconfig-gaenabled').change(function () {
        updateTableRowVisibility();
    });
});

function updateTableRowVisibility() {
    let currentValue = $('#siteconfig-verifyprovider').val();
    let ipinfoEnable = $('#siteconfig-enableipinfo').prop('checked');
    let clarityEnable = $('#siteconfig-clarityenabled').prop('checked');
    let gaEnable = $('#siteconfig-gaenabled').prop('checked');
    $('#tr-recaptchaSiteKey, #tr-recaptchaSecret, #tr-hcaptchaSiteKey, #tr-hcaptchaSecret, #tr-turnstileSiteKey, #tr-turnstileSecret,#tr-ipinfoToken,#tr-clarityId,#tr-gaId').hide();
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
    if (clarityEnable) {
        $('#tr-clarityId').show();
    }
    if (gaEnable) {
        $('#tr-gaId').show();
    }
}