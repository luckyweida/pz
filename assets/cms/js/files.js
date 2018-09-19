"use strict";
require('fancybox/dist/css/jquery.fancybox.css');
require('../../redactor/redactor/redactor.css');
require('../../inspinia/css/plugins/jsTree/style.min.css');

require("./main.js");

require('../../redactor/redactor/redactor.js');
require('../../redactor/plugins/table.js');
require('../../redactor/plugins/video.js');

var jstree = require('jstree/dist/jstree.js');
window.jstree = jstree;

require('../../inspinia/js/plugins/slimscroll/jquery.slimscroll.min.js');

require('blueimp-file-upload/js/jquery.iframe-transport.js');
require('blueimp-file-upload/js/jquery.fileupload.js');
require('fancybox');

var fileManager = {}

fileManager.init = function (currentFolderId, keyword) {
    fileManager.fileTemplate = Handlebars.compile($("#file").html());
    fileManager.fileNavTemplate = Handlebars.compile($("#file-nav").html());
    fileManager.currentFolderId = -1;
    fileManager.keyword = -1;
    fileManager.files = [];
    fileManager.currentFileId = null;

    fileManager.currentFolderId = currentFolderId;
    fileManager.keyword = keyword;

    $(".js-search").keyup(function () {
        $('#js-folders > div').jstree('search', $(this).val());
    });

    $(document).on('click', '.jstree-anchor', function () {
        fileManager.currentFolderId = $(this).parent().attr('id');
        history.pushState(null, null, '/pz/secured/files/?currentFolderId=' + fileManager.currentFolderId);
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
                fileManager._renderFiles();
                $.ajax({
                    type: 'POST',
                    url: '/pz/secured/files/move-file',
                    data: '__parentId=' + targetFolderId + '&id=' + fileManager.currentFileId,
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
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Yes, delete it!",
            closeOnConfirm: false,
            showLoaderOnConfirm: true,
        }, function () {
            $.ajax({
                type: 'POST',
                url: '/pz/secured/files/delete-folder/' + $(_this).data('id'),
                success: function (msg) {
                    swal({
                        title: "Deleted",
                        text: "Your data has been deleted.",
                        type: 'success',
                        timer: 1000,
                        showConfirmButton: false
                    });
                    setTimeout(function () {
                        location.href = '/pz/secured/files/?currentFolderId=' + $(_this).data('parent')
                    }, 800)
                }
            });

        });
        return false;
    });

    $(document).on('click', '.js-file-delete', function(ev) {
        var _this = this;
        swal({
            title: "Are you sure?",
            text: "You will not be able to recover this data!",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Yes, delete it!",
            closeOnConfirm: false,
            showLoaderOnConfirm: true,
        }, function () {
            $.ajax({
                type: 'POST',
                url: '/pz/secured/files/delete-file/' + $(_this).closest('.file-box').data('id'),
                success: function (msg) {
                    swal({
                        title: "Deleted",
                        text: "Your data has been deleted.",
                        type: 'success',
                        timer: 1000,
                        showConfirmButton: false
                    });
                    setTimeout(function () {
                        for (var idx in fileManager.files) {
                            var itm = fileManager.files[idx];
                            if (itm.id == $(_this).closest('.file-box').data('id')) {
                                fileManager.files.splice(idx, 1)
                            }
                        }
                        fileManager._renderFiles();
                    }, 800)
                }
            });

        });
        return false;
    });

    fileManager.getFolders();
    fileManager.getFiles();
};

fileManager.getFolders = function () {
    $('#js-folders').html('<div>Loading...</div>');
    $.ajax({
        type: 'POST',
        url: '/pz/ajax/folders',
        data: 'currentFolderId=' + fileManager.currentFolderId,
        success: function (data) {
            fileManager.renderFolders(data)
        }
    });
};

fileManager.renderFolders = function (root) {
    $('#js-folders > div').jstree({
        core: {
            check_callback: function (operation, node, node_parent, node_position, more) {
                if (!node_parent.parent) {
                    return false;
                }
                return true;
            },
            data: [root],
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
                    __parentId: itm.parent,
                    __rank: idx,
                });
            }
        }
        $.ajax({
            type: 'POST',
            url: '/pz/secured/files/update-folders',
            data: 'data=' + encodeURIComponent(JSON.stringify(data)),
            success: function (data) {
            }
        });
    });
};

fileManager.getFiles = function () {
    $('#js-files').html($("#loading").html());
    $('#js-files-nav').empty();

    $.ajax({
        type: 'POST',
        url: '/pz/ajax/files',
        data: 'currentFolderId=' + fileManager.currentFolderId + '&keyword=' + fileManager.keyword,
        success: function (data) {
            fileManager.files = data;
            fileManager.renderFiles(data);
        }
    });
};

fileManager.renderFiles = function (data) {
    $('#js-files-nav').html(fileManager.fileNavTemplate(data));
    $('#fileupload').fileupload({
        url: '/pz/secured/files/upload',
        dataType: 'json',
        sequentialUploads: true,
        formData: {
            __parentId: fileManager.currentFolderId,
        },
        add: function (e, data) {
            var uploadErrors = [];
            if (data.files[0]['size'] == '' || data.files[0]['size'] > 50000000) {
                uploadErrors.push('File size is too big');
            }
            if (uploadErrors.length > 0) {
                alert(uploadErrors.join("\n"));
            } else {
                $('.progress').show();
                data.submit();
            }
        },
        start: function () {
            $('.progress-bar').css('width', 0);
        },
        done: function (e, data) {
            fileManager.files.unshift(data.result);
            fileManager._renderFiles();
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
                type: 'POST',
                url: '/pz/secured/files/add-folder',
                data: {
                    title: $('#js-add-dialog form input[name=name]').val(),
                    __parentId: fileManager.currentFolderId,
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
                url: '/pz/secured/files/edit-folder',
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

    fileManager.files = data.files;
    fileManager._renderFiles();
};

fileManager._renderFiles = function () {
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
                type: 'POST',
                url: '/pz/secured/contents/sort',
                data: {
                    data: JSON.stringify(data),
                    model: 'Asset',
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