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
        let table = $('#datatable-subject').DataTable({
            searching: false, 
            info: false,
            lengthChange: false,
            select: true,
            columns: [
                {
                    width: "50px",
                    orderable: false,
                    render: function ( data, type, row ) {
                        if ( type === 'display' ) {
                            return '<input type="checkbox" class="editor-active">';
                        }
                        return data;
                    },
                    className: "dt-body-center"
                },
                { data: 'date'},
                { data: 'subject'}
            ],
            select: {
                style:    'multi',
                selector: 'td:not(:first-child)' // no row selection on last column
            },
            order: [[ 1, 'asc' ]]
        });

        $('#datatable-subject').on('change', 'input.editor-active', function () {
            let idx = $(this).closest('tr').index();
            if ($(this).is(':checked'))
                table.row(':eq(' + idx + ')', { page: 'current' }).select(); 
            else
                table.row(':eq(' + idx + ')', { page: 'current' }).deselect(); 
        });

        $(".selectAll").on("click", function(e) {
            if ($(this).is(":checked")) {
                table.rows().select();
                table.rows().every( function ( rowIdx, tableLoop, rowLoop ) {
                    let node = this.node();
                    console.log('node >> ' , node)
                    $(node).find('input.editor-active').prop('checked', true);
                }); 
            } else {
                table.rows().deselect(); 
                table.rows().every( function ( rowIdx, tableLoop, rowLoop ) {
                    let node = this.node();
                    console.log('node >> ' , node)
                    $(node).find('input.editor-active').prop('checked', false);
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

    init = function() {
        this.runDatatableHome();
        this.runDatatableWork();
    }
}





$(document).ready(function() {
    let admin = new Admin();
    admin.init();
});