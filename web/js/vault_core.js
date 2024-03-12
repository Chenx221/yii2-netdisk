const vaultRawKey = sessionStorage.getItem('vaultRawKey');

async function getSaltFromBackend() {
    const response = await fetch('index.php?r=vault%2Fget-salt');
    const data = await response.json();
    return data.vault_salt;
}

async function deriveKey(password) {
    const passwordBuffer = new TextEncoder().encode(password);
    const salt = await getSaltFromBackend();
    const saltBuffer = new TextEncoder().encode(salt);
    const key = await window.crypto.subtle.importKey(
        'raw',
        passwordBuffer,
        {name: 'PBKDF2'},
        false,
        ['deriveKey']
    );
    return await window.crypto.subtle.deriveKey(
        {
            name: 'PBKDF2',
            salt: saltBuffer,
            iterations: 100000,
            hash: 'SHA-256'
        },
        key,
        {name: 'AES-GCM', length: 256},
        false, // 是否允许导出
        ['encrypt', 'decrypt']
    );
}

async function encryptFile(file, password) {
    const iv = window.crypto.getRandomValues(new Uint8Array(12));
    const derivedKey = await deriveKey(password);
    // console.log(password);
    const plaintextData = await file.arrayBuffer();
    const encryptedData = await window.crypto.subtle.encrypt(
        {name: 'AES-GCM', iv: iv, tagLength: 128},
        derivedKey,
        plaintextData
    );
    return new Blob([iv, encryptedData], {type: file.type});
}

async function decryptFile(encryptedFile, password) {
    const encryptedData = new Uint8Array(await encryptedFile.arrayBuffer());
    const iv = encryptedData.slice(0, 12);
    const ciphertext = encryptedData.slice(12);
    const derivedKey = await deriveKey(password);
    const decryptedData = await window.crypto.subtle.decrypt(
        {name: 'AES-GCM', iv: iv, tagLength: 128},
        derivedKey,
        ciphertext
    );
    return new Blob([decryptedData], {type: encryptedFile.type});
}
// async function decryptFile(encryptedFile, password) {
//     const encryptedData = new Uint8Array(await encryptedFile.arrayBuffer());
//     const iv = encryptedData.slice(0, 12);
//     const ciphertext = encryptedData.slice(12);
//     const derivedKey = await deriveKey(password);
//     const keyData = await window.crypto.subtle.exportKey('raw', derivedKey);
//     const keyBytes = new Uint8Array(keyData);
//     // console.log('Key:', keyBytes);
//     // console.log(password);
//     const decryptedData = await window.crypto.subtle.decrypt(
//         {name: 'AES-GCM', iv: iv, tagLength: 128},
//         derivedKey,
//         ciphertext
//     );
//     return new Blob([decryptedData], {type: encryptedFile.type});
// }
async function downloadAndDecryptFile(url, password, filename) {
    const response = await fetch(url);
    const encryptedFile = await response.blob();
    const decryptedFile = await decryptFile(encryptedFile, password);
    const blob = new Blob([decryptedFile], {type: decryptedFile.type});
    const blobURL = window.URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = blobURL;
    link.download = filename;
    link.click();
    window.URL.revokeObjectURL(blobURL);
}