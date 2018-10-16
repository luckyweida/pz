"use strict";
require("./main.js");


require('../../redactor/redactor/redactor.css');
require('../../inspinia/css/plugins/jsTree/style.min.css');
require('jquery.fancybox/source/jquery.fancybox.css');
require('animate.css/animate.css');


require('../../redactor/redactor/redactor.js');
require('../../redactor/plugins/table.js');
require('../../redactor/plugins/video.js');

var jstree = require('jstree/dist/jstree.js');
window.jstree = jstree;

require('../../inspinia/js/plugins/slimscroll/jquery.slimscroll.min.js');

require('blueimp-file-upload/js/jquery.iframe-transport.js');
require('blueimp-file-upload/js/jquery.fileupload.js');
// require('jquery.fancybox');

var fileManager = {}

fileManager.init = function (currentFolderId, keyword) {
    fileManager.fileTemplate = Handlebars.compile($("#file").html());
    fileManager.fileNavTemplate = Handlebars.compile($("#file-nav").html());
    fileManager.currentFolderId = currentFolderId;
    fileManager.keyword = keyword;
    fileManager.files = [];
    fileManager.path = [];
    fileManager.currentFolder = null;
    fileManager.currentFileId = null;

    $(".js-search").keyup(function () {
        $('#js-folders > div').jstree('search', $(this).val());
    });

    $(document).on('click', '.jstree-anchor', function () {
        fileManager.currentFolderId = $(this).parent().attr('id');
        history.pushState(null, null, '/pz/files?currentFolderId=' + fileManager.currentFolderId);
        fileManager.getFiles();
        fileManager.getFolderNav();
        return false;
    });

    $(document).on('click', '#js-files-nav .pz-nav a', function () {
        fileManager.currentFolderId = $(this).data('id');
        history.pushState(null, null, '/pz/files?currentFolderId=' + fileManager.currentFolderId);
        fileManager.getFolders();
        fileManager.getFolderNav();
        fileManager.getFiles();
        return false;
    });

    $('body').mousedown(function (ev) {
        fileManager.currentFileId = $(ev.target).data('id');
        if (!fileManager.currentFileId) {
            fileManager.currentFileId = $(ev.target).closest('.file-box').data('id');
        }
    });

    $('body').mouseup(function (ev) {
        if ($(ev.target).closest('li').attr('aria-selected') != 'true') {
            var targetFolderId = $(ev.target).closest('li').attr('id')
            if (fileManager.currentFileId && targetFolderId) {
                for (var idx in fileManager.files) {
                    var itm = fileManager.files[idx];
                    if (itm.id == fileManager.currentFileId) {
                        fileManager.files.splice(idx, 1)
                    }
                }
                fileManager.renderFiles();
                $.ajax({
                    type: 'GET',
                    url: '/pz/ajax/file/move',
                    data: 'parentId=' + targetFolderId + '&id=' + fileManager.currentFileId,
                    success: function (data) {
                    }
                });
            }
        }
        fileManager.currentFileId = null;
    });

    $(document).on('click', '.js-folder-delete', function(ev) {
        var _this = this;
        swal({
            title: "Are you sure?",
            text: "You will not be able to recover this data!",
            icon: "warning",
            dangerMode: true,
            buttons: {
                cancel: {
                    text: "Cancel",
                    visible: true,
                    closeModal: true,
                },
                confirm: {
                    text: "Delete",
                    closeModal: false
                }
            }
        }).
        then((willDelete) => {
            if (willDelete) {
                fileManager.currentFolderId = $(_this).data('parent');
                $.ajax({
                    type: 'GET',
                    url: '/pz/ajax/files/delete/folder?id=' + $(_this).data('id'),
                    success: function (msg) {
                        swal({
                            title: "Deleted",
                            text: "Your data has been deleted.",
                            icon: 'success',
                            timer: 1000,
                            buttons: false
                        });
                        setTimeout(function () {
                            fileManager.getFolders();
                            fileManager.getFolderNav();
                            fileManager.getFiles();
                        }, 800)
                    }
                });
            }
        });
    });

    $(document).on('click', '.js-file-delete', function(ev) {
        var _this = this;
        swal({
            title: "Are you sure?",
            text: "You will not be able to recover this data!",
            icon: "warning",
            dangerMode: true,
            buttons: {
                cancel: {
                    text: "Cancel",
                    visible: true,
                    closeModal: true,
                },
                confirm: {
                    text: "Delete",
                    closeModal: false
                }
            }
        }).
        then((willDelete) => {
            if (willDelete) {
                $.ajax({
                    type: 'POST',
                    url: '/pz/ajax/files/delete/file?id=' + $(_this).closest('.file-box').data('id'),
                    success: function (msg) {
                        swal({
                            title: "Deleted",
                            text: "Your data has been deleted.",
                            icon: 'success',
                            timer: 1000,
                            buttons: false
                        });
                        setTimeout(function () {
                            for (var idx in fileManager.files) {
                                var itm = fileManager.files[idx];
                                if (itm.id == $(_this).closest('.file-box').data('id')) {
                                    fileManager.files.splice(idx, 1)
                                }
                            }
                            fileManager.renderFiles();
                        }, 800)
                    }
                });
            }
        });

        return false;
    });

    $('#js-folders').html('<div>Loading...</div>');

    fileManager.getFiles();
    fileManager.getFolders();
    fileManager.getFolderNav();
};

