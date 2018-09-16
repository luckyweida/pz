"use strict";
require('../css/nestable.css');

require("./main.js");
require('nestable');

$(function() {
    if (_listType == 2) {
        $('#nestable').nestable({ group: 1 }).on('change', update);
    }
});

function update() {
    var root = {
        id: 0,
        children: $('#nestable').nestable('serialize'),
    }
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