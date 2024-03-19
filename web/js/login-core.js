const elemSuccess = document.getElementById('webauthn_success');
const elemError = document.getElementById('webauthn_error');
const { startAuthentication } = SimpleWebAuthnBrowser;
const elemBegin_v = document.getElementById('webauthn_verify');
const username_input = document.getElementById('username');
const remember = document.getElementById('rememberMe');

elemBegin_v.addEventListener('click', async () => {
    elemSuccess.innerHTML = '';
    elemError.innerHTML = '';
    elemSuccess.parentElement.hidden = true;
    elemError.parentElement.hidden = true;
    const username = encodeURIComponent(username_input.value);
    const resp = await fetch(`index.php?r=user%2Frequest-assertion-options&username=${username}`);
    let asseResp;
    try {
        asseResp = await startAuthentication(await resp.json());
    } catch (error) {
        elemError.innerText = error;
        elemError.parentElement.hidden = false;
        throw error;
    }
    const isChecked = remember.checked? 1 : 0;
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const verificationResp = await fetch(`index.php?r=user%2Fverify-assertion&is_login=1&remember=${isChecked}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': csrfToken
        },
        body: JSON.stringify(asseResp),
    });
    const verificationJSON = await verificationResp.json();
    if (verificationJSON && verificationJSON.verified) {
        elemSuccess.innerHTML = '登录成功！1s后跳转到首页';
        elemSuccess.parentElement.hidden = false;
        setTimeout(() => {
            window.location.href = 'index.php';
        }, 1000);

    } else {
        elemError.innerHTML = `Oh no, something went wrong! Response: <pre>${JSON.stringify(
            verificationJSON,
        )}</pre>`;
        elemError.parentElement.hidden = false;
    }
});