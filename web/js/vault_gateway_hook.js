document.getElementById('gateway-vault-form').addEventListener('submit', function (event) {
    event.preventDefault();
    var password = document.getElementById('password').value;
    sessionStorage.setItem('vaultRawKey', password);
    this.submit();
});
document.addEventListener('DOMContentLoaded', function () {
    if (!(window.crypto && window.crypto.subtle)) {
        console.log('浏览器不支持 Crypto API');
        //顺带一提，简单测试了下，那些不支持crypto api的浏览器，可能前面登录那关都过不去（验证码）
        alert('您的浏览器不支持加密功能，故无法使用文件保险箱功能，请使用现代浏览器。');
        window.location.href = 'index.php?r=site%2Findex';
    }
});
