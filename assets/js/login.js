import $ from 'jquery';
require('bootstrap-datepicker');
require('bootstrap');


// any CSS you import will output into a single css file (app.css in this case)
import '../scss/login.scss';
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
     * Carousel
     */
    runCarousel = () => {

        let idxImg = 0;
        let idxSlogans = 0;

        let images = $('body').find('#carousel img');
        let imagesLength = images.length;

        let slogans = $('body').find('#carousel .slogans > div');
        let slogansLength = slogans.length;
        setInterval(function() {
            idxImg++;
            idxSlogans++;
            if (idxImg == imagesLength) idxImg = 0;
            if (idxSlogans == slogansLength) idxSlogans = 0;
            images.fadeOut(500);
            slogans.fadeOut(500);
            setTimeout(function () {
                images.eq(idxImg).fadeIn(1000);
                slogans.eq(idxSlogans).fadeIn(1000);
            }, 500);
        }, 8000);

    }

    init = function() {
        this.runCarousel();
    }
}





$(document).ready(function() {
    let login = new Login();
    login.init();
});