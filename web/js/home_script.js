$(document).on('click', '.rename-btn', function () {
    var relativePath = $(this).attr('value');
    var fileName = $(this).closest('tr').find('td:eq(1)').text().trim();
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
    $.each(relativePaths, function (index, value) {
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
    var relativePaths = $('.select-item:checked').map(function () {
        return $(this).data('relativePath');
    }).get();
    $('#zipRelativePath').val(JSON.stringify(relativePaths));
    $('#zipModal').modal('show');
});

$(document).on('click', '.unzip-btn', function () {
    var relativePath = $('.select-item:checked').first().data('relativePath');
    $.ajax({
        type: "POST",
        url: "index.php?r=home%2Funzip",
        data: {relativePath: relativePath},
        dataType: "json",  // 期望从服务器接收json格式的响应
        success: function (response) {
            // 如果服务器返回的状态码是200，说明解压成功
            if (response.status !== 200) {
                console.error('Unzip failed: ' + response.message);
            }
            window.location.href = 'index.php?r=home%2Findex&directory=' + encodeURIComponent(response.parentDirectory);
        },
        error: function () {
            // 处理错误
            console.error('AJAX request failed.');
        }
    });
});

$(document).on('click', '.single-rename-btn', function () {
    var relativePath = $('.select-item:checked').first().data('relativePath');
    $('.rename-btn[value="' + relativePath + '"]').trigger('click');
});
// 当用户点击复制按钮时
$(document).on('click', '.batch-copy-btn', function () {
    var relativePaths = $('.select-item:checked').map(function () {
        return $(this).data('relativePath');
    }).get();
    sessionStorage.setItem('operation', 'copy');
    sessionStorage.setItem('relativePaths', JSON.stringify(relativePaths));
    updateButtons();  // 更新按钮的状态
});

// 当用户点击剪切按钮时
$(document).on('click', '.batch-cut-btn', function () {
    var relativePaths = $('.select-item:checked').map(function () {
        return $(this).data('relativePath');
    }).get();
    sessionStorage.setItem('operation', 'cut');
    sessionStorage.setItem('relativePaths', JSON.stringify(relativePaths));
    updateButtons();  // 更新按钮的状态
});

// 当用户点击粘贴按钮时
$(document).on('click', '.batch-paste-btn', function () {
    var operation = sessionStorage.getItem('operation');
    var relativePaths = JSON.parse(sessionStorage.getItem('relativePaths'));
    var targetDirectory = $('#target-dir').val();
    $.ajax({
        type: "POST",
        url: "index.php?r=home%2Fpaste",
        data: {operation: operation, relativePaths: relativePaths, targetDirectory: targetDirectory},
        success: function () {
            // 处理响应
            location.reload();
        },
        error: function () {
            // 处理错误
            console.error('AJAX request failed.');
        }
    });
    sessionStorage.removeItem('operation');  // 清除 sessionStorage 中的 operation
    sessionStorage.removeItem('relativePaths');  // 清除 sessionStorage 中的 relativePaths
    hasCopiedOrCut = false;  // 设置 hasCopiedOrCut 为 false
    updateButtons();  // 更新按钮的状态
});

$(document).on('click', '.calc-sum-btn', function () {
    var relativePath = $('.select-item:checked').first().data('relativePath');
    $.ajax({
        type: "POST",
        url: "index.php?r=home%2Fchecksum",
        data: {relativePath: relativePath},
        dataType: "json",
        success: function (response) {
            // 更新模态框中的内容
            $('#crc32b').text('CRC32B: ' + response.crc32b);
            $('#sha256').text('SHA256: ' + response.sha256);
            // 显示模态框
            $('#checksumModal').modal('show');
        },
        error: function () {
            console.error('AJAX request failed.');
        }
    });
});

$(document).on('click', '.single-share-btn', function () {
    var relativePath = $('.select-item:checked').first().data('relativePath');
    $('#shareModal #share-file_relative_path').val(relativePath);
    $('#shareModal').modal('show');
});
$(document).on('click', '#generate_access_code', function () {
    var accessCode = Math.random().toString(36).substring(2, 6);
    $('#shareModal #share-access_code').val(accessCode);
});

$(document).on('click', '.shares-btn', function () {
    var relativePath = $(this).closest('tr').find('.select-item').data('relativePath');
    $('#shareModal #share-file_relative_path').val(relativePath);
    $('#shareModal').modal('show');
});

$(document).on('click', '.batch-delete-btn', function () {
    var relativePaths = $('.select-item:checked').map(function () {
        return $(this).data('relativePath');
    }).get();
    $.ajax({
        type: "POST",
        url: "index.php?r=home%2Fdelete",
        data: {relativePath: relativePaths},
        success: function () {
            // 处理响应
            location.reload();
        },
        error: function () {
            // 处理错误
            console.error('AJAX request failed.');
            location.reload();
        }
    });
});

$(document).on('click', '.single-open-btn', function () {
    // 下面这个写法无效，因为它目前只对支持预览的设置onclick事件的文件有效
    // $('.select-item:checked').first().closest('tr').find('.file_name').click();

    var firstSelectedElement = $('.select-item:checked').first().closest('tr').find('.file_name')[0];
    if (firstSelectedElement) {
        firstSelectedElement.click();
    }
});

//下面的代码实现了各种按钮/样式功能，建议别看了(写的头疼了

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
    if (event.ctrlKey && event.key === 'a') {
        event.preventDefault();
        var checkboxes = document.querySelectorAll('.select-item');
        var allChecked = Array.from(checkboxes).every(checkbox => checkbox.checked);
        for (var i = 0; i < checkboxes.length; i++) {
            checkboxes[i].checked = !allChecked;
            checkboxes[i].closest('tr').classList.toggle('selected', !allChecked);
        }
        updateButtons();
    }
    if (event.ctrlKey && event.key === 'd') {
        event.preventDefault();
        $('tr.selected').removeClass('selected').find('input[type="checkbox"]').prop('checked', false);
        $('#select-all').prop('checked', false);
        updateButtons();
    }
    if (event.ctrlKey && event.key === 'c') {
        var cp = $('.batch-copy-btn');
        if (cp.css('display') !== 'none') {
            cp.click();
        }
    }
    if (event.ctrlKey && event.key === 'x') {
        var ct = $('.batch-cut-btn');
        if (ct.css('display') !== 'none') {
            ct.click();
        }
    }
    if (event.ctrlKey && event.key === 'v') {
        var pe = $('.batch-paste-btn');
        if (pe.css('display') !== 'none') {
            pe.click();
        }
    }
});

