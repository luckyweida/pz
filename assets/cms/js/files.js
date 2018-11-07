"use strict";
require("./main.js");


require('../../redactor/redactor/redactor.css');
require('../../inspinia/css/plugins/jsTree/style.min.css');
require('jquery.fancybox/source/jquery.fancybox.css');
require('animate.css/animate.css');


require('../../redactor/redactor/redactor.js');
require('../../redactor/plugins/table.js');
require('../../redactor/plugins/video.js');

var jstree = require('jstree/dist/jstree.js');
window.jstree = jstree;

require('../../inspinia/js/plugins/slimscroll/jquery.slimscroll.min.js');

require('blueimp-file-upload/js/jquery.iframe-transport.js');
require('blueimp-file-upload/js/jquery.fileupload.js');
// require('jquery.fancybox');

require('./fm.js');

$(function() {
    fm.init({
        mode: 0,
        modelName: null,
        attributeName: null,
        ormId: null,
    });
});