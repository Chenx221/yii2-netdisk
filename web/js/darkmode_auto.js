window.onload = function() {
    // 检查设备是否开启了夜间模式
    if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
        // 如果开启了夜间模式，设置 data-bs-theme 属性为 'dark'
        document.documentElement.setAttribute('data-bs-theme', 'dark');

        // 找到所有的outline按钮
        var buttons = document.querySelectorAll('.btn-outline-primary, .btn-outline-secondary, .btn-outline-success, .btn-outline-danger, .btn-outline-warning, .btn-outline-info, .btn-outline-light, .btn-outline-dark');

        // 遍历所有的outline按钮
        for (var i = 0; i < buttons.length; i++) {
            // 替换 'btn-outline-*' 类为 'btn-*' 类
            buttons[i].className = buttons[i].className.replace('btn-outline-', 'btn-');
        }
    }
};