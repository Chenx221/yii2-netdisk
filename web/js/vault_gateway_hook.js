document.getElementById('gateway-vault-form').addEventListener('submit', function (event) {
    event.preventDefault();
    var password = document.getElementById('password').value;
    sessionStorage.setItem('vaultRawKey', password);
    this.submit();
});
document.addEventListener('DOMContentLoaded', function () {
    if (!(window.crypto && window.crypto.subtle)) {
        console.log('浏览器不支持 Crypto API');
        alert('您的浏览器不支持加密功能，故无法使用文件保险箱功能，请使用现代浏览器。');
        window.location.href = 'index.php?r=site%2Findex';
    }
});

// async function generateEncryptionKeyFromPassword(password) {
//     const passwordBuffer = new TextEncoder().encode(password);
//     const key = await window.crypto.subtle.importKey(
//         'raw',
//         passwordBuffer,
//         {name: 'PBKDF2'},
//         false,
//         ['deriveKey']
//     );
//     const encryptionKey = await window.crypto.subtle.deriveKey(
//         {
//             name: 'PBKDF2',
//             salt: new Uint8Array([]),
//             iterations: 100000,
//             hash: 'SHA-256'
//         },
//         key,
//         {name: 'AES-GCM', length: 256},
//         false,
//         ['encrypt', 'decrypt']
//     );
//
//     return encryptionKey;
// }
//
// function cryptoKeyToString(cryptoKey) {
//     return window.crypto.subtle.exportKey('raw', cryptoKey).then(function (keyData) {
//         return String.fromCharCode.apply(null, new Uint8Array(keyData));
//     });
// }
//
// function stringToCryptoKey(keyString) {
//     // 将字符串转换为 Uint8Array
//     var keyData = new Uint8Array(keyString.length);
//     for (var i = 0; i < keyString.length; ++i) {
//         keyData[i] = keyString.charCodeAt(i);
//     }
//
//     // 使用 importKey 方法导入 CryptoKey 对象
//     return window.crypto.subtle.importKey(
//         'raw',
//         keyData,
//         {name: 'PBKDF2'},
//         false,
//         ['deriveKey']
//     );
// }