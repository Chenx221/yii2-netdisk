$(document).on('click', '.rename-btn', function () {
    var relativePath = $(this).attr('value');
    var fileName = $(this).closest('tr').find('td:first').text().trim();
    $('#renameRelativePath').val(relativePath);
    $('#renameform-newname').val(fileName);
    $('#renameModal').modal('show');
})
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl)
});
$(document).on('click', '.download-btn', function () {
    window.location.href = $(this).attr('value');
});
$(document).on('click', '.folder-download-btn', function () {
    var relativePath = $(this).attr('value');
    window.open('index.php?r=home%2Fdownload-folder&relativePath=' + encodeURIComponent(relativePath), '_blank');
});
$(document).on('click', '.delete-btn', function () {
    var relativePath = $(this).attr('value');
    $('#deleteRelativePath').val(relativePath);
    $('#deleteModal').modal('show');
});
$(document).on('click', '.file-upload-btn', function () {
    $('#file-input').click();
});
$('#file-input').on('change', function () {
    uploadFiles(this.files);
});

$(document).on('click', '.folder-upload-btn', function () {
    $('#folder-input').click();
});

$('#folder-input').on('change', function () {
    uploadFiles(this.files);
});

$(document).on('click', '.offline-download-btn', function () {
    console.log('离线下载功能尚未实现');
    //TO DO
});

$(document).on('click', '.refresh-btn', function () {
    window.location.reload();
});

$(document).on('click', '.new-folder-btn', function () {
    var relativePath = $(this).attr('value');
    $('#newDirRelativePath').val(relativePath);
    $('#newFolderModal').modal('show');
})

$(document).on('click', '.single-download-btn', function () {
    var relativePath = $('.select-item:checked').first().data('relativePath');
    window.location.href = 'index.php?r=home%2Fdownload&relativePath=' + encodeURIComponent(relativePath);
});

$(document).on('click', '.batch-zip-download-btn', function () {
    var relativePaths = $('.select-item:checked').map(function () {
        return $(this).data('relativePath');
    }).get();

    // 创建一个新的表单
    var form = $('<form>', {
        action: 'index.php?r=home%2Fmulti-ff-zip-dl',
        method: 'post'
    });

    // 将相对路径添加到表单中
    $.each(relativePaths, function(index, value) {
        form.append($('<input>', {
            type: 'hidden',
            name: 'relativePaths[]',
            value: value
        }));
    });

    // 添加 CSRF 令牌
    form.append($('<input>', {
        type: 'hidden',
        name: '_csrf',
        value: $('meta[name="csrf-token"]').attr('content')
    }));

    // 将表单添加到页面中并提交
    form.appendTo('body').submit();
});

$(document).on('click', '.batch-zip-btn', function () {
    console.log('打包按钮被点击');
    // 在这里添加你的代码
});

$(document).on('click', '.unzip-btn', function () {
    console.log('解压按钮被点击');
    // 在这里添加你的代码
});

$(document).on('click', '.single-rename-btn', function () {
    console.log('重命名按钮被点击');
    // 在这里添加你的代码
});

$(document).on('click', '.batch-copy-btn', function () {
    console.log('复制按钮被点击');
    // 在这里添加你的代码
});

$(document).on('click', '.batch-cut-btn', function () {
    console.log('剪切按钮被点击');
    // 在这里添加你的代码
});

$(document).on('click', '.batch-paste-btn', function () {
    console.log('粘贴按钮被点击');
    // 在这里添加你的代码
});

$(document).on('click', '.calc-sum-btn', function () {
    console.log('计算文件校验值按钮被点击');
    // 在这里添加你的代码
});

$(document).on('click', '.single-share-btn', function () {
    console.log('分享按钮被点击');
    // 在这里添加你的代码
});

$(document).on('click', '.batch-delete-btn', function () {
    console.log('删除按钮被点击');
    // 在这里添加你的代码
});

//下面的代码实现了各种按钮/样式功能，建议别看了(

//上传
function uploadFiles(files) {
    $('#progress-bar').show();
    var formData = new FormData();
    for (var i = 0; i < files.length; i++) {
        formData.append('files[]', files[i]);
    }
    formData.append('targetDir', $('#target-dir').val());
    formData.append('_csrf', $('meta[name="csrf-token"]').attr('content'));

    var xhr = new XMLHttpRequest();
    xhr.upload.onprogress = function (event) {
        if (event.lengthComputable) {
            var percentComplete = event.loaded / event.total * 100;
            $('#progress-bar .progress-bar').css('width', percentComplete + '%').text(Math.round(percentComplete) + '%');
        }
    };
    xhr.onload = function () {
        if (xhr.status !== 200) {
            alert('An error occurred during the upload.');
        }
        window.location.reload();
    };
    xhr.open('POST', 'index.php?r=home%2Fupload');
    xhr.send(formData);
}

