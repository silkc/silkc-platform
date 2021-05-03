//import $ from 'jquery';
import $ from 'jquery';
import doT from 'dot';

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

            let tpl = document.getElementById('tpl_training');
            if (!tpl) return false;
            let tempFn = doT.template(tpl.textContent);
            let id = $('body').find('#list-trainings').children().length;
            let name = $('#training-input').val();
            let json = {k : id, name : name};
            $('body').find('#list-trainings .collapse').collapse('hide');
            let html = tempFn( json );
            $(html).prependTo('#list-trainings');
            $('#training-input').val('');

            // Trigger pour reinitialiser la recherche autocomplete dans le fichier main.js
            const event = new Event('newTraining');
            document.dispatchEvent(event);
        });
    }

    /**
     * Duplicate a training
     */
    duplicateTraining = () => {
        $('body').on('click', '#list-trainings .clone', function(e) {
            e.preventDefault();

            let _this = this;
            $(_this).hide().siblings('.loader').css('display', 'inline-block');
            let id = $(_this).attr('data-id');
            if (!id) return false;

            let baseUrl = '/duplicate_training';
            let params = $.param({'training_id': id});

            let url = `${baseUrl}?${params}`;
            if (url) {
                $.ajax({
                    type: "GET",
                    url: url,
                    success: function (data, textStatus, jqXHR) {
                        if (!data || data == undefined) return false;
                        data = JSON.parse(data);
                        let json = data.shift();
                        let tpl = document.getElementById('tpl_training');
                        if (!tpl) return false;
                        let tempFn = doT.template(tpl.textContent);
                        json.k = $('body').find('#list-trainings').children().length;
                        $('body').find('#list-trainings .collapse').collapse('hide');
                        let html = tempFn( json );
                        $(html).prependTo('#list-trainings');

                        // Trigger pour reinitialiser la recherche autocomplete dans le fichier main.js
                        const event = new Event('newTraining');
                        document.dispatchEvent(event);
                    },
                    error : function(jqXHR, textStatus, errorThrown){},
                    complete : function(jqXHR, textStatus ){
                        $(_this).css('display', 'inline-block').siblings('.loader').hide();
                    }
                });
            } else {
                $(_this).css('display', 'inline-block').siblings('.loader').hide();
            }
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
