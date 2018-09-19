"use strict";
require('@fancyapps/fancybox/dist/jquery.fancybox.css');
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
require('@fancyapps/fancybox');

$(function() {
    window._parentId = -1;
    window._folders = [];
    window._files = [];
    window._ancestors = [];

    $.Redactor.prototype.filePicker = function() {
        return {
            init: function()
            {
                var button = this.button.add('file', 'File Picker');
                this.button.addCallback(button, this.filePicker.show);
            },
            show: function()
            {
                window._redactor = this;
                window._callback = function() {
                    window._redactor.file.insert.call(window._redactor, '<a href="/asset/files/download/' + $(this).closest('.file-box').data('id') + '/">' + $(this).closest('.file-box').data('title') + '</a>');
                };
                filepicker();
            }
        };
    };

    $.Redactor.prototype.imagePicker = function() {
        return {
            init: function()
            {
                var button = this.button.add('image', 'Image Picker');
                this.button.addCallback(button, this.imagePicker.show);
            },
            show: function()
            {
                window._redactor = this;
                window._callback = function() {
                    window._redactor.image.insert.call(window._redactor, '<img src="/asset/files/image/' + $(this).closest('.file-box').data('id') + '/general" alt="' + $(this).closest('.file-box').data('title') + '">');
                };
                filepicker();
            }
        };
    };

    $(document).on('click', '.assetpicker .js-asset-change', function(ev) {
        var _this = this;
        window._callback = function () {
            $($(_this).data('id')).val($(this).closest('.file-box').data('id'));
            $($(_this).data('id') + '-preview').css('visibility', 'visible');
            $($(_this).data('id') + '-preview').attr('src', '/asset/files/image/' + $(this).closest('.file-box').data('id') + '/cms_file_preview');
        };
        filepicker();
    });

    $(document).on('click', '.assetpicker .js-asset-delete', function(ev) {
        $($(this).data('id')).val('');
        $($(this).data('id') + '-preview').css('visibility', 'hidden');
    });

    $(document).on('click', '.assetfolderpicker .change', function(ev) {
        var _this = this;
        window._callback = function () {
            $($(_this).attr('data-id')).val($(this).closest('tr.folder-row').attr('data-id'));
            $($(_this).attr('data-id') + '-title').html($(this).closest('tr.folder-row').find('.folder').html());
        };
        folderpicker();
    });

    $(document).on('click', '.assetfolderpicker .delete', function(ev) {
        $($(this).attr('data-id')).val('');
        $($(this).attr('data-id') + '-title').html('Choose...');
    });

    $(document).on('click', '#js-files .file-box a', function() {
        window._callback.call(this);
        $.fancybox.close();
        return false;
    });


    $('.wysiwyg textarea').redactor({
        plugins: ['filePicker', 'imagePicker', 'video', 'table'],
        minHeight: 300,
    });

    $('select:not(.no-chosen)').chosen({
        allow_single_deselect: true
    });
});

function folderpicker() {
    $('.asset-picker-upload').css('visibility', 'hidden');
    $('#popup-container .title').html('Choose a folder');
    $('#popup-container .content').hide();
    $('#popup-container .load').show();
    $.fancybox.open([
        {
            href : '#popup-container',
            type : 'inline',
            minWidth: 400,
            minHeight: 600,
            maxWidth: 400,
            maxHeight: 600,
            helpers: {
                overlay: {
                    locked: false
                }
            }
        },
    ], {
        padding : 0
    });

    folders(-1);
}

function folders(parentId) {

    $.ajax({
        url: '/pz/asset/json/' + parentId + '/',
        dataType: 'json',
        beforeSend: function() {
            $('#popup-container .content').fadeOut(400, function() {
                $('#popup-container .load').fadeIn();
            });
        },
    }).done(function(json) {
        window._folders = json[0];
        window._files = [];
        window._ancestors = json[2];
        window._parentId = json[3];
        repaintAssetFolderPicker();

    });
};

