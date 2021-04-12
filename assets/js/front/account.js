import $ from 'jquery';
//import '../../css/bootstrap-extended.css';

// any CSS you import will output into a single css file (app.css in this case)
import '../../scss/elements/header.scss';
import '../../scss/account.scss';

require('bootstrap');
//require('popper');
var moment = require('moment');
require('chart.js');
require('@fortawesome/fontawesome-free/js/all.min');

class Account {
    instanceProperty = "Account";
    boundFunction = () => {
        return this.instanceProperty;
    }

    runDetail = () => {
        $('body').on('click', '.list-job .detail', function(e) {
            e.preventDefault();

            let $this = $(this);

            let name = $this.attr('data-name');
            let description = $this.attr('data-description');
            let $modal = $('#common-modal');

            if ($modal) {
                $modal.find('.modal-title').html(name);
                $(`<p>${description}</p>`).appendTo($modal.find('.modal-body'));
                $('#common-modal').modal('show');
            }
        });
    }

    init = function() {
        this.runDetail();
    }
}

$(document).ready(function() {
    let account = new Account();
    account.init();
});