//行点击事件
$(document).on('click', 'tr', function (event) {
    if ($(event.target).is('input[type="checkbox"]') || $(event.target).is('a')) {
        return;
    }
    var checkboxes = document.querySelectorAll('.select-item');
    // 检查是否所有的复选框都已经被选中
    var checkedCount = Array.from(checkboxes).filter(checkbox => checkbox.checked).length;
    // 如果选中的复选框的数量大于或等于2，那么就将allChecked设置为true
    var allChecked = checkedCount >= 2;    // 如果Ctrl键没有被按下，取消所有其他行的选中状态
    if (!event.ctrlKey) {
        $('tr.selected').not(this).removeClass('selected').find('input[type="checkbox"]').prop('checked', false);
    }
    // 切换当前行的选中状态
    if ((!allChecked) || (allChecked && event.ctrlKey)) {
        $(this).toggleClass('selected');
        var checkbox = $(this).children(':first-child').find('input[type="checkbox"]');
        checkbox.prop('checked', !checkbox.prop('checked'));
    }
    updateButtons();
});

// 更新按钮的状态
function updateButtons() {
    var checkboxes = $('.select-item:checked');
    var count = checkboxes.length;
    var isSingleFile = count === 1 && !checkboxes.first().data('isDirectory');
    var isSingleZip = isSingleFile && checkboxes.first().closest('tr').find('.file_icon').hasClass('fa-file-zipper');
    var hasOperation = sessionStorage.getItem('operation') !== null;  // 检查 sessionStorage 中是否存在 operation
    $('.single-open-btn').toggle(count === 1);
    $('.single-download-btn').toggle(isSingleFile);
    $('.batch-zip-download-btn').toggle(count > 0 && !isSingleFile);
    $('.batch-zip-btn').toggle(count >= 1);
    $('.unzip-btn').toggle(isSingleZip);
    $('.single-rename-btn').toggle(count === 1);
    $('.batch-copy-btn').toggle(count >= 1);
    $('.batch-cut-btn').toggle(count >= 1);
    $('.batch-paste-btn').toggle(hasOperation);  // 根据 hasOperation 的值来决定是否显示粘贴按钮
    $('.calc-sum-btn').toggle(isSingleFile);
    $('.single-share-btn').toggle(count === 1);
    $('.batch-delete-btn').toggle(count >= 1);
    $('.create-collection-btn').toggle(count === 1 && !isSingleFile);
}

// 当checkbox的状态改变时，调用updateButtons函数
$(document).on('change', '.select-item', updateButtons);

