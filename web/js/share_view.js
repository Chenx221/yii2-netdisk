$(document).ready(function() {
    $('#copy-link-button').click(function() {
        var shareId = $('table.detail-view tbody tr:first-child td').text();
        var shareLink = window.location.origin + '/index.php?r=share%2Faccess&share_id=' + shareId;
        navigator.clipboard.writeText(shareLink).then(function() {
            alert('分享链接已复制到剪贴板');
        }).catch(function(error) {
            console.error('复制失败: ', error);
        });
    });
});