//import $ from 'jquery';
const $ = require('jquery');

//import '../../css/bootstrap-extended.css';

// any CSS you import will output into a single css file (app.css in this case)
import '../../scss/elements/header.scss';
import '../../scss/search_results.scss';

require('bootstrap');
//require('popper');
var moment = require('moment');
require('chart.js');
require('@fortawesome/fontawesome-free/js/all.min');

class SearchResults {
    instanceProperty = "SearchResults";
    boundFunction = () => {
        return this.instanceProperty;
    }

    /**
     * Selection du type de recherche (occupation/skill)
     */
    runTypeSearch = () => {

        let $formOccupation = $('#search-results .form-search-occupation');
        let $formSkill = $('#search-results .form-search-skill');

        $('body').on('change', 'select[name="type_search"]', function() {

            $formOccupation.find('.input-autocomplete').val('');
            $formOccupation.find('input[type="hidden"]').val('');
            $formSkill.find('.input-autocomplete').val('');
            $formSkill.find('input[type="hidden"]').val('');

            if (this.value == 'occupation') {
                $formSkill.hide();
                $formOccupation.show();
            }
            if (this.value == 'skill') {
                $formOccupation.hide();
                $formSkill.show();
            }

            $(this).closest('form').find('.disabled-search').prop('disabled', true);
        }); 
    }

    /**
     * Actions utilisateur lors de la recherche d'une formation (score)
     */
     setScore = () => {

        // Collapse show
        $('#search-results #accordion').on('show.bs.collapse', function (e) {
            let card = $(e.target).closest('.card');
            let id = card.attr('data-id');

            let params = $.param({'id': id, 'score': 50});
            let url = `/set_score?${params}`;

            $.ajax({
                type: "GET",
                url: url,
                async: true,
                success: function (data, textStatus, jqXHR) {}
            });
        })

        // More info
        $('body').on('click', '#search-results #accordion .btn-more', function (e) {
            let card = $(this).closest('.card');
            let id = card.attr('data-id');

            let params = $.param({'id': id, 'score': 100});
            let url = `/set_score?${params}`;

            $.ajax({
                type: "GET",
                url: url,
                async: true,
                success: function (data, textStatus, jqXHR) {}
            });
        })
    }

    init = function() {
        this.runTypeSearch();
        this.setScore();
    }
}


$(document).ready(function() {
    var searchResults = new SearchResults();
    searchResults.init();
});