$(document).ready(function () {
    updateButtons();

    $('tr').contextmenu(function (e) {
        e.preventDefault();
        if ($(e.target).is('button') || $(e.target).is('i') || $(e.target).is('a')) {
            e.preventDefault();  // 阻止事件的默认行为
            return;
        }
        // 获取点击的元素
        var clickedElement = $(this);

        if (!clickedElement.hasClass('selected')) {
            $('tr.selected').removeClass('selected').find('input[type="checkbox"]').prop('checked', false);
            clickedElement.addClass('selected');
            var checkbox = clickedElement.children(':first-child').find('input[type="checkbox"]');
            checkbox.prop('checked', true);
            updateButtons();
        }

        $('#option-open').toggle($('.single-open-btn').css('display') !== 'none');
        $('#option-download').toggle($('.single-download-btn').css('display') !== 'none');
        $('#option-batch-zip-download').toggle($('.batch-zip-download-btn').css('display') !== 'none');
        $('#option-single-rename').toggle($('.single-rename-btn').css('display') !== 'none');
        $('#option-batch-copy').toggle($('.batch-copy-btn').css('display') !== 'none');
        $('#option-batch-cut').toggle($('.batch-cut-btn').css('display') !== 'none');
        $('#option-batch-paste').toggle($('.batch-paste-btn').css('display') !== 'none');
        $('#option-batch-delete').toggle($('.batch-delete-btn').css('display') !== 'none');
        $('#option-refresh').toggle($('.refresh-btn').css('display') !== 'none');

        // 显示菜单
        $('#contextMenu').css({
            display: "block",
            left: e.pageX,
            top: e.pageY
        }).addClass('show');
        $('#contextMenu .dropdown-menu').addClass('show');
    });
    // 当用户点击菜单项时
    $('#contextMenu a').off('click').on('click', function (e) {
        e.preventDefault();
        // 获取点击的菜单项
        var clickedMenuItem = $(this).attr('id');

        // 根据点击的菜单项执行相应的操作
        switch (clickedMenuItem) {
            case 'option-open':
                // 模拟点击打开按钮
                $('.single-open-btn').click();
                break;
            case 'option-download':
                // 模拟点击下载按钮
                $('.single-download-btn').click();
                break;
            case 'option-batch-zip-download':
                // 模拟点击打包下载按钮
                $('.batch-zip-download-btn').click();
                break;
            case 'option-single-rename':
                // 模拟点击重命名按钮
                $('.single-rename-btn').click();
                break;
            case 'option-batch-copy':
                // 模拟点击复制按钮
                $('.batch-copy-btn').click();
                break;
            case 'option-batch-cut':
                // 模拟点击剪切按钮
                $('.batch-cut-btn').click();
                break;
            case 'option-batch-paste':
                // 模拟点击粘贴按钮
                $('.batch-paste-btn').click();
                break;
            case 'option-batch-delete':
                // 模拟点击删除按钮
                $('.batch-delete-btn').click();
                break;
            case 'option-refresh':
                // 模拟点击刷新按钮
                $('.refresh-btn').click();
                break;
        }

        // 隐藏菜单
        $('#contextMenu').hide().removeClass('show');
    });

// 当用户点击其他地方时，隐藏菜单
    $(document).click(function () {
        $('#contextMenu').hide().removeClass('show');
        $('#contextMenu .dropdown-menu').removeClass('show');
    });
});

// image preview
function previewImage(element, event) {
    event.preventDefault(); // 阻止默认的点击事件
    var hiddenImage = document.getElementById('hidden-image');
    hiddenImage.src = element.href; // 设置图像的URL

    // 创建一个新的 Viewer.js 实例
    var viewer = new Viewer(hiddenImage, {
        toolbar: {
            zoomIn: 1,
            zoomOut: 1,
            oneToOne: 1,
            reset: 1,
            rotateLeft: 1,
            rotateRight: 1,
            flipHorizontal: 1,
            flipVertical: 1,
        },
        hidden: function () {
            viewer.destroy();
        }
    });
    viewer.show();
}

// video preview
var player;
var videoModal = $('#videoModal'); // 存储选择器的结果

function previewVideo(element, event) {
    event.preventDefault(); // 阻止默认的点击事件
    var videoElement = document.getElementById('vPlayer');
    videoElement.src = element.href; // 设置视频的URL
    videoElement.type = element.getAttribute('type'); // 设置视频的MIME类型

    // 创建一个新的 Plyr 实例
    player = new Plyr(videoElement);
    player.play();

    // 显示模态框
    videoModal.modal('show');
}

videoModal.on('hidden.bs.modal', function () {
    if (player) {
        player.destroy();
    }
});

//music preview
var aPlayer;
var audioModal = $('#audioModal');

