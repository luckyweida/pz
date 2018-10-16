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
require('fancybox/dist/js/jquery.fancybox.js');


$(function() {
    $.each($('.js-fragment-container'), function (idx, itm) {
        var id = $(itm).data('id');

        var blocks = JSON.parse($(itm).find('.js-blocks').val());
        var value = JSON.parse($(itm).find('.js-value').val());
        var tags = JSON.parse($(itm).find('.js-tags').val());

        var template_section = Handlebars.compile($(`#${id}_section`).html());
        var template_block = Handlebars.compile($(`#${id}_block`).html());
        var template_modal_section = Handlebars.compile($(`#${id}_modal_section`).html());
        var template_modal_block = Handlebars.compile($(`#${id}_modal_block`).html());
        var template_sidebar = Handlebars.compile($(`#${id}_sidebar`).html());

        $(`.js-blocks-${id}`).sortable({
            connectWith: `.js-blocks-${id}`,
            handle: ".panel-heading",
        }).disableSelection();

        $(`#${id}-modal-section`).on('shown.bs.modal', function () {
            $(`#${id}-modal-section select.js-after-chosen`).chosen({
                allow_single_deselect: true
            });
        });

        // $(document).on('click', `#${id}_wrap .js-section .js-down`, function () {
        //     var id = $(this).closest('.js-section').data('id');
        //     for (var idx in value) {
        //         var itm = value[idx];
        //         if (itm.id == id && idx < (value.length - 1)) {
        //             idx = parseInt(idx, 10);
        //             value[idx] = JSON.parse(JSON.stringify(value[idx + 1]));
        //             value[idx + 1] = JSON.parse(JSON.stringify(itm));
        //             break;
        //         }
        //     }
        //     render();
        //     assemble();
        //     return false;
        // });

        // $(document).on('click', '#${id}_wrap .js-section .js-up', function () {
        //     var id = $(this).closest('.js-section').data('id');
        //     for (var idx in value) {
        //         var itm = value[idx];
        //         if (itm.id == id && idx > 0) {
        //             idx = parseInt(idx, 10);
        //             value[idx] = JSON.parse(JSON.stringify(value[idx - 1]));
        //             value[idx - 1] = JSON.parse(JSON.stringify(itm));
        //             break;
        //         }
        //     }
        //     render();
        //     assemble();
        //     return false;
        // });

        $(document).on('click', `#${id}-add-section`, function () {
            $(`#${id}-modal-section`).html(template_modal_section({
                section: {
                    id: Math.random().toString(36).substr(2, 9),
                    title: 'Content',
                    attr: 'content',
                    status: 1,
                    tags: [],
                    blocks: [],
                },
                optionTags: [],
            }));
            $(`#${id}-modal-section`).modal();
        });


        $(document).on('click', `#${id}_wrap .js-save-section`, function () {
            var section = {
                id: $(`#${id}-modal-section [name=id]`).val(),
                title: $(`#${id}-modal-section [name=name]`).val(),
                attr: $(`#${id}-modal-section [name=attr]`).val(),
                status: $(`#${id}-modal-section [name=status]`).val(),
                tags: typeof $(`#${id}-modal-section [name=tags]`).val() == 'object' ? $(`#${id}-modal-section [name=tags]`).val() : [],
                blocks: [],
            };
            var existSection = getById([], section.id)
            if (!existSection) {
                value.push(section);
            } else {
                existSection.title = section.title;
                existSection.attr = section.attr;
                existSection.tags = section.tags;
            }
            $(`#${id}`).val(cleanString(value));
            $(`#${id}-modal-section`).modal('hide');
            render();
        });

        // $(document).on('click', '#${id}_wrap .js-edit-section', function () {
        //     var id = $(this).closest('.js-section').data('id');
        //     var section = getById(value, id);
        //     $("#${id}-modal-section").html(template_modal_section({
        //         section: section,
        //         optionTags: _${id}_tags,
        // }));
        //     $("#${id}-modal-section").modal();
        //     return false;
        // });
        //

        //
        // $(document).on('click', '#${id}_container .js-delete-section', function () {
        //     var secId = $(this).closest('.js-section').data('id');
        //
        //     for (var idx in value) {
        //         var itm = value[idx];
        //         if (itm.id == secId) {
        //             value.splice(idx, 1)
        //         }
        //     }
        //     render();
        //     assemble();
        //     return false;
        // });
        //
        // $(document).on('click', '#${id}_container .js-status-toggle', function () {
        //     var type = $(this).data('type');
        //     if (type == 'section') {
        //         var obj = getById(value, $(this).closest('.js-section').data('id'));
        //     } else {
        //         var blocks = [];
        //         for (var idx in value) {
        //             var itm = value[idx];
        //             blocks = blocks.concat(itm.blocks);
        //         }
        //         var obj = getById(blocks, $(this).closest('.js-block').data('id'));
        //     }
        //
        //     obj.status = $(this).data('status');
        //     render();
        //     assemble();
        //     return false;
        // });
        //
        // $(document).on('change', '.js-section-${id} .js-add-block', function () {
        //     var blockOption = getById(blocks, $(this).val());
        //     var block = {
        //         id: Math.random().toString(36).substr(2, 9),
        //         title: blockOption.title,
        //         status: 1,
        //         block: blockOption.id,
        //         twig: blockOption.twig,
        //         items: blockOption.items,
        //         values: {},
        //     };
        //     var section = getById(value, $(this).closest('.js-section').data('id'));
        //     $("#${id}-modal-block").html(template_modal_block({
        //         block: block,
        //         section: section,
        //     }));
        //     $("#${id}-modal-block").modal();
        //     $(this).val('');
        // });
        //
        // $(document).on('click', '#${id}_wrap .js-edit-block', function () {
        //     var blocks = [];
        //     for (var idx in value) {
        //         var itm = value[idx];
        //         blocks = blocks.concat(itm.blocks);
        //     }
        //     var block = getById(blocks, $(this).closest('.js-block').data('id'));
        //     var section = getById(value, $(this).closest('.js-section').data('id'));
        //     $("#${id}-modal-block").html(template_modal_block({
        //         block: block,
        //         section: section,
        //     }));
        //     $("#${id}-modal-block").modal();
        //     $(this).val('');
        // });
        //
        // $(document).on('click', '#${id}_wrap .js-save-block', function () {
        //     var blockOption = getById(blocks, $('#${id}-modal-block [name=blockId]').val());
        //
        //     var block = {
        //         id: $('#${id}-modal-block [name=id]').val(),
        //         title: $('#${id}-modal-block [name=name]').val(),
        //         status: $('#${id}-modal-block [name=status]').val(),
        //         block: $('#${id}-modal-block [name=blockId]').val(),
        //         twig: $('#${id}-modal-block [name=twig]').val(),
        //         items: blockOption.items,
        //         values: {},
        //     };
        //
        //     var section = getById(value, $('#${id}-modal-block [name=sectionId]').val());
        //
        //     var blocks = [];
        //     for (var idx in value) {
        //         var itm = value[idx];
        //         blocks = blocks.concat(itm.blocks);
        //     }
        //     var existBlock = getById(blocks, $('#${id}-modal-block [name=id]').val());
        //     if (!existBlock) {
        //         section.blocks.push(block);
        //     } else {
        //         existBlock.title = block.title
        //     }
        //     $('#${id}').val(cleanString(value));
        //     $('#${id}-modal-block').modal('hide');
        //     render();
        // });
        //
        // $(document).on('click', '#${id}_container .js-delete-block', function () {
        //     var secId = $(this).closest('.js-section').data('id');
        //     var blkId = $(this).closest('.js-block').data('id');
        //
        //     var section = getById(value, secId);
        //     for (var idx in section.blocks) {
        //         var itm = section.blocks[idx];
        //         if (itm.id == blkId) {
        //             section.blocks.splice(idx, 1)
        //         }
        //     }
        //     render();
        //     assemble();
        //     return false;
        // });


        var render = function () {
            render_content();
            render_sidebar();
        };

        var render_content = function () {
            $(`#${id}_container`).empty();

            for (var idx in value) {
                var itm = value[idx];
                $(`#${id}_container`).append(template_section({
                    id: `${id}`,
                    blockOptions: blocks,
                    section: itm,
                    idx: idx,
                    total: value.length - 1,
                }));

                for (var idxBlk in itm.blocks) {
                    var block = itm.blocks[idxBlk];
                    $(`.js-section-${id}-${itm.id} .js-blocks`).append(template_block({
                        id: `${id}`,
                        block: block,
                        idx: idxBlk,
                    }));
                }

                if (!itm.blocks.length) {
                    $('.js-section-${id}-' + itm.id + ' .js-blocks .js-no-blocks').fadeIn();
                }
            }

            // $('#${id}_container').sortable({
            //     handle: '.panel-heading',
            //     items: '.panel-default',
            //     stop: function (event, ui) {
            //         assemble${id}();
            //         render${id}();
            //     },
            //     placeholder: {
            //         element: function (currentItem) {
            //             return $('<div class="panel panel-default js-block" colspan="3" style="background: lightyellow; height: ' + $(currentItem).height() + 'px">&nbsp;</div>')[0];
            //         },
            //         update: function (container, p) {
            //             return;
            //         }
            //     }
            // });
            //
            // $('#${id}_container select.js-after-chosen').chosen({
            //     allow_single_deselect: true
            // });
            //
            // $('#${id}_container .js-date').datetimepicker({
            //     timepicker: false,
            //     format: 'Y-m-d',
            //     scrollInput: false,
            // });
            //
            // $('#${id}_container .js-datetime').datetimepicker({
            //     timepicker: true,
            //     format: 'Y-m-d H:i',
            //     scrollInput: false,
            //     step: 5,
            // });
            //
            // $('#${id}_container .js-time').datetimepicker({
            //     timepicker: true,
            //     datepicker: false,
            //     format: 'H:i',
            //     scrollInput: false,
            //     step: 5,
            // });
            //
            // $('#${id}_container .js-redactor').redactor({
            //     plugins: ['filePicker', 'imagePicker', 'video', 'table'],
            //     minHeight: 300,
            //     changeCallback: function() {
            //         assemble${id}();
            //     },
            // });
            //
            // $('#${id}_container .js-asset-delete').click(function(ev) {
            //     $($(this).data('id')).val('');
            //     $($(this).data('id') + '-preview').css('visibility', 'hidden');
            //     assemble${id}();
            // });
            //
            // $('#${id}_container .js-asset-change').click(function(ev) {
            //     var _this = this;
            //     window._callback = function () {
            //         $(_this).closest('.inner-box').find($(_this).data('id')).val($(this).closest('.file-box').data('id'));
            //         $(_this).closest('.inner-box').find($(_this).data('id') + '-preview').css('visibility', 'visible');
            //         $(_this).closest('.inner-box').find($(_this).data('id') + '-preview').attr('src', '/asset/files/image/' + $(this).closest('.file-box').data('id') + '/cms_file_preview');
            //         assemble${id}();
            //     };
            //     filepicker();
            // });
            //
            // $('.js-elem').change(function () {
            //     assemble${id}();
            // });
            //
            // $('.js-cbi-item').on('keyup', function () {
            //     assemble${id}();
            // });
        };

        var render_sidebar = function () {

        };

        var assemble = function () {

        };

        var getById = function (data, id) {
            for (var idx in data) {
                var itm = data[idx];
                if (itm.id == id) {
                    return itm;
                }
            }
            return null;
        };

        var cleanString = function (str) {

        };

        render();
    });

    window._parentId = 0;
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
                    window._redactor.file.insert.call(window._redactor, '<a href="/assets/download/' + $(this).closest('.file-box').data('id') + '/">' + $(this).closest('.file-box').data('title') + '</a>');
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
                    window._redactor.image.insert.call(window._redactor, '<img src="/assets/image/' + $(this).closest('.file-box').data('id') + '/large" alt="' + $(this).closest('.file-box').data('title') + '">');
                };
                filepicker();
            }
        };
    };

    $(document).on('click', ".js-fancybox", function () {
        $.fancybox.open([
            {
                href : $(this).attr('href'),
                type : 'image',
            },
        ], {
            padding : 0
        });
        return false;
    });

    $(document).on('click', '.assetpicker .js-asset-change', function(ev) {
        var _this = this;
        window._callback = function () {
            $($(_this).data('id')).val($(this).closest('.file-box').data('id'));
            $($(_this).data('id') + '-preview').attr('href', '/assets/image/' + $(this).closest('.file-box').data('id') + '/large');
            $($(_this).data('id') + '-preview').find('.image-holder').css('background', 'url("/assets/image/' + $(this).closest('.file-box').data('id') + '/small") no-repeat center center');
        };
        filepicker();
    });

    $(document).on('click', '.assetpicker .js-asset-delete', function(ev) {
        $($(this).data('id')).val('');
        $($(this).data('id') + '-preview').attr('href', '/assets/image/0/large');
        $($(this).data('id') + '-preview').find('.image-holder').css('background', 'url("/assets/image/0/small") no-repeat center center');
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

    $(document).on('change', '.js-choice_multi_json', function() {
        $(this).prev('input').val(JSON.stringify($(this).val()));
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
        url: '/pz/ajax/folders?currentFolderId=' + parentId,
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
            minWidth: 900,
            minHeight: 600,
            maxWidth: 900,
            maxHeight: 600,
        },
    ], {
        padding : 0
    });
    var template = Handlebars.compile($("#loading").html())
    $('#js-folders').html('<div class="jstree">' + template() + '</div>');
    $.ajax({
        type: 'GET',
        url: '/pz/ajax/folders',
        data: 'currentFolderId=' + window._parentId,
        success: function (data) {
            $('#js-folders .jstree').jstree({
                core: {
                    data: [data.root],
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
        url: '/pz/ajax/files/upload',
        dataType: 'json',
        sequentialUploads: true,
        formData: {
            parentId: window._parentId,
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
    if (typeof window._ajax != 'undefined') {
        window._ajax.abort();
    }
    window._ajax = $.ajax({
        type: 'GET',
        url: '/pz/ajax/files',
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