//拖拽上传
var dropArea = document.getElementById('drop-area');
dropArea.addEventListener('dragover', function (event) {
    event.preventDefault();
});
dropArea.addEventListener('drop', function (event) {
    event.preventDefault();

    var items = event.dataTransfer.items;
    var files = [];

    for (var i = 0; i < items.length; i++) {
        var item = items[i];
        files.push(item.getAsFile());
    }

    uploadFiles(files);
    dropArea.classList.remove('dragging');
});
dropArea.addEventListener('dragenter', function (event) {
    event.preventDefault();

    dropArea.classList.add('dragging');
});

dropArea.addEventListener('dragleave', function (event) {
    event.preventDefault();

    if (!dropArea.contains(event.relatedTarget)) {
        dropArea.classList.remove('dragging');
    }
});

// 为全选/取消全选的复选框添加事件监听器
document.getElementById('select-all').addEventListener('change', function () {
    // 获取所有的复选框
    var checkboxes = document.querySelectorAll('.select-item');
    // 设置所有复选框的状态与全选/取消全选的复选框的状态相同
    for (var i = 0; i < checkboxes.length; i++) {
        checkboxes[i].checked = this.checked;
        checkboxes[i].closest('tr').classList.toggle('selected', this.checked);
    }
});

// 为每一行的复选框添加事件监听器
var itemCheckboxes = document.querySelectorAll('.select-item');
for (var i = 0; i < itemCheckboxes.length; i++) {
    itemCheckboxes[i].addEventListener('change', function () {
        // 如果有一个复选框未被选中，则全选/取消全选的复选框也应该未被选中
        if (!this.checked) {
            document.getElementById('select-all').checked = false;
        }
        // 如果所有的复选框都被选中，则全选/取消全选的复选框也应该被选中
        else {
            var allChecked = true;
            for (var j = 0; j < itemCheckboxes.length; j++) {
                if (!itemCheckboxes[j].checked) {
                    allChecked = false;
                    break;
                }
            }
            document.getElementById('select-all').checked = allChecked;
        }
        this.closest('tr').classList.toggle('selected', this.checked);
    });
}

// 为document添加键盘事件监听器
document.addEventListener('keydown', function (event) {
    // 如果用户按下了Ctrl+A
    if (event.ctrlKey && event.key === 'a') {
        // 阻止默认的全选操作
        event.preventDefault();
        // 获取所有的复选框
        var checkboxes = document.querySelectorAll('.select-item');
        var selectAll = document.getElementById('select-all');
        selectAll.checked = !selectAll.checked;
        for (var i = 0; i < checkboxes.length; i++) {
            checkboxes[i].checked = selectAll.checked;
            checkboxes[i].closest('tr').classList.toggle('selected', selectAll.checked);
        }
        updateButtons();
    }
});

//行点击事件
$(document).on('click', 'tr', function (event) {
    // 如果点击的是checkbox，就不执行下面的代码
    if ($(event.target).is('input[type="checkbox"]')) {
        return;
    }

    $(this).toggleClass('selected');
    var checkbox = $(this).children(':first-child').find('input[type="checkbox"]');
    checkbox.prop('checked', !checkbox.prop('checked'));
    updateButtons();
});

// 更新按钮的状态
function updateButtons() {
    var checkboxes = $('.select-item:checked');
    var count = checkboxes.length;
    var isSingleFile = count === 1 && !checkboxes.first().data('isDirectory');
    var isSingleZip = isSingleFile && checkboxes.first().closest('tr').find('.file_icon').hasClass('fa-file-zipper');
    var hasCopiedOrCut = false/* 判断是否有复制或剪切的项，你需要自己实现这部分逻辑 */;

    $('.single-download-btn').toggle(isSingleFile);
    $('.batch-zip-download-btn').toggle(count > 0 && !isSingleFile);
    $('.batch-zip-btn').toggle(count >= 1);
    $('.unzip-btn').toggle(isSingleZip);
    $('.single-rename-btn').toggle(count === 1);
    $('.batch-copy-btn').toggle(count >= 1);
    $('.batch-cut-btn').toggle(count >= 1);
    $('.batch-paste-btn').toggle(hasCopiedOrCut);
    $('.calc-sum-btn').toggle(isSingleFile);
    $('.single-share-btn').toggle(count === 1);
    $('.batch-delete-btn').toggle(count >= 1);
}

// 在页面加载时调用updateButtons函数
$(document).ready(updateButtons);

// 当checkbox的状态改变时，调用updateButtons函数
$(document).on('change', '.select-item', updateButtons);