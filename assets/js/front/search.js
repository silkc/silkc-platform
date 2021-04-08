//import $ from 'jquery';
const $ = require('jquery');

//import '../../css/bootstrap-extended.css';

// any CSS you import will output into a single css file (app.css in this case)
import '../../scss/elements/header.scss';
import '../../scss/search.scss';

require('bootstrap');
//require('popper');
var moment = require('moment');
require('chart.js');
require('@fortawesome/fontawesome-free/js/all.min');

class Search {
    instanceProperty = "Search";
    boundFunction = () => {
        return this.instanceProperty;
    }

    init = function() {
    }
}


$(document).ready(function() {
    var search = new Search();
    search.init();
});