fileManager.getFolderNav = function () {
    if (fileManager.keyword) {
        return;
    }
    $.ajax({
        type: 'GET',
        url: '/pz/ajax/folder/nav',
        data: 'currentFolderId=' + fileManager.currentFolderId,
        success: function (data) {
            fileManager.currentFolder = data.currentFolder;
            fileManager.path = data.path;
            fileManager.renderFolderNav();
        }
    });
};

fileManager.renderFolderNav = function () {
    $('#js-files-nav').html(fileManager.fileNavTemplate({
        keyword: fileManager.keyword,
        currentFolder: fileManager.currentFolder,
        path: fileManager.path,
        files: fileManager.files,
    }));

    $('#fileupload').fileupload({
        url: '/pz/ajax/files/upload',
        dataType: 'json',
        sequentialUploads: true,
        formData: {
            parentId: fileManager.currentFolderId,
        },
        add: function (e, data) {
            var uploadErrors = [];
            if (data.files[0]['size'] == '' || data.files[0]['size'] > 50000000) {
                uploadErrors.push('File size is too big');
            }
            if (uploadErrors.length > 0) {
                alert(uploadErrors.join("\n"));
            } else {
                $('.progress-bar').css('width', 0);
                $('.progress').show();
                data.submit();
            }
        },
        start: function () {
        },
        done: function (e, data) {
            if (typeof data.result.id != 'undefined') {
                fileManager.files.unshift(data.result);
                fileManager.renderFiles();
            }
        },
        progressall: function (e, data) {
            var progress = parseInt(data.loaded / data.total * 100, 10);
            $('.progress-bar').css('width', progress + '%');
        },
        stop: function (e) {
            $('.progress').fadeOut(3000);
        }
    }).prop('disabled', !$.support.fileInput).parent().addClass($.support.fileInput ? undefined : 'disabled');

    $('#js-add-dialog form').validate({
        rules: {
            name: "required",
        },
    });

    $('#js-add-dialog form').submit(function (ev) {
        ev.preventDefault();
        if ($(this).valid()) {
            $.ajax({
                type: 'GET',
                url: '/pz/ajax/files/add/folder',
                data: {
                    title: $('#js-add-dialog form input[name=name]').val(),
                    parentId: fileManager.currentFolderId,
                },
                success: function (data) {
                    $('#js-add-dialog form input[name=name]').val('');
                    $('#js-add-dialog').modal('hide');
                    fileManager.getFolders();
                }
            });
        }
        return false;
    });

    $('#js-edit-dialog form').validate({
        rules: {
            name: "required",
        },
    });

    $('#js-edit-dialog form').submit(function (ev) {
        ev.preventDefault();
        if ($(this).valid()) {
            $.ajax({
                type: 'POST',
                url: '/pz/ajax/files/edit/folder',
                data: {
                    id: fileManager.currentFolderId,
                    title: $('#js-edit-dialog form input[name=name]').val(),
                },
                success: function (data) {
                    $('#js-edit-dialog').modal('hide');
                    fileManager.getFolders();
                }
            });
        }
        return false;
    });
};

fileManager.getFolders = function () {
    $.ajax({
        type: 'GET',
        url: '/pz/ajax/folders',
        data: 'currentFolderId=' + fileManager.currentFolderId,
        success: function (data) {
            fileManager.root = data.root;
            fileManager.renderFolders(data)
        }
    });
};

fileManager.renderFolders = function () {
    $('#js-folders').html('<div></div>');
    $('#js-folders > div').jstree({
        core: {
            check_callback: function (operation, node, node_parent, node_position, more) {
                if (!node_parent.parent) {
                    return false;
                }
                return true;
            },
            data: [fileManager.root],
        },
        search: {
            show_only_matches: true
        },
        plugins: ['types', 'dnd', 'search'],
        types: {
            default: {
                'icon': 'fa fa-folder'
            },
        }
    });

    $('#js-folders > div').bind("move_node.jstree", function (e, data) {
        var nodes = $(this).jstree().get_json($(this), {
            flat: true
        });

        var data = [];
        for (var idx in nodes) {
            var itm = nodes[idx];
            if (itm.parent != '#') {
                data.push({
                    id: itm.id,
                    parentId: itm.parent,
                    rank: idx,
                });
            }
        }
        $.ajax({
            type: 'GET',
            url: '/pz/ajax/folders/update',
            data: 'data=' + encodeURIComponent(JSON.stringify(data)),
            success: function (data) {
            }
        });
    });
};

fileManager.getFiles = function () {
    $('#js-files').html($("#loading").html());
    $.ajax({
        type: 'GET',
        url: '/pz/ajax/files',
        data: 'currentFolderId=' + fileManager.currentFolderId + '&keyword=' + fileManager.keyword,
        success: function (data) {
            fileManager.files = data.files;
            fileManager.renderFiles();
        }
    });
};

fileManager.renderFiles = function () {
    if (fileManager.keyword) {
        $('#js-files-nav').html(fileManager.files.length + ' Results Found')
    }

    $('#js-files').html('<div></div>');
    for (var idx in fileManager.files) {
        var itm = fileManager.files[idx];
        $('#js-files > div').append(fileManager.fileTemplate(itm))
    }

    $('#js-files > div').sortable({
        cursorAt: {left: -30, top: -30},
        stop: function () {
            var data = $('#js-files > div').sortable("toArray");
            $.ajax({
                type: 'GET',
                url: '/pz/ajax/column/sort',
                data: {
                    data: JSON.stringify(data),
                    className: 'Asset',
                },
                success: function (data) {
                }
            });
        }
    });
};

$(function() {
    fileManager.init(window._currentFolderId, window._keyword);
});