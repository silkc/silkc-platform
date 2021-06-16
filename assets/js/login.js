import $ from 'jquery';
require('bootstrap');


// any CSS you import will output into a single css file (app.css in this case)
import '../scss/login.scss';


//require('popper');
var moment = require('moment');
require('chart.js');
require('@fortawesome/fontawesome-free/js/all.min');

class Login {
    instanceProperty = "Login";
    boundFunction = () => {
        return this.instanceProperty;
    }


    /**
     * Tabs signup
     */
     runTabs = () => {

        $('body.signup').on('click', '.tabs-signup a', function (e) {
            e.preventDefault();
            $('body.signup .tabs-signup a').removeClass('active');
            let $formUser = $('#form-user');
            let $formInstitution = $('#form-institution');
            let href = $(this).attr('href');
            $(this).addClass('active');
            if (href == '#form-user') {
                $formInstitution.slideUp(500, function () {
                    $(href).slideDown(1000);
                });
            }
            if (href == '#form-institution') {
                $formUser.slideUp(500, function() {
                    $(href).slideDown(1000);
                });
            }
            $(this).closest('.tabs-signup').addClass('active');
        });
    }

    init = function() {
        this.runTabs();
    }
}





$(document).ready(function() {
    let login = new Login();
    login.init();
});