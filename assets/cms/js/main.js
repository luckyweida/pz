"use strict";

require('../../inspinia/css/bootstrap.min.css');
require('../../inspinia/css/animate.css');
require('../../inspinia/css/style.css');
require('../../inspinia/css/plugins/iCheck/custom.css');
require('../../inspinia/css/plugins/sweetalert/sweetalert.css');

require('../../font-awesome/css/font-awesome.css');
require('../../pz/css/chosen.css');
require('../../pz/css/style.css');

const $ = require("jquery");
window.$ = $;
window.jQuery = $

require('jquery-ui');
require('jquery-ui/ui/widgets/sortable');
require('jquery-ui/ui/disable-selection');

require('bootstrap');
require('metismenu');
require('pace');
require('icheck');

require('jquery-slimscroll');
require('jquery-validation');

require('sweetalert');
require('chosen-js');
require('datetimepicker-jquery');
var Cookies = require('js-cookie');
window.Cookies = Cookies;

var Handlebars = require('handlebars');
window.Handlebars = Handlebars;

require('../../inspinia/js/inspinia.js');
require('./function.js');



$(function() {
    $('.navbar-minimalize').off();
    $(document).on('click', '.navbar-minimalize', function(ev) {
        $('body').toggleClass('mini-navbar');
        if ($('body').hasClass('mini-navbar')) {
            Cookies.set('miniNavbar', 1);
        } else {
            Cookies.remove('miniNavbar');
        }
        return false;
    });

    $.each($('.js-sort-column'), function (idx, itm) {

        $.each($(itm).find('td'), function (key, val) {
            $(val).css('width', $(val).outerWidth() + 'px');
        });

        $(itm).sortable({
            stop: function (event, ui) {
                $.ajax({
                    type: 'GET',
                    url: '/pz/ajax/column/sort',
                    data: 'data=' + encodeURIComponent(JSON.stringify($(itm).sortable("toArray"))) + '&className=' + encodeURIComponent($(itm).data('classname')),
                    success: function (msg) {
                    }
                });
            }
        });
    });

    $(document).on('click', '.js-status', function() {
        var result = [];
        $.each($(this).find('.js-status-opt'), function (idx, itm) {
            result.push({
                color: $(itm).data('color'),
                icon: $(itm).data('icon'),
            });
        });

        var status = $(this).data('status');
        var nextStatus = (status + 1) % result.length;
        $(this).data('status', nextStatus);

        $.ajax({
            type: 'GET',
            url: '/pz/ajax/status',
            data: 'id=' + encodeURIComponent($(this).data('id'))  + '&className=' + encodeURIComponent($(this).data('classname') ? $(this).data('classname') : $(this).closest('tbody').data('classname')) + '&status=' + encodeURIComponent(nextStatus),
            success: function (msg) {
            }
        });


        $(this).attr('class', 'js-status isactive btn btn-xs btn-circle ' + result[nextStatus].color);
        $(this).find('i').attr('class', 'fa ' + result[nextStatus].icon);
        return false;
    });

    $(document).on('click', '.js-delete', function(ev) {
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
                delFunc(_this);
            }
        });
        return false;
    });
});

function delFunc(elem) {
    $.ajax({
        type: 'GET',
        url: '/pz/ajax/delete',
        data: 'id=' + encodeURIComponent($(elem).data('id')) + '&className=' + encodeURIComponent($(elem).data('classname') ? $(elem).data('classname') : $(elem).closest('tbody').data('classname')),
        success: function (msg) {
            swal({
                title: "Deleted",
                text: "Your data has been deleted.",
                icon: 'success',
                timer: 1000,
                buttons: false
            });

            setTimeout(function () {
                if ($(elem).closest('.dd-item').length) {
                    if ($(elem).closest('.dd-list').find('.dd-item').length == 1) {
                        $(elem).closest('.dd-list').remove();
                    } else {
                        $(elem).closest('.dd-item').remove();
                    }
                } else {
                    $(elem).closest('.content-container').remove();
                }
            }, 800)
        }
    });
};