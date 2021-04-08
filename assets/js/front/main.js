import $ from 'jquery';
//import '../../css/bootstrap-extended.css';

// any CSS you import will output into a single css file (app.css in this case)
import '../../scss/elements/header.scss';
import '../../scss/app.scss';

require('bootstrap');
//require('popper');
var moment = require('moment');
require('chart.js');
require('@fortawesome/fontawesome-free/js/all.min');

class Main {
    instanceProperty = "Main";
    boundFunction = () => {
        return this.instanceProperty;
    }

    static init = function() {

    }
}

$(document).ready(function() {
    Main.init();
});