function repaintAssetFolderPicker() {
    $('#popup-container .content .modal-body').empty();

    var folderPickerTableTemplate = Handlebars.compile(_folderPickerTableSource);
    $('#popup-container .content .modal-body').append(folderPickerTableTemplate());

    var folderPickerTableRowTemplate = Handlebars.compile(_folderPickerTableRowSource);
    for (var idx in window._folders) {
        var itm = window._folders[idx];
        $('#folderpicker-table-body').append(folderPickerTableRowTemplate({
            itm: itm,
        }));
    }


    $('#popup-container .load').fadeOut(400, function() {
        $('#popup-container .content').fadeIn();
    });

    $('#popup-container #folderpicker-table-body .folder').off();
    $('#popup-container #folderpicker-table-body .folder').click(function() {
        folders($(this).closest('tr.folder-row').attr('data-id'));
        return false;
    });

    $('#popup-container #folderpicker-table-body .select').off();
    $('#popup-container #folderpicker-table-body .select').click(function() {
        window._callback.call(this);
        $.fancybox.close();
        return false;
    });

    $('#popup-container .content .breadcrumb').empty();
    $('#popup-container .content .breadcrumb').append(_ancestors.length == 0 ? '<li class="active"><strong>Home</strong></li>' : '<li><a href="#" data-id="0">Home</a></li>');
    for (var idx in window._ancestors) {
        var itm = window._ancestors[idx];
        $('#popup-container .content .breadcrumb').append(idx == window._ancestors.length - 1 ? '<li class="active"><strong>' + itm.title + '</strong></li>' : '<li><a href="#" data-id="' + itm.id + '">' + itm.title + '</a></li>');
    }

    $('#popup-container .content .breadcrumb a').click(function() {
        folders($(this).attr('data-id'));
        return false;
    });
}

function filepicker() {
    $.fancybox.open([
        {
            href : '#popup-container',
            type : 'inline',
            minWidth: 850,
            minHeight: 600,
            maxWidth: 850,
            maxHeight: 600,
        },
    ], {
        padding : 0
    });
    var template = Handlebars.compile($("#loading").html())
    $('#js-folders').html('<div class="jstree">' + template() + '</div>');
    $.ajax({
        type: 'POST',
        url: '/pz/secured/files/folders',
        data: 'currentFolderId=' + window._parentId,
        success: function (data) {
            $('#js-folders .jstree').jstree({
                core: {
                    data: [data],
                },
                plugins: ['types'],
                types: {
                    default: {
                        'icon': 'fa fa-folder'
                    },
                }
            });
            getFiles();
        }
    });
    $('#js-folders .jstree').on("select_node.jstree", function (e, data) {
        window._parentId = data.node.id;
        getFiles();
    });

    getFiles();
}

function getFiles() {
    var template = Handlebars.compile($("#template-upload").html());
    $('#popup-container .js-upload').html(template());
    $('#fileupload').fileupload({
        url: '/pz/secured/files/upload',
        dataType: 'json',
        sequentialUploads: true,
        formData: {
            _parentId: window._parentId,
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
            getFiles();
        },
        progressall: function (e, data) {
            var progress = parseInt(data.loaded / data.total * 100, 10);
            $('.progress-bar').css('width', progress + '%');
        },
        stop: function (e) {
            $('.progress').fadeOut(3000);
        }
    }).prop('disabled', !$.support.fileInput).parent().addClass($.support.fileInput ? undefined : 'disabled');

    var template = Handlebars.compile($("#loading").html())
    $('#js-files').html('<div>' + template() + '</div>');
    $.ajax({
        type: 'POST',
        url: '/pz/secured/files/files',
        data: 'currentFolderId=' + window._parentId + '&keyword=',
        success: function (data) {
            $('#js-files').html('<div></div>');
            for (var idx in data.files) {
                var itm = data.files[idx];
                var template = Handlebars.compile($("#file").html())
                $('#js-files > div').append(template(itm))
                $('#js-files > div').find('.file-box .js-file-delete').remove();

            }
        }
    });
};