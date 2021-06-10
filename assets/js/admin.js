import $ from 'jquery';
require('bootstrap');

import 'datatables.net';
import 'datatables.net-select-dt';
import 'datatables.net-dt/css/jquery.dataTables.min.css';
import 'datatables.net-select-dt/css/select.dataTables.min.css';

// any CSS you import will output into a single css file (app.css in this case)
import '../scss/elements/header.scss';
import '../scss/admin.scss';


//require('popper');
var moment = require('moment');
require('chart.js');
require('@fortawesome/fontawesome-free/js/all.min');

class Admin {
    instanceProperty = "Admin";
    boundFunction = () => {
        return this.instanceProperty;
    }

    /**
     * Affichage des messages de mises à jour
     */
    runDatatableHome = () => {
        let notificationsIds = [];
        let setInputHidden = function (ids = []) {
            if (ids.length > 0)
                $('.btn-read').prop('disabled', false);
            else
                $('.btn-read').prop('disabled', true);

            $('#notifications-read').val(JSON.stringify(ids));
        };
        let tableNotification = $('#datatable-subject').DataTable({
            searching: false, 
            info: false,
            lengthChange: false,
            select: {
                style:    'multi',
                selector: 'td:not(:first-child)' // no row selection on last column
            },
            columnDefs: [
                { targets: [0], orderable: false}
            ],
            order: [[ 1, 'asc' ]]
        });

        $('#datatable-subject').on('change', 'input.editor-active', function () {
            let tr = $(this).closest('tr');
            let notificationId = tr.attr('data-id');
            let idx = tr.index();
            if ($(this).is(':checked')) {
                tableNotification.row(':eq(' + idx + ')', { page: 'current' }).select(); 
                if (notificationsIds.indexOf(notificationId) == -1) {
                    notificationsIds.push(notificationId);
                }
                setInputHidden(notificationsIds);
            } else {
                tableNotification.row(':eq(' + idx + ')', { page: 'current' }).deselect(); 
                if (notificationsIds.indexOf(notificationId) != -1) {
                    notificationsIds.splice(notificationsIds.indexOf(notificationId), 1); 
                }
                setInputHidden(notificationsIds);
            }
        });

        $(".selectAll").on("click", function(e) {
            if ($(this).is(":checked")) {
                let cpt = 0;
                let totalRowCount = tableNotification.data().length;
                tableNotification.rows().select();
                tableNotification.rows().every( function ( rowIdx, tableLoop, rowLoop ) {
                    let node = this.node();
                    $(node).find('input.editor-active').prop('checked', true);
                    let tr = $(node);
                    let notificationId = tr.attr('data-id');
                    if (notificationsIds.indexOf(notificationId) == -1) {

                        console.log('notificationsIds >> ', notificationsIds)
                        console.log('notificationId >> ', notificationId)

                        notificationsIds.push(notificationId);
                    }
                    cpt++;
                    if (cpt == totalRowCount) {
                        setInputHidden(notificationsIds);
                    }
                }); 
            } else {
                let cpt = 0;
                let totalRowCount = tableNotification.data().length;
                tableNotification.rows().deselect(); 
                tableNotification.rows().every( function ( rowIdx, tableLoop, rowLoop ) {
                    let node = this.node();
                    $(node).find('input.editor-active').prop('checked', false);
                    let tr = $(node);
                    let notificationId = tr.attr('data-id');
                    if (notificationsIds.indexOf(notificationId) != -1) {
                        notificationsIds.splice(notificationsIds.indexOf(notificationId), 1); 
                    }
                    cpt++;
                    if (cpt == totalRowCount) {
                        setInputHidden(notificationsIds);
                    }
                });
            }
        });
    }

    /**
     * Affichage des messages de mises à jour
     */
     runDatatableWork = () => {
        let table = $('#datatable-work').DataTable({
            searching: false, 
            info: false,
            lengthChange: false,
            columnDefs: [
                { targets: [4], orderable: false}
            ],
            order: [[ 1, 'asc' ]]
        });

        $('.selectAll').on('click', function(e) {
            if ($(this).is(':checked'))
                table.rows().select(); 
            else
                table.rows( ).deselect();
        });
    }

    /**
     * Affichage detail de la formation dans une modal
     */
     seeDetailWork = () => {

        $('body').on('click', '#content-work a.see-detail', function(e) {
            e.preventDefault();

            let name = $(this).attr('data-name');
            let description = $(this).attr('data-description');
            let $modal = $('#common-modal');

            if ($modal) {
                $modal.find('.modal-title').html(name);
                $(`<p>${description}</p>`).appendTo($modal.find('.modal-body'));
                $('#common-modal').modal('show');
            }
        });

        $('#common-modal').on('hidden.bs.modal', function (e) {
            $(this).find('.modal-title').children().remove();
            $(this).find('.modal-body').children().remove();
        });
    }

    init = function() {
        this.runDatatableHome();
        this.runDatatableWork();
        this.seeDetailWork();

        $('[data-toggle="tooltip"]').tooltip();
    }
}





$(document).ready(function() {
    let admin = new Admin();
    admin.init();
});