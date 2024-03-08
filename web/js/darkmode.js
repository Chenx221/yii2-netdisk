window.onload = function () {
    // 找到所有的outline按钮
    var buttons = document.querySelectorAll('.btn-outline-primary, .btn-outline-secondary, .btn-outline-success, .btn-outline-danger, .btn-outline-warning, .btn-outline-info, .btn-outline-light, .btn-outline-dark');

    // 遍历所有的outline按钮
    for (var i = 0; i < buttons.length; i++) {
        // 替换 'btn-outline-*' 类为 'btn-*' 类
        buttons[i].className = buttons[i].className.replace('btn-outline-', 'btn-');
    }
};