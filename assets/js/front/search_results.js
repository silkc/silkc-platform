//import $ from 'jquery';
const $ = require('jquery');

//import '../../css/bootstrap-extended.css';

// any CSS you import will output into a single css file (app.css in this case)
import '../../scss/elements/header.scss';
import '../../scss/search_results.scss';

require('bootstrap');
const bootbox = require('bootbox/bootbox');
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
                success: function (data, textStatus, jqXHR) {}
            });
        })
    }


    runDonetraining = () => { 

       $('body').on('click', '#search-results #accordion .btn-done', function() {

            let _this = this;
            $(_this).attr('disabled', true);

            let token = $('body').attr('data-token');
            let id = $(this).attr('data-id');
            let url = '/api/done_training/' + id;
           
            bootbox.confirm({message : 'Can you confirm that you have completed this training?', buttons : { cancel : { label : 'Cancel'}, confirm : { label : 'Yes'}}, callback : function(result) {
                if (result == true) {
                    $.ajax({
                        url: url,
                        type: "POST",
                        dataType: 'json',
                        data: {},
                        headers: {"X-auth-token": token},
                        success: function (data, textStatus, jqXHR) {
                            if (data.result != undefined && data.result == true) {
                                $(_this).removeClass('btn-done').addClass('btn-notdone');
                                $(_this).removeClass('btn-success').addClass('btn-warning');
                                $(_this).html('I did not do this training');
                            } else {
                                bootbox.alert('An error occured');
                            }
                        },
                        error : function(resultat, statut, erreur){
                            bootbox.alert('An error occured');
                        },
                        complete: function () {
                            $(_this).attr('disabled', false);
                        }
                    });
                }
            }});
       });

       $('body').on('click', '#search-results #accordion .btn-notdone', function() {

           let _this = this;
           $(_this).attr('disabled', true);

           let token = $('body').attr('data-token');
           let id = $(this).attr('data-id');
           let url = '/api/undone_training/' + id;
                      
            bootbox.confirm({message : 'Can you confirm that you did not take this training?', buttons : { cancel : { label : 'Cancel'}, confirm : { label : 'Yes'}}, callback : function(result) {
                if (result == true) {
                    $.ajax({
                        url: url,
                        type: "POST",
                        dataType: 'json',
                        data: {},
                        headers: {"X-auth-token": token},
                        success: function (data, textStatus, jqXHR) {
                            if (data.result != undefined && data.result == true) {
                                $(_this).removeClass('btn-notdone').addClass('btn-done');
                                $(_this).removeClass('btn-warning').addClass('btn-success');
                                $(_this).html("I've done this training");
                            } else {
                                bootbox.alert('An error occured');
                            }
                        },
                        error : function(resultat, statut, erreur){
                            bootbox.alert('An error occured');
                        },
                        complete: function () {
                            $(_this).attr('disabled', false);
                        }
                    });
                }
            }});
       });
   }


   runMap = () => { 

        $('#search-results #accordion').on('shown.bs.collapse', function (e) {
            let blcMap = e.target.querySelector('.blc-map');
            if (blcMap) {
                let mapContent = blcMap.querySelector('.map');
                let trainingAddress = blcMap.querySelector('.training_address');
                let trainingAddressHidden = blcMap.querySelector('.training_address_hidden');

                if (mapContent.innerHTML != '') return false;

                let map = null;
                let coords = trainingAddressHidden.value;
    
                if (!coords) return false;

                coords = JSON.parse(coords);
                map = L.map(mapContent).setView([coords.lat, coords.lng], 10);
                
                let geocoder = L.Control.Geocoder.nominatim();
                
                let control = L.Control.geocoder({
                    collapsed: false,
                    placeholder: 'Search here...',
                    position: 'topleft',
                    geocoder: geocoder
                }).addTo(map);
                
                // Créer l'objet "map" et l'insèrer dans l'élément HTML qui a l'ID "map"
                // Leaflet ne récupère pas les cartes (tiles) sur un serveur par défaut. Nous devons lui préciser où nous souhaitons les récupérer. Ici, openstreetmap.fr
                L.tileLayer('https://{s}.tile.openstreetmap.fr/osmfr/{z}/{x}/{y}.png', {
                    attribution: '',
                    minZoom: 1,
                    maxZoom: 20
                }).addTo(map);
                
                //document.getElementById('searchmap').appendChild(document.querySelector('.leaflet-control-geocoder.leaflet-bar'));
    
                if (coords) {
                    let marker = L.marker([coords.lat, coords.lng]).addTo(map); // Markeur
                    marker.bindPopup(coords.city); // Bulle d'info
    
                    trainingAddress.innerHTML = coords.city;
                }
            }

        })
   }

    init = function() {
        this.runTypeSearch();
        this.setScore();
        this.runDonetraining();
        this.runMap();
    }
}


$(document).ready(function() {
    var searchResults = new SearchResults();
    searchResults.init();
});
