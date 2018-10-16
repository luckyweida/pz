"use strict";
require("./orm.js");

$(function() {
    window._template = Handlebars.compile($("#row").html());;
    $('#add').click(function () {
        try {
            var data = JSON.parse($('#form_items').val());
        } catch (ex) {
            var data = [];
        }
        data.push({
            widget: 0,
            id: 'id',
            title: 'Titie:',
            sql: '',
        });
        $('#form_items').val(JSON.stringify(data));
        render();
    });

    $(document).on('click', '.js-remove', function () {
        try {
            var data = JSON.parse($('#form_items').val());
        } catch (ex) {
            var data = [];
        }
        data.splice($(this).closest('tbody.js-row').data('idx'), 1);
        $('#form_items').val(JSON.stringify(data));
        render();
    });
    render();
});

function render() {
    $("#content-block-container tbody").remove();
    var data = JSON.parse($('#form_items').val());
    for (var idx in data) {
        var itm = data[idx];
        $("#content-block-container").append(_template({
            widgets: window._blockWidgets,
            data: itm,
            idx: idx,
        }));
    }
    $('#content-block-container').sortable({
        items: 'tbody',
        stop: function(event, ui) {
            assembleform_items();
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
    $('.js-cbi-widget').on('change', function () {
        assembleform_items();
        render();
    });
    $('.js-cbi-item').on('keyup', function () {
        assembleform_items();
    });
    $.each($('#content-block-container td'), function (key, value) {
        $(value).css('width', $(value).outerWidth() + 'px');
    });
};

function assembleform_items() {
    var data = [];
    $.each($('#content-block-container tbody.js-row'), function (idx, itm) {
        data.push({
            widget: $(itm).find('.js-cbi-widget').val(),
            id: $(itm).find('.js-cbi-id').val(),
            title: $(itm).find('.js-cbi-title').val(),
            sql: $(itm).find('.js-cbi-sql').val(),
        });
    });
    $('#form_items').val(JSON.stringify(data));
};