"use strict";
require("./orm.js");

$(function() {
    window._template = Handlebars.compile($("#row").html());;
    $('#add').click(function () {
        try {
            var data = JSON.parse($('#form_content').val());
        } catch (ex) {
            var data = [];
        }
        data.push({
            id: 'content',
            title: 'Content:',
            tags: [],
        });
        $('#form_content').val(JSON.stringify(data));
        render();
    });

    $(document).on('click', '.js-remove', function () {
        try {
            var data = JSON.parse($('#form_content').val());
        } catch (ex) {
            var data = [];
        }
        data.splice($(this).closest('tbody.js-row').data('idx'), 1);
        $('#form_content').val(JSON.stringify(data));
        render();
    });
    render();
});

function render() {
    $("#content-block-container tbody").remove();
    var data = $('#form_content').val() ? JSON.parse($('#form_content').val()) : [];
    for (var idx in data) {
        var itm = data[idx];
        $("#content-block-container").append(_template({
            tags: window._blockWidgets,
            data: itm,
            idx: idx,
        }));
    }
    $('#content-block-container').sortable({
        items: 'tbody',
        stop: function(event, ui) {
            assembleform_content();
        },
        placeholder: {
            element: function(currentItem) {
                return $('<tr><td colspan="3" style="background: lightyellow; height: ' + $(currentItem).height() + 'px">&nbsp;</td></tr>')[0];
            },
            update: function(container, p) {
                return;
            }
        }
    });

    $('.js-cbi-tags').chosen();

    $('.js-cbi-item').on('keyup', function () {
        assembleform_content();
    });
    $('.js-cbi-tags').on('change', function () {
        assembleform_content();
        render();
    });
    $.each($('#content-block-container td'), function (key, value) {
        $(value).css('width', $(value).outerWidth() + 'px');
    });
};

function assembleform_content() {
    var data = [];
    $.each($('#content-block-container tbody.js-row'), function (idx, itm) {
        data.push({
            id: $(itm).find('.js-cbi-id').val(),
            title: $(itm).find('.js-cbi-title').val(),
            tags: $(itm).find('.js-cbi-tags').val(),
        });
    });
    $('#form_content').val(JSON.stringify(data));
};