$(document).ready(function() {
    $('#copy-link-button').click(function() {
        var id = $('table.detail-view tbody tr:first-child td').text();
        var secret = $('table.detail-view tbody tr:nth-child(4) td').text();  // 获取访问密码
        var shareLink = window.location.origin + '/index.php?r=collection%2Faccess&id=' + id + '&secret=' + secret;  // 将访问密码添加到分享链接中
        navigator.clipboard.writeText(shareLink).then(function() {
            alert('分享链接已复制到剪贴板');
        }).catch(function(error) {
            console.error('复制失败: ', error);
        });
    });
});