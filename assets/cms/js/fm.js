"use strict";
var fm = {}

fm = {
    init: function (options = {}) {
        window.__currentFolderId = $('#currentFolderId').length ? $('#currentFolderId').val() : 0;

        fm.options = options;
        fm.currentFolderId = window.__currentFolderId;

        fm.templateLoading = Handlebars.compile($("#loading").html());
        fm.templateFolder = Handlebars.compile($("#folder").html());
        fm.templateFile = Handlebars.compile($("#file").html());
        fm.templateNav = Handlebars.compile($("#nav").html());

        fm.ajaxFile = null;
        fm.ajaxFolder = null;
        fm.ajaxNav = null;

        fm.keyword = '';
        fm.files = [];
        fm.folders = [];
        fm.currentFolder = null;
        fm.path = [];

        fm.getFolders();
        fm.getFiles();
        fm.getNav();

        $(document).on('click', '.jstree-anchor', function () {
            fm.currentFolderId = $(this).parent().attr('id');
            window.__currentFolderId = fm.currentFolderId;
            $('#currentFolderId').val(window.__currentFolderId);
            fm.getFiles();
            fm.getNav();
            return false;
        });

        $(document).on('click', '.js-nav .pz-nav a', function () {
            fm.currentFolderId = $(this).data('id');
            fm.getFolders();
            fm.getFiles();
            fm.getNav();
            return false;
        });

        $(document).on('change', '.js-search', function () {
            fm.keyword = $(this).val();
            fm.renderFolders();
            fm.renderNav();
            fm.getFiles();
        });

        $(document).on('click', '.js-reset', function () {
            fm.keyword = '';
            fm.renderFolders();
            fm.renderNav();
            fm.getFiles();
            return false;
        });

        $('body').mousedown(function (ev) {
            fm.currentFileId = $(ev.target).data('id');
            if (!fm.currentFileId) {
                fm.currentFileId = $(ev.target).closest('.file-box').data('id');
            }
        });

        $('body').mouseup(function (ev) {
            if ($(ev.target).closest('li').attr('aria-selected') != 'true') {
                var targetFolderId = $(ev.target).closest('li').attr('id')
                if (fm.currentFileId && targetFolderId) {
                    for (var idx in fm.files) {
                        var itm = fm.files[idx];
                        if (itm.id == fm.currentFileId) {
                            fm.files.splice(idx, 1)
                        }
                    }
                    fm.renderFiles();
                    $.ajax({
                        type: 'GET',
                        url: '/pz/ajax/asset/file/move',
                        data: 'parentId=' + targetFolderId + '&id=' + fm.currentFileId,
                        success: function (data) {
                        }
                    });
                }
            }
            fm.currentFileId = null;
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
                    fm.currentFolderId = $(_this).data('parent');
                    $.ajax({
                        type: 'GET',
                        url: '/pz/ajax/asset/files/delete/folder?id=' + $(_this).data('id'),
                        success: function (msg) {
                            swal({
                                title: "Deleted",
                                text: "Your data has been deleted.",
                                icon: 'success',
                                timer: 1000,
                                buttons: false
                            });
                            setTimeout(function () {
                                fm.getFolders();
                                fm.getFiles();
                                fm.getNav();
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
                        url: '/pz/ajax/asset/files/delete/file?id=' + $(_this).closest('.file-box').data('id'),
                        success: function (msg) {
                            swal({
                                title: "Deleted",
                                text: "Your data has been deleted.",
                                icon: 'success',
                                timer: 1000,
                                buttons: false
                            });
                            setTimeout(function () {
                                for (var idx in fm.files) {
                                    var itm = fm.files[idx];
                                    if (itm.id == $(_this).closest('.file-box').data('id')) {
                                        fm.files.splice(idx, 1)
                                    }
                                }
                                fm.renderFiles();
                            }, 800)
                        }
                    });
                }
            });

            return false;
        });
    },

    getFolders: function () {
        $('#js-folders').html(fm.templateLoading());

        if (fm.ajaxFolder) {
            fm.ajaxFolder.abort();
        }
        fm.ajaxFolder = $.ajax({
            type: 'GET',
            url: '/pz/ajax/asset/folders',
            data: 'currentFolderId=' + fm.currentFolderId,
            success: function (data) {
                fm.folders = data.folders;
                fm.renderFolders()
            }
        });
    },

    getFiles: function () {
        $('#js-files').html(fm.templateLoading());

        if (fm.ajaxFile) {
            fm.ajaxFile.abort();
        }
        fm.ajaxFile = $.ajax({
            type: 'GET',
            url: '/pz/ajax/asset/files',
            data: 'currentFolderId=' + fm.currentFolderId + '&keyword=' + fm.keyword,
            success: function (data) {
                fm.files = data.files;
                fm.renderFiles()
            }
        });
    },

    getNav: function () {
        // $('.js-nav').html('<div></div>');

        if (fm.ajaxNav) {
            fm.ajaxNav.abort();
        }
        fm.ajaxNav = $.ajax({
            type: 'GET',
            url: '/pz/ajax/asset/nav',
            data: 'currentFolderId=' + fm.currentFolderId,
            success: function (data) {
                fm.currentFolder = data.currentFolder;
                fm.path = data.path;
                fm.renderNav();
            }
        });
    },

    renderFolders: function () {
        $('#js-folders').html(fm.templateFolder({
            keyword: fm.keyword,
        }));

        $('#js-folders .jstree').jstree({
            core: {
                check_callback: function (operation, node, node_parent, node_position, more) {
                    if (!node_parent.parent) {
                        return false;
                    }
                    return true;
                },
                data: [fm.folders],
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

        $('#js-folders .jstree').bind("move_node.jstree", function (e, data) {
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
                url: '/pz/ajax/asset/folders/update',
                data: 'data=' + encodeURIComponent(JSON.stringify(data)),
                success: function (data) {
                }
            });
        });
    },

    renderFiles: function () {
        $('#js-files').html('<div></div>');
        for (var idx in fm.files) {
            var itm = fm.files[idx];
            var template = Handlebars.compile($("#file").html())
            $('#js-files > div').append(template(itm))
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

        $(':checkbox').iCheck({
            checkboxClass: 'icheckbox_square-green',
        });
    },

    renderNav: function () {
        $('.js-nav').html(fm.templateNav({
            keyword: fm.keyword,
            currentFolder: fm.currentFolder,
            path: fm.path,
        }));

        $('#fileupload').fileupload({
            url: '/pz/ajax/asset/files/upload',
            dataType: 'json',
            sequentialUploads: true,
            formData: {
                parentId: fm.currentFolderId,
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
                    fm.files.unshift(data.result);
                    fm.renderFiles();
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
                    url: '/pz/ajax/asset/files/add/folder',
                    data: {
                        title: $('#js-add-dialog form input[name=name]').val(),
                        parentId: fm.currentFolderId,
                    },
                    success: function (data) {
                        $('#js-add-dialog form input[name=name]').val('');
                        $('#js-add-dialog').modal('hide');
                        fm.getFolders();
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
                    url: '/pz/ajax/asset/files/edit/folder',
                    data: {
                        id: fm.currentFolderId,
                        title: $('#js-edit-dialog form input[name=name]').val(),
                    },
                    success: function (data) {
                        $('#js-edit-dialog').modal('hide');
                        fm.getFolders();
                        fm.getNav();
                    }
                });
            }
            return false;
        });
    },
};

window.fm = fm;