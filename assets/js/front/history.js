import $ from 'jquery';
require('bootstrap');
const bootbox = require('bootbox/bootbox');

import 'datatables.net';
import 'datatables.net-select-dt';
import 'datatables.net-dt/css/jquery.dataTables.min.css';
import 'datatables.net-select-dt/css/select.dataTables.min.css';

// any CSS you import will output into a single css file (app.css in this case)
import '../../scss/elements/header.scss';

var moment = require('moment');
require('chart.js');
require('@fortawesome/fontawesome-free/js/all.min');
require('bootstrap-select');

class History {
    instanceProperty = "History";
    boundFunction = () => {
        return this.instanceProperty;
    }

    runDeleteHistory = () => {
        $('body').on('click', 'button.delete-search-history', function() {
            let $button = $(this);
            let id = $button.attr('data-id');
            let type = $button.attr('data-type');
            let url = '/api/delete_search_history';
            let token = $('body').attr('data-token');

            let data = {};
            data.id = id;
            data.type = type;

            bootbox.confirm({message : 'Are you sure you want to delete this item?', buttons : { cancel : { label : 'Cancel'}, confirm : { label : 'Yes'}}, callback : function(result) {
                    if (result == true) {
                        $button.html('<i class="fas fa-spinner fa-spin"></i>');
                        $.ajax({
                            url: url,
                            type: "POST",
                            dataType: 'json',
                            data: data,
                            headers: {"X-auth-token": token},
                            success: function (data, textStatus, jqXHR) {
                                if (data.result != undefined && data.result == true) {
                                    $('#datatable-search_history').DataTable()
                                        .row( $button.closest('tr') )
                                        .remove()
                                        .draw();
                                    
                                } else {
                                    bootbox.alert('An error occured');
                                }
                            }
                        });
                    }
                }});
        });
    }

    runDatatableHistory = () => {
        let table = $('#datatable-search_history').DataTable({
            searching: true, 
            info: false,
            lengthChange: false,
            columnDefs: [
                {targets: [5], orderable: false},
            ],
            order: [[ 1, 'asc' ]]
        });
    }

    init = function() {
        this.runDatatableHistory();
        this.runDeleteHistory();
    }
}


$(document).ready(function() {
    var history = new History();
    history.init();
});
