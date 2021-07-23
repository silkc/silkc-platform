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


    initSelectTypeSearch = () => {
        let checkbox = $('.checkbox-search input[name="search-by"]');
        let blcSearch = $('.blc-search-by');
        checkbox.on('change', function () {
            let typeSearch = this.value;
            if (typeSearch) {
                blcSearch.hide();
                $(typeSearch).show();
            }
        });

        window.addEventListener('resize', function () {
            let widthWindow = $(window).outerWidth();
            if(widthWindow > 767) {
                blcSearch.show();
                $('.checkbox-search input[name="search-by"][value="#blc-search-by-occupation"]').prop('checked', true);
            }
            if(widthWindow < 768) {
                $('#blc-search-by-occupation').show();
                $('#blc-search-by-skill').hide();
                $('.checkbox-search input[name="search-by"][value="#blc-search-by-occupation"]').prop('checked', true);
            }
        });
    }


    carouselSearch = () => {
        let divs = $('#search .slider-img div');
        let cpt = 0;
        setInterval(function() {
            cpt++;
            if (cpt >= divs.length ) cpt = 0;
            divs.fadeOut(2000);
            divs.eq(cpt).fadeIn(1000);
        }, 10000);
    }

    init = function() {
        this.initSelectTypeSearch();
        this.carouselSearch();
    }
}


$(document).ready(function() {
    var search = new Search();
    search.init();
});
