"use strict";
require('../../pz/css/nestable.css');

require("./main.js");
require('nestable');

$(function() {
    if (_listType == 2) {

        //closed
        $(document).on('click', '.dd-item button', function () {
            $.ajax({
                type: 'GET',
                url: '/pz/ajax/nestable/closed',
                data: 'id=' + $(this).parent().data('id') + '&closed=' + ($(this).parent().hasClass('dd-collapsed') ? 1 : 0) + '&model=' + $('.js-model-wrapper').data('modelname') ,
                success : function(msg) {

                }
            });
        });

        $('#nestable').nestable({ group: 1 }).on('change', update);
    }
});

function update() {
    var root = {
        id: 0,
        children: $('#nestable').nestable('serialize'),
    }

    if (typeof window._ajax != 'undefined') {
        window._ajax.abort()
    }

    window._ajax = $.ajax({
        type: 'GET',
        url: '/pz/ajax/nestable/sort',
        data: 'model=' + $('.js-model-wrapper').data('modelname') + '&data=' + encodeURIComponent(JSON.stringify(toArray(root))),
        success : function(msg) {
        }
    });
};

function toArray(node) {
    var result = [];
    for (var idx in node.children) {
        var itm = node.children[idx];
        result.push({
            id: itm.id,
            parentId: node.id,
            rank: idx,
        });
        result = result.concat(toArray(itm));
    }
    return result;
};