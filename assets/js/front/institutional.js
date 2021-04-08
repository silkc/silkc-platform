//import $ from 'jquery';
const $ = require('jquery');
const doT = require("dot");

//import '../../css/bootstrap-extended.css';

// any CSS you import will output into a single css file (app.css in this case)
import '../../scss/elements/header.scss';
import '../../scss/institutional.scss';

require('bootstrap');
//require('popper');
var moment = require('moment');
require('chart.js');
require('@fortawesome/fontawesome-free/js/all.min');

class Institutional {
    instanceProperty = "Institutional";
    boundFunction = () => {
        return this.instanceProperty;
    }
    
    /**
     * Add new training
     */
    dotjs = () => {    
        doT.templateSettings = {
            evaluate:    /\[\[([\s\S]+?)\]\]/g,
            interpolate: /\[\[=([\s\S]+?)\]\]/g,
            encode:      /\[\[!([\s\S]+?)\]\]/g,
            use:         /\[\[#([\s\S]+?)\]\]/g,
            define:      /\[\[##\s*([\w\.$]+)\s*(\:|=)([\s\S]+?)#\]\]/g,
            conditional: /\[\[\?(\?)?\s*([\s\S]*?)\s*\]\]/g,
            iterate:     /\[\[~\s*(?:\]\]|([\s\S]+?)\s*\:\s*([\w$]+)\s*(?:\:\s*([\w$]+))?\s*\]\])/g,
            varname: 'it',
            strip: true,
            append: true,
            selfcontained: false
        };
    }
    
    /**
     * Add new training
     */
    addTraining = () => {    
        $('body').on('click', '#add-training', function(e) {
            e.preventDefault();
    
            let template = document.getElementById('institutional-training').innerHTML;
            var compiledTemplate = doT.template(template);

            let id = $('body').find('.list-trainings > form').length;
            let name = $('#training-input').val();
            let json = {id : id, name : name};

            var result = compiledTemplate(json);
            $(result).prependTo('.list-trainings');
            $('#training-input').val('');
        });
    }

    /**
     * Duplicate a training
     */
    duplicateTraining = () => {

        $('body').on('click', '.list-trainings .clone', function(e) {
            e.preventDefault();
    

        });
    }


    init = function() {
        this.dotjs();
        this.addTraining();
        this.duplicateTraining();
    }
}


$(document).ready(function() {
    var institutional = new Institutional();
    institutional.init();
});
