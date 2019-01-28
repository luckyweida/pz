"use strict";
require("./orm.js");

$(function() {
    window.formFieldsId = 'form_formFields';
    window._formbuilder = $("#formbuilder-source").html();

    $(document).on('change', '#' + window.formFieldsId + '_formbuilder .wgt', function(ev) {
        if ($(this).val() == 'choice') {
            $('#sql' + $(this).attr('data-idx')).show();
        } else {
            $('#sql' + $(this).attr('data-idx')).hide();
        }
    });

    $(document).on('keyup, change', '#' + window.formFieldsId + '_formbuilder .form-control', function(ev) {
        assemble();
    });

    $(document).on('click', '#' + window.formFieldsId + '_formbuilder .js-formbuilderfield-delete', function(ev) {
        var json = JSON.parse($('#' + window.formFieldsId).val() ? $('#' + window.formFieldsId).val() : '[]');
        json.splice($(this).attr('data-idx'), 1);
        $('#' + window.formFieldsId).val(JSON.stringify(json));

        $(this).closest('tbody').remove();
        return false;
    });

    $(document).on('change', '#' + window.formFieldsId + '_add', function(ev) {
        var itm = {
            widget: $(this).val(),
            label: 'New Field',
            id: 'newFeild',
            required: 0,
            sql: '',
        };
        var json = [];
        try {
            json = JSON.parse($('#' + window.formFieldsId).val() ? $('#' + window.formFieldsId).val() : '[]');
        } catch (ex) {
            json = [];
        }
        json.push(itm);
        $('#' + window.formFieldsId).val(JSON.stringify(json));

        repaint_add(itm, json.length - 1);

        $('#' + window.formFieldsId + '_formbuilder').sortable({
            items: 'tbody',
            stop: function(event, ui) {
                assemble();
            },
            placeholder: {
                element: function(currentItem) {
                    return $('<tr><td colspan="5" style="height: ' + $(currentItem).height() + 'px">&nbsp;</td></tr>')[0];
                },
                update: function(container, p) {
                    return;
                }
            }
        });
    });

    repaint();
    $('#' + window.formFieldsId + '_formbuilder').sortable({
        items: 'tbody',
        stop: function(event, ui) {
            assemble();
        },
        placeholder: {
            element: function(currentItem) {
                return $('<tr><td colspan="5" style="height: ' + $(currentItem).height() + 'px">&nbsp;</td></tr>')[0];
            },
            update: function(container, p) {
                return;
            }
        }
    });
});

function repaint() {
    $('#' + window.formFieldsId + '_formbuilder').find('tbody').remove();
    var json = JSON.parse($('#' + window.formFieldsId).val() ? $('#' + window.formFieldsId).val() : '[]');
    for (var idx in json) {
        var itm = json[idx];
        repaint_add(itm, idx);
    }
};

function repaint_add(itm, idx) {
    var template = Handlebars.compile(window._formbuilder);
    $('#' + window.formFieldsId + '_formbuilder').append(template({
        widgets: window._formWidgets,
        itm: itm,
        idx: idx,
    }));
    $.each($('#' + window.formFieldsId + '_formbuilder td'), function (key, value) {
        $(value).css('width', $(value).outerWidth() + 'px');
    });
    $('#' + window.formFieldsId + '_add').val('');
    $('#' + window.formFieldsId + '_add').trigger("chosen:updated");
    $('#' + window.formFieldsId + '_formbuilder .wgt').chosen({
        allow_single_deselect: true
    });
};

function assemble() {
    var json = [];
    $.each($('#' + window.formFieldsId + '_formbuilder tbody'), function(key, value) {
        json.push({
            widget: $(value).find('.wgt').val(),
            label: $(value).find('.lbl').val(),
            id: $(value).find('.id').val(),
            required: $(value).find('.req').is(':checked') ? 1 : 0,
            sql: $(value).find('.sql').val(),
        });
    });
    $('#' + window.formFieldsId).val(JSON.stringify(json));
};