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

require('./fm.js');

$(function() {

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
                    $.fancybox.close();
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
                    $.fancybox.close();
                };
                filepicker();
            }
        };
    };

    var templateGalleryFile = Handlebars.compile($('#gallery-file').html());

    var getById = function (data, id) {
        for (var idx in data) {
            var itm = data[idx];
            if (itm.id == id) {
                return itm;
            }
        }
        return null;
    };

    var cleanString = function (sections) {
        sections = JSON.parse(JSON.stringify(sections));
        for (var idxSection in sections) {
            var section = sections[idxSection];
            for (var idxBlock in section.blocks) {
                var block = section.blocks[idxBlock];
                delete block.items;
            }
        }
        return JSON.stringify(sections)
    };

    var getRandomId = function () {
        return 'uniq_' + Math.random().toString(36).substr(2, 9) + new Date().getTime();
    };

    window._galleryFiles = [];
    var renderElements = function(container, callback) {
        $.each($(container).find('.assetfolderpicker'), function(idx, itm) {
            var modelName = $(itm).closest('form').data('modelname');
            var ormId = $(itm).closest('form').data('ormid');
            var attributeName = $(itm).find('input').data('attributename');

            var getFiles = function () {
                $(itm).find('.js-no-results').hide();
                $(itm).find('.js-loading').show();
                $(itm).find('.js-gallery-container').parent().addClass('sk-loading');
                $.ajax({
                    type: 'GET',
                    url: '/pz/ajax/asset/files/chosen',
                    data: 'modelName=' + modelName + '&attributeName=' + attributeName + '&ormId=' + ormId,
                    success: function (data) {
                        window._galleryFiles[attributeName] = data;
                        renderFiles();
                    }
                });
            };

            var renderFiles = function () {
                var data = window._galleryFiles[attributeName];
                $(itm).find('.js-loading').hide();
                $(itm).find('.js-gallery-container').parent().removeClass('sk-loading');
                $(itm).find('.js-gallery-container').empty();
                for (var idxValue in data) {
                    var itmValue = data[idxValue];
                    $(itm).find('.js-gallery-container').append(templateGalleryFile(itmValue));
                }
                if (!data.length) {
                    $(itm).find('.js-no-results').show();
                }
                $(itm).find('.js-gallery-container').sortable({
                    cursorAt: {left: -30, top: -30},
                    stop: function () {
                        var data = $(itm).find('.js-gallery-container').sortable("toArray");
                        $.ajax({
                            type: 'GET',
                            url: '/pz/ajax/asset/files/chosen/rank',
                            data: 'modelName=' + modelName + '&attributeName=' + attributeName + '&ormId=' + ormId + '&ids=' + encodeURIComponent(JSON.stringify(data)),
                            success: function (data) {
                            }
                        });
                    }
                });
            };

            if (typeof window._galleryFiles[attributeName] !== 'undefined') {
                renderFiles();
            } else {
                getFiles();
            }

            $(itm).on('click', '.change', function(ev) {
                window._callback = function () {
                    $(this).next('div').find('a').click();
                };

                folderpicker(modelName, ormId, attributeName, function () {
                    getFiles();
                });
            });

            $(itm).on('click', '.delete', function(ev) {
                $(itm).find('.js-no-results').hide();
                $(itm).find('.js-loading').show();
                $(itm).find('.js-gallery-container').parent().addClass('sk-loading');
                $.ajax({
                    type: 'GET',
                    url: '/pz/ajax/asset/folders/file/select',
                    data: 'modelName=' + modelName + '&attributeName=' + attributeName + '&ormId=' + ormId + '&addOrDelete=2',
                    success: function (data) {
                        getFiles();
                    }
                });
            });

            $(itm).on('click', '.pz-file-delete', function () {
                $(itm).find('.js-no-results').hide();
                $(itm).find('.js-loading').show();
                $(itm).find('.js-gallery-container').parent().addClass('sk-loading');
                var id = $(this).parent().data('id');
                $.ajax({
                    type: 'GET',
                    url: '/pz/ajax/asset/folders/file/select',
                    data: 'modelName=' + modelName + '&attributeName=' + attributeName + '&ormId=' + ormId + '&addOrDelete=0' + '&id[]=' + id,
                    success: function (data) {
                        getFiles();
                    }
                });
                return false;
            });

            $(itm).on('click', ".js-fancybox-gallery", function () {
                var images = [];
                images.push({
                    href : $(this).attr('href'),
                    type : 'image',
                });

                $.each($(itm).find('a').not(this), function (idx, itm) {
                    images.push({
                        href : $(itm).attr('href'),
                        type : 'image',
                    });
                });
                $.fancybox.open(images, {
                    padding : 0
                });
                return false;
            });

        });

        $.each($(container).find('.assetpicker'), function(idx, itm) {
            $(itm).on('click', '.js-asset-change', function(ev) {
                var _this = this;
                window._callback = function () {
                    $($(_this).data('id')).val($(this).closest('.file-box').data('id'));
                    $($(_this).data('id') + '-preview').attr('href', '/assets/image/' + $(this).closest('.file-box').data('id') + '/large');
                    $($(_this).data('id') + '-preview').find('.image-holder').css('background', 'url("/assets/image/' + $(this).closest('.file-box').data('id') + '/small") no-repeat center center');
                    if (callback) {
                        callback();
                    };
                    $.fancybox.close();
                };
                filepicker();
            });

            $(itm).on('click', '.js-asset-delete', function(ev) {
                $($(this).data('id')).val('');
                $($(this).data('id') + '-preview').attr('href', '/assets/image/0/large');
                $($(this).data('id') + '-preview').find('.image-holder').css('background', 'url("/assets/image/0/small") no-repeat center center');
                if (callback) {
                    callback();
                };
            });
        });

        $(container).find('.box.datepicker input,.inner-box.datepicker input').datetimepicker({
            timepicker: false,
            format: 'Y-m-d',
            scrollInput: false,
        });

        $(container).find('.box.datetimepicker input,.inner-box.datetimepicker input').datetimepicker({
            step: 5,
            format: 'Y-m-d H:i',
            scrollInput: false,
        });

        $(container).find('.box.timepicker input,.inner-box.timepicker input').datetimepicker({
            timepicker: true,
            datepicker: false,
            step: 5,
            format: 'H:i',
            scrollInput: false,
        });

        $(container).find('select:not(.no-chosen)').chosen({
            allow_single_deselect: true
        });

        $(container).find('.wysiwyg textarea').redactor({
            plugins: ['filePicker', 'imagePicker', 'video', 'table'],
            minHeight: 300,
            changeCallback: function() {
                if (callback) {
                    callback();
                }
            },
        });
    };
    renderElements($('.page-content'), null);

    $.each($('.js-fragment-container'), function (idx, itm) {
        var dataId = $(itm).data('id');
        var dataBlocks = JSON.parse($(itm).find('.js-blocks').val());
        var dataValue = JSON.parse($(itm).find(`#${dataId}`).val());
        var dataTags = JSON.parse($(itm).find('.js-tags').val());
        var dataDefault = $(itm).find('.js-default').val() != 'null' ? JSON.parse($(itm).find('.js-default').val()) : null;

        if (!dataValue.length && dataDefault) {
            dataDefault.content = JSON.parse(dataDefault.content);

            for (var idx in dataDefault.content) {
                var itm = dataDefault.content[idx];
                dataValue.push({
                    id: getRandomId(),
                    title: itm.title,
                    attr: itm.id,
                    status: 1,
                    tags: itm.tags,
                    blocks: [],
                })
            }
        }

        for (var idx in dataBlocks) {
            var itm = dataBlocks[idx];
            itm.items = JSON.parse(itm.items);
        }

        for (var secIdx in dataValue) {
            var sec = dataValue[secIdx];
            for (var blkIdx in sec.blocks) {
                var blk = sec.blocks[blkIdx];
                var dataBlk = getById(dataBlocks, blk.block);
                blk.items = dataBlk.items;
            }
        }

        var template_section = Handlebars.compile($(`#${dataId}_section`).html());
        var template_block = Handlebars.compile($(`#${dataId}_block`).html());
        var template_modal_section = Handlebars.compile($(`#${dataId}_modal_section`).html());
        var template_modal_block = Handlebars.compile($(`#${dataId}_modal_block`).html());
        var template_sidebar = Handlebars.compile($(`#${dataId}_sidebar`).html());

        $(`.js-blocks-${dataId}`).sortable({
            connectWith: `.js-blocks-${dataId}`,
            handle: ".panel-heading",
        }).disableSelection();

        $(`#${dataId}-modal-section`).on('shown.bs.modal', function () {
            $(`#${dataId}-modal-section select.js-after-chosen`).chosen({
                allow_single_deselect: true
            });
        });

        $(document).on('click', `#${dataId}_wrap .js-section .js-down`, function () {
            var id = $(this).closest('.js-section').data('id');
            for (var idx in dataValue) {
                var itm = dataValue[idx];
                if (itm.id == id && idx < (dataValue.length - 1)) {
                    idx = parseInt(idx, 10);
                    dataValue[idx] = JSON.parse(JSON.stringify(dataValue[idx + 1]));
                    dataValue[idx + 1] = JSON.parse(JSON.stringify(itm));
                    break;
                }
            }
            render();
            assemble();
            return false;
        });

        $(document).on('click', `#${dataId}_wrap .js-section .js-up`, function () {
            var id = $(this).closest('.js-section').data('id');
            for (var idx in dataValue) {
                var itm = dataValue[idx];
                if (itm.id == id && idx > 0) {
                    idx = parseInt(idx, 10);
                    dataValue[idx] = JSON.parse(JSON.stringify(dataValue[idx - 1]));
                    dataValue[idx - 1] = JSON.parse(JSON.stringify(itm));
                    break;
                }
            }
            render();
            assemble();
            return false;
        });

        $(document).on('click', `#${dataId}-add-section`, function () {
            $(`#${dataId}-modal-section`).html(template_modal_section({
                section: {
                    id: getRandomId(),
                    title: 'Content',
                    attr: 'content',
                    status: 1,
                    tags: [],
                    blocks: [],
                },
                optionTags: [],
            }));
            $(`#${dataId}-modal-section`).modal();
        });

        $(document).on('click', `#${dataId}_wrap .js-save-section`, function () {
            var section = {
                id: $(`#${dataId}-modal-section [name=id]`).val(),
                title: $(`#${dataId}-modal-section [name=name]`).val(),
                attr: $(`#${dataId}-modal-section [name=attr]`).val(),
                status: $(`#${dataId}-modal-section [name=status]`).val(),
                tags: typeof $(`#${dataId}-modal-section [name=tags]`).val() == 'object' ? $(`#${dataId}-modal-section [name=tags]`).val() : [],
                blocks: [],
            };
            var existSection = getById(dataValue, section.id)
            if (!existSection) {
                dataValue.push(section);
            } else {
                existSection.title = section.title;
                existSection.attr = section.attr;
                existSection.tags = section.tags;
            }
            $(`#${dataId}`).val(cleanString(dataValue));
            $(`#${dataId}-modal-section`).modal('hide');
            render();
        });

        $(document).on('click', `#${dataId}_wrap .js-edit-section`, function () {
            var id = $(this).closest('.js-section').data('id');
            var section = getById(dataValue, id);
            $(`#${dataId}-modal-section`).html(template_modal_section({
                section: section,
                optionTags: dataTags,
            }));
            $(`#${dataId}-modal-section`).modal();
            return false;
        });

        $(document).on('click', `#${dataId}_container .js-delete-section`, function () {
            var secId = $(this).closest('.js-section').data('id');

            for (var idx in dataValue) {
                var itm = dataValue[idx];
                if (itm.id == secId) {
                    dataValue.splice(idx, 1)
                }
            }
            render();
            assemble();
            return false;
        });

        $(document).on('click', `#${dataId}_container .js-status-toggle`, function () {
            var type = $(this).data('type');
            if (type == 'section') {
                var obj = getById(dataValue, $(this).closest('.js-section').data('id'));
            } else {
                var blocks = [];
                for (var idx in dataValue) {
                    var itm = dataValue[idx];
                    blocks = blocks.concat(itm.blocks);
                }
                var obj = getById(blocks, $(this).closest('.js-block').data('id'));
            }

            obj.status = $(this).data('status');
            render();
            assemble();
            return false;
        });

        $(document).on('change', `.js-section-${dataId} .js-add-block`, function () {
            var blockOption = getById(dataBlocks, $(this).val());
            var block = {
                id: getRandomId(),
                title: blockOption.title,
                status: 1,
                block: blockOption.id,
                twig: blockOption.twig,
                items: blockOption.items,
                values: {},
            };
            var section = getById(dataValue, $(this).closest('.js-section').data('id'));
            $(`#${dataId}-modal-block`).html(template_modal_block({
                block: block,
                section: section,
            }));
            $(`#${dataId}-modal-block`).modal();
            $(this).val('');
        });

        $(document).on('click', `#${dataId}_wrap .js-edit-block`, function () {
            var blocks = [];
            for (var idx in dataValue) {
                var itm = dataValue[idx];
                blocks = blocks.concat(itm.blocks);
            }
            var block = getById(blocks, $(this).closest('.js-block').data('id'));
            var section = getById(dataValue, $(this).closest('.js-section').data('id'));
            $(`#${dataId}-modal-block`).html(template_modal_block({
                block: block,
                section: section,
            }));
            $(`#${dataId}-modal-block`).modal();
            $(this).val('');
        });

        $(document).on('click', `#${dataId}_wrap .js-save-block`, function () {
            var blockOption = getById(dataBlocks, $(`#${dataId}-modal-block [name=blockId]`).val());

            var block = {
                id: $(`#${dataId}-modal-block [name=id]`).val(),
                title: $(`#${dataId}-modal-block [name=name]`).val(),
                status: $(`#${dataId}-modal-block [name=status]`).val(),
                block: $(`#${dataId}-modal-block [name=blockId]`).val(),
                twig: $(`#${dataId}-modal-block [name=twig]`).val(),
                items: blockOption.items,
                values: {},
            };

            var section = getById(dataValue, $(`#${dataId}-modal-block [name=sectionId]`).val());

            var blocks = [];
            for (var idx in dataValue) {
                var itm = dataValue[idx];
                blocks = blocks.concat(itm.blocks);
            }
            var existBlock = getById(blocks, $(`#${dataId}-modal-block [name=id]`).val());
            if (!existBlock) {
                section.blocks.push(block);
            } else {
                existBlock.title = block.title
            }
            $(`#${dataId}`).val(cleanString(dataValue));
            $(`#${dataId}-modal-block`).modal('hide');
            render();
        });

        $(document).on('click', `#${dataId}_container .js-delete-block`, function () {
            var secId = $(this).closest('.js-section').data('id');
            var blkId = $(this).closest('.js-block').data('id');

            var section = getById(dataValue, secId);
            for (var idx in section.blocks) {
                var itm = section.blocks[idx];
                if (itm.id == blkId) {
                    section.blocks.splice(idx, 1)
                }
            }
            render();
            assemble();
            return false;
        });

        var render = function () {
            render_content();
            render_sidebar();
        };

        var render_content = function () {
            $(`#${dataId}_container`).empty();

            for (var idx in dataValue) {
                var itm = dataValue[idx];
                var blocks = [];
                for (idxBlk in dataBlocks) {
                    var dataBlk = dataBlocks[idxBlk];
                    var tags = itm.tags.filter(value => -1 !== dataBlk.tags.indexOf(value));
                    if (tags.length || !itm.tags.length) {
                        blocks.push(dataBlk);
                    }
                }
                $(`#${dataId}_container`).append(template_section({
                    id: `${dataId}`,
                    blockOptions: blocks,
                    section: itm,
                    idx: idx,
                    total: dataValue.length - 1,
                }));

                for (var idxBlk in itm.blocks) {
                    var block = itm.blocks[idxBlk];
                    $(`.js-section-${dataId}-${itm.id} .js-blocks`).append(template_block({
                        id: `${dataId}`,
                        block: block,
                        idx: idxBlk,
                    }));
                }

                if (!itm.blocks.length) {
                    $(`.js-section-${dataId}-${itm.id} .js-blocks .js-no-blocks`).fadeIn();
                }
            }

            $(`#${dataId}_container`).sortable({
                handle: '.panel-heading',
                items: '.panel-default',
                stop: function (event, ui) {
                    assemble();
                    render();
                },
                placeholder: {
                    element: function (currentItem) {
                        return $('<div class="panel panel-default js-block" colspan="3" style="background: lightyellow; height: ' + $(currentItem).height() + 'px">&nbsp;</div>')[0];
                    },
                    update: function (container, p) {
                        return;
                    }
                }
            });

            $(`#${dataId}_container select.js-after-chosen`).chosen({
                allow_single_deselect: true
            });

            renderElements($(`#${dataId}_container`), function () {
                assemble();
            });

            $('.js-elem').change(function () {
                assemble();
            });

            $('.js-cbi-item').on('keyup', function () {
                assemble();
            });
        };

        var render_sidebar = function () {
            $(`.sidebar${dataId}`).remove();
            $('body').append(template_sidebar({
                className: `sidebar${dataId}`,
            }));
            $(`.sidebar${dataId} .panel-body`).append('<div class="jstree"></div>');

            if ($(window).scrollTop() > 150) {
                $(`.sidebar${dataId}`).css('top', 0);
            } else {
                $(`.sidebar${dataId}`).css('top', '150px');
            }
            $(window).on('scroll resize', function () {
                if ($(window).scrollTop() > 150) {
                    $(`.sidebar${dataId}`).css('top', 0);
                } else {
                    $(`.sidebar${dataId}`).css('top', '150px');
                }
            });

            var data = [];
            for (var idxSec in dataValue) {
                var section = dataValue[idxSec];
                var obj = {
                    id: section.id,
                    text: section.title,
                    state: {
                        opened: true,
                        selected: false
                    },
                    children: [],
                    type: section.status == 1 ? 'section' : 'section-disabled',
                };
                for (var idxBlk in section.blocks) {
                    var block = section.blocks[idxBlk];
                    obj.children.push({
                        id: block.id,
                        text: block.title,
                        type: block.status == 1 ? 'block' : 'block-disabled',
                    })
                }
                data.push(obj);
            }

            $(`.sidebar${dataId} .jstree`).jstree({
                core: {
                    check_callback: true,
                    data: data,
                },
                'plugins': ['types', 'dnd'],
                'types': {
                    "#" : {
                        "valid_children" : ["section"]
                    },
                    "section" : {
                        'icon': 'fa fa-folder-open-o',
                        "valid_children" : ["block"]
                    },
                    "section-disabled" : {
                        'icon': 'fa fa-folder-open-o text-danger',
                        "valid_children" : ["block"]
                    },
                    "block" : {
                        'icon': 'fa fa-file-text-o',
                        "valid_children" : [],
                    },
                    "block-disabled" : {
                        'icon': 'fa fa-file-text-o text-danger',
                        "valid_children" : [],
                    },
                },
            }).on('ready.jstree',
                function() {
                    setTimeout($.proxy(function () {
                        $('.scroll-content').slimscroll({
                            height: Math.min($(window).height() - 50, $(`.sidebar${dataId} .panel-body`).outerHeight()) + 'px',
                        })
                    }, this), 100);
                }
            );

            $(`.sidebar${dataId} .jstree`).bind("move_node.jstree", function (e, data) {
                var nodes = $(this).jstree().get_json($(this), {
                    flat: true
                });

                var blocks = [];
                for (var idx in dataValue) {
                    var itm = dataValue[idx];
                    blocks = blocks.concat(itm.blocks);
                }

                var result = [];
                for (var idx in nodes) {
                    var itm = nodes[idx];
                    if (itm.parent == '#') {
                        var section = getById(dataValue, itm.id);
                        section.blocks = [];
                        for (var idxBlk in nodes) {
                            var blk = nodes[idxBlk];
                            if (blk.parent == itm.id) {
                                section.blocks.push(getById(blocks, blk.id));
                            }
                        }
                        result.push(section);
                    }
                }

                dataValue = result;
                render_content();
                assemble();

            });
            $(`.sidebar${dataId} .jstree`).on("select_node.jstree", function (e, data) {
                if (data.node.parent == '#') {
                    var selector = `.js-section-${dataId}-${data.node.id}`;
                } else {
                    var selector = `.js-block-${data.node.id}`;
                }
                $("html, body").animate({ scrollTop: $(selector).position().top });
            });

            $(`.sidebar${dataId}`).find('.sidebar-submit .js-back').click(function () {
                location.href = $('form').find('.js-back').attr('href');
                return false;
            });

            $(`.sidebar${dataId}`).find('.sidebar-submit [value=Apply]').click(function () {
                $('form').find('[value=Apply]').click();
            });

            $(`.sidebar${dataId}`).find('.sidebar-submit [value=Save]').click(function () {
                $('form').find('[value=Save]').click();
            });
        };

        var assemble = function () {
            var blocks = [];
            for (var idx in dataValue) {
                var itm = dataValue[idx];
                blocks = blocks.concat(itm.blocks);
            }

            $.each($(`#${dataId}_container .js-section`), function (idx, itm) {
                var section = getById(dataValue, $(itm).data('id'));
                section.blocks = [];
                $.each($(itm).find('.js-block'), function (idxBlk, itmBlk) {
                    var block = getById(blocks, $(itmBlk).data('id'));
                    if (block) {
                        $.each($(itmBlk).find('.js-elem'), function (idxElem, itmElem) {
                            if ($(itmElem).attr('type') == 'checkbox') {
                                block.values[$(itmElem).data('id')] = $(itmElem).is(':checked') ? 1 : 0;
                            } else {
                                block.values[$(itmElem).data('id')] = $(itmElem).val();
                            }
                        });
                        section.blocks.push(block)
                    }
                });
            });
            $(`#${dataId}`).val(cleanString(dataValue));
        };

        render();
    });

    window._parentId = 0;
    window._folders = [];
    window._files = [];
    window._ancestors = [];

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

    $(document).on('click', '#js-files .file-box a', function() {
        window._callback.call(this);
        return false;
    });

    $(document).on('change', '.js-choice_multi_json', function() {
        $(this).prev('input').val(JSON.stringify($(this).val()));
    });
});

function folderpicker(modelName, ormId, attributeName, callback) {
    $.fancybox.open([
        {
            href : '#popup-container',
            type : 'inline',
            minWidth: 900,
            minHeight: 600,
            maxWidth: 900,
            maxHeight: 600,
            beforeClose: function() {
                callback()
            }
        },
    ], {
        padding : 0
    });

    fm.init({
        mode: 1,
        modelName: modelName,
        attributeName: attributeName,
        ormId: ormId,
    });
};

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

    fm.init({
        mode: 0,
        modelName: null,
        attributeName: null,
        ormId: null,
    });
};
