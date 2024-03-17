$.pjax.defaults.scrollTo = false;
$(document).ready(function () {
    $('#deleteConfirm').change(function () {
        if (this.checked) {
            $('#deleteButton').prop('disabled', false);
        } else {
            $('#deleteButton').prop('disabled', true);
        }
    });
    $('#totp-enabled').change(function () {
        if (this.checked) {
            $('#totpSetupModal').modal('show');
        } else {
            $.post('index.php?r=user%2Fremove-two-factor', function () {
                location.reload();
            });
        }
    });
    $('#totpSetupModal').on('hidden.bs.modal', function () {
        $('#totp-enabled').prop('checked', false);
    });
    $('#useDarkTheme').change(function () {
        var darkMode = this.checked ? 1 : 0;
        $.post('index.php?r=user%2Fset-theme', {dark_mode: darkMode}, function () {
            location.reload();
        });
    });

    $('#followSystemTheme').change(function () {
        $('#useDarkTheme').prop('checked', false);
        var darkMode = this.checked ? 2 : 0;
        $.post('index.php?r=user%2Fset-theme', {dark_mode: darkMode}, function () {
            location.reload();
        });
    });
});

document.querySelector('.avatar-container').addEventListener('click', function () {
    $('#avatarModal').modal('show');
});

document.querySelector('.editable-username').addEventListener('click', function () {
    // 在这里添加你的代码来显示一个模态框或其他你想要的东西
    $('#changeAccountName').modal('show');
});

document.querySelector('#webauthn_detail').addEventListener('click', function () {
    $.ajax({
        url: 'index.php?r=user%2Fcredential-list',
        method: 'GET',
        success: function(data) {
            $('#pjax-container').html(data);
        },
        complete: function() {
            $('#credentialModal').modal('show');
        }
    });
});

// WebAuthn registration #BEGIN
const { startRegistration } = SimpleWebAuthnBrowser;

// <button>
const elemBegin = document.getElementById('webauthn_add');
// <span>/<p>/etc...
const elemSuccess = document.getElementById('webauthn_success');
// <span>/<p>/etc...
const elemError = document.getElementById('webauthn_error');

// Start registration when the user clicks a button
elemBegin.addEventListener('click', async () => {
    // Reset success/error messages
    elemSuccess.innerHTML = '';
    elemError.innerHTML = '';
    elemSuccess.parentElement.hidden = true;
    elemError.parentElement.hidden = true;

    // GET registration options from the endpoint that calls
    const resp = await fetch('index.php?r=user%2Fcreate-credential-options');

    let attResp;
    try {
        // Pass the options to the authenticator and wait for a response
        attResp = await startRegistration(await resp.json());
    } catch (error) {
        // Some basic error handling
        if (error.name === 'InvalidStateError') {
            elemError.innerText = 'Error: Authenticator was probably already registered by user';
        } else {
            elemError.innerText = error;
        }
        elemError.parentElement.hidden = false;
        throw error;
    }
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    attResp.fido_name = document.getElementById('fido_name').value;
    // POST the response to the endpoint that calls
    const verificationResp = await fetch('index.php?r=user%2Fcreate-credential', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': csrfToken
        },
        body: JSON.stringify(attResp),
    });

    // Wait for the results of verification
    const verificationJSON = await verificationResp.json();

    // Show UI appropriate for the `verified` status
    if (verificationJSON && verificationJSON.verified) {
        elemSuccess.innerHTML = 'Success!';
        elemSuccess.parentElement.hidden = false;
    } else {
        elemError.innerHTML = `Oh no, something went wrong! Response: <pre>${JSON.stringify(
            verificationJSON,
        )}</pre>`;
        elemError.parentElement.hidden = false;
    }
});

const { startAuthentication } = SimpleWebAuthnBrowser;

// <button>
const elemBegin_v = document.getElementById('webauthn_verify');

// Start authentication when the user clicks a button
elemBegin_v.addEventListener('click', async () => {
    // Reset success/error messages
    elemSuccess.innerHTML = '';
    elemError.innerHTML = '';
    elemSuccess.parentElement.hidden = true;
    elemError.parentElement.hidden = true;

    // GET authentication options from the endpoint that calls
    // @simplewebauthn/server -> generateAuthenticationOptions()
    const resp = await fetch('index.php?r=user%2Frequest-assertion-options');

    let asseResp;
    try {
        // Pass the options to the authenticator and wait for a response
        asseResp = await startAuthentication(await resp.json());
    } catch (error) {
        // Some basic error handling
        elemError.innerText = error;
        elemError.parentElement.hidden = false;
        throw error;
    }
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    // POST the response to the endpoint that calls
    // @simplewebauthn/server -> verifyAuthenticationResponse()
    const verificationResp = await fetch('index.php?r=user%2Fverify-assertion', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': csrfToken
        },
        body: JSON.stringify(asseResp),
    });

    // Wait for the results of verification
    const verificationJSON = await verificationResp.json();

    // Show UI appropriate for the `verified` status
    if (verificationJSON && verificationJSON.verified) {
        elemSuccess.innerHTML = 'Success!';
        elemSuccess.parentElement.hidden = false;

    } else {
        elemError.innerHTML = `Oh no, something went wrong! Response: <pre>${JSON.stringify(
            verificationJSON,
        )}</pre>`;
        elemError.parentElement.hidden = false;
    }
});