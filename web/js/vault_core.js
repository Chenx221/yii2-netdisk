const vaultRawKey = sessionStorage.getItem('vaultRawKey');
async function generateEncryptionKeyFromPassword(password) {
    const passwordBuffer = new TextEncoder().encode(password);
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
            salt: new Uint8Array([]),
            iterations: 100000,
            hash: 'SHA-256'
        },
        key,
        {name: 'AES-GCM', length: 256},
        false,
        ['encrypt', 'decrypt']
    );
}
// 加密文件
async function encryptFile(file, password) {
    const iv = window.crypto.getRandomValues(new Uint8Array(12)); // 生成随机 IV
    const passwordBuffer = new TextEncoder().encode(password);
    const key = await window.crypto.subtle.importKey(
        'raw',
        passwordBuffer,
        {name: 'PBKDF2'},
        false,
        ['deriveKey']
    );
    const derivedKey = await window.crypto.subtle.deriveKey(
        {
            name: 'PBKDF2',
            salt: new Uint8Array([]),
            iterations: 100000,
            hash: 'SHA-256'
        },
        key,
        {name: 'AES-GCM', length: 256},
        false,
        ['encrypt', 'decrypt']
    );

    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onload = function(event) {
            const plaintextData = event.target.result;
            window.crypto.subtle.encrypt(
                { name: 'AES-GCM', iv: iv }, // 使用随机生成的 IV
                derivedKey,
                plaintextData
            ).then(encryptedData => {
                const encryptedBlob = new Blob([iv, encryptedData], {type: file.type});
                resolve(encryptedBlob);
            }).catch(error => {
                reject(error);
            });
        };
        reader.readAsArrayBuffer(file);
    });
}

// 解密文件
async function decryptFile(encryptedFile, password) {
    return new Promise((resolve, reject) => {
        const fileReader = new FileReader();
        fileReader.onload = async function(event) {
            try {
                const encryptedData = new Uint8Array(event.target.result);
                const iv = encryptedData.slice(0, 12); // 从密文中提取 IV
                const ciphertext = encryptedData.slice(12);
                const passwordBuffer = new TextEncoder().encode(password);
                const key = await window.crypto.subtle.importKey(
                    'raw',
                    passwordBuffer,
                    {name: 'PBKDF2'},
                    false,
                    ['deriveKey']
                );
                const derivedKey = await window.crypto.subtle.deriveKey(
                    {
                        name: 'PBKDF2',
                        salt: new Uint8Array([]),
                        iterations: 100000,
                        hash: 'SHA-256'
                    },
                    key,
                    {name: 'AES-GCM', length: 256},
                    false,
                    ['encrypt', 'decrypt']
                );
                const decryptedData = await window.crypto.subtle.decrypt(
                    { name: 'AES-GCM', iv: iv }, // 使用从密文中提取的 IV
                    derivedKey,
                    ciphertext
                );
                const decryptedFile = new Blob([decryptedData], { type: encryptedFile.type });
                resolve(decryptedFile);
            } catch (error) {
                reject(error);
            }
        };
        fileReader.readAsArrayBuffer(encryptedFile);
    });
}
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