function previewAudio(element, event) {
    event.preventDefault();
    var audioElement = document.getElementById('aPlayer');
    audioElement.src = element.href;
    audioElement.type = element.getAttribute('type');

    aPlayer = new Plyr(audioElement);
    aPlayer.play();

    // 显示模态框
    audioModal.modal('show');
}

audioModal.on('hidden.bs.modal', function () {
    if (aPlayer) {
        aPlayer.destroy();
    }
});

//text edit
var editor;
var editorModal = $('#textEditModal');

function textEdit(element, event) {
    event.preventDefault();
    editorModal.modal('show');
    var fileUrl = element.href;
    $('#edFilename').val(element.innerText);
    $('#ed-alert-success').hide();
    $('#ed-alert-fail').hide();
    $.get(fileUrl, function (data) {
        editor = ace.edit("editor");
        editor.session.setOption("useWorker", false);
        editor.setTheme("ace/theme/github");
        editor.renderer.setOption("fontSize", 15);
        editor.renderer.setOption("animatedScroll", true)
        editor.session.setMode("ace/mode/text");
        editor.setValue(data);
    });
}

editorModal.on('hidden.bs.modal', function () {
    if (editor) {
        editor.destroy();
        // editor.container.remove();
    }
});
$('#saveButton').on('click', function () {
    var content = editor.getValue();
    var blob = new Blob([content], {type: "text/plain"});
    var file = new File([blob], $('#edFilename').val(), {type: "text/plain"});
    var formData = new FormData();
    formData.append('files', file);
    formData.append('sp', 'editSaving');
    formData.append('targetDir', $('#target-dir').val());

    $.ajax({
        type: "POST",
        url: "index.php?r=home%2Fupload", // 替换为你的上传URL
        data: formData,
        processData: false, // 告诉jQuery不要处理发送的数据
        contentType: false, // 告诉jQuery不要设置Content-Type请求头
        success: function (data) {
            if (data.status === 200) {
                $('#ed-alert-success').show();
                $('#ed-alert-fail').hide();
            } else {
                $('#ed-alert-success').hide();
                $('#ed-alert-fail').show();
            }
        },
        error: function () {
            $('#ed-alert-success').hide();
            $('#ed-alert-fail').show();
        }
    });
});

//pdf preview
var pdfModal = $('#pdfModal');

function previewPdf(element, event) {
    event.preventDefault();
    var pdfUrl = element.href;
    var pdfObject = document.createElement('object');
    pdfObject.id = 'pdfObject';
    pdfObject.type = 'application/pdf';
    pdfObject.style.width = '100%';
    pdfObject.style.height = '500px';
    pdfObject.data = pdfUrl;
    pdfModal.find('.modal-body').append(pdfObject);
    pdfModal.modal('show');
}

pdfModal.on('hidden.bs.modal', function () {
    var pdfObject = document.getElementById('pdfObject');
    if (pdfObject) {
        pdfObject.remove();
    }
});

//create collection task
$(document).on('click', '.create-collection-btn', function () {
    document.getElementById('collectiontasks-folder_path').value = $('.select-item:checked').first().data('relativePath');
    $('#collectionModal').modal('show');
});

const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]')
const popoverList = [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl))

$(document).on('click', '#btnSearch', function () {
    var keyword = $('#filesearch-keyword').val();
    var directory = $('#filesearch-directory').val();
    $('#search-result').html('');
    $.ajax({
        type: "POST",
        url: "index.php?r=home%2Fsearch",
        data: {keyword: keyword, directory: directory},
        success: function (response) {
            if (response.status === 'success') {
                var table = '<table class="table"><tr><th style="display: table-cell;">文件/文件夹名</th><th>位置</th></tr>';
                $.each(response.data, function (index, item) {
                    let path = item.relativePath;
                    let correctedPath = path.replace(/\\/g, '/');
                    table += '<tr><td style="display: table-cell;"> <i class="' + item.type + '"></i>  ' + item.name + '</td>' + '<td><a style="text-decoration: underline;" href="index.php?r=home%2Findex&directory=' + correctedPath + '">' + item.relativePath + '</a></td></tr>';
                });
                table += '</table>';
                $('#search-result').html(table);
            } else if (response.status === 'error') {
                $('#search-result').html(response.message);
            } else {
                $('#search-result').html('An error occurred while processing your request.');
            }
        },
        error: function () {
            $('#search-result').html('An error occurred while processing your request.');
        },
        beforeSend: function () {
            $('#loading').show();
        },
        complete: function () {
            $('#loading').hide();
        },
    });
});
$('#searchModal').on('hidden.bs.modal', function () {
    $('#search-result').html('');
    $('#filesearch-keyword').val('');
});