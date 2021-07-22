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
    }
}


$(document).ready(function() {
    var history = new History();
    history.init();
});
