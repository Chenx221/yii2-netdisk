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
$(document).on('click', '.delete-btn', function () {
    var relativePath = $(this).attr('value');
    $('#deleteRelativePath').val(relativePath);
    $('#deleteModal').modal('show');
});
$(document).on('click', '.file-upload-btn', function () {
    // 触发文件输入元素的点击事件
    $('#file-input').click();
});
$('#file-input').on('change', function () {
    $('#progress-bar').show();
    var files = this.files;
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
});


$(document).on('click', '.folder-upload-btn', function () {
    console.log('你点击了上传文件夹，但功能尚未实现');
});
$(document).on('click', '.offline-download-btn', function () {
    console.log('你点击了离线下载，但功能尚未实现');
});

var dropArea = document.getElementById('drop-area');
dropArea.addEventListener('dragover', function (event) {
    // 阻止浏览器的默认行为
    event.preventDefault();
});
dropArea.addEventListener('drop', function (event) {
    // 阻止浏览器的默认行为
    event.preventDefault();

    // 获取用户拖拽的文件或文件夹
    var items = event.dataTransfer.items;

    // 遍历项目
    for (var i = 0; i < items.length; i++) {
        var item = items[i];
        uploadFile(item.getAsFile());
        dropArea.classList.remove('dragging');
    }
});
dropArea.addEventListener('dragenter', function (event) {
    // 阻止浏览器的默认行为
    event.preventDefault();

    // 添加 dragging 类
    dropArea.classList.add('dragging');
});

dropArea.addEventListener('dragleave', function (event) {
    // 阻止浏览器的默认行为
    event.preventDefault();

    // 如果相关目标是拖拽区域或其子元素，那么就不移除 dragging 类
    if (!dropArea.contains(event.relatedTarget)) {
        // 移除 dragging 类
        dropArea.classList.remove('dragging');
    }
});



