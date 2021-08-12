import $ from 'jquery';
var Slider = require("bootstrap-slider");
require('bootstrap-slider/dist/css/bootstrap-slider.min.css');

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
                } else {
                    $(_this).attr('disabled', false);
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
                } else {
                    $(_this).attr('disabled', false);
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
                
                if (/^[\],:{}\s]*$/.test(coords.replace(/\\["\\\/bfnrtu]/g, '@').
                replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g, ']').
                replace(/(?:^|:|,)(?:\s*\[)+/g, ''))) {
                    
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
                
                } else {
                    blcMap.innerHTML = coords;
                }
            }

        })
   }


   runKeepSearch = () => { 

        $('body').on('change', '#search-save-results', function (e) {

            let url = '/api/toggle_user_searches_param';
            let token = $('body').attr('data-token');
            let data = {};

            $.ajax({
                url: url,
                type: "POST",
                data: data,
                dataType: 'json',
                headers: {"X-auth-token": token},
                success: function (data, textStatus, jqXHR) {
                    if (data.result) {
                        $('#history-search').show();
                    } else {
                        $('#history-search').hide();
                    }
                },
                error: function () {
                    bootbox.alert('An error occured');
                }
            });
        })
   }


    sliderSearch = () => { 
        
        let sliderDistance;
        let sliderPrice;

        // PRIX
        let initSliderPrice = function() {
            sliderPrice = new Slider('#formControlRangePrice', {
                formatter: function(value) {
                    let devise = $('#devise').val();
                    return value + devise;
                }
            });
            sliderPrice.on("change", function(slideEvt) {
                let min = slideEvt.newValue[0];
                let max = slideEvt.newValue[1];
                let devise = $('#devise').val();

                $("#priceValMin > span:first-child").text(min);
                $("#priceValMax > span:first-child").text(max);
                $("#priceValMin > span:last-child").text(devise);
                $("#priceValMax > span:last-child").text(devise);
                $("#minPrice").val(min);
                $("#maxPrice").val(max);
            });
        }
        
        // DISTANCE
        let initSliderDistance = function() {
            sliderDistance = new Slider('#formControlRangeDistance', {
                formatter: function(value) {
                    return value + ' km';
                }
            });
            sliderDistance.on("change", function(obj) {
                $("#distanceVal").text(obj.newValue + 'km');
                $("#distance").val(obj.newValue);
            });
        }

        $('#devise').on('change', function() {
            $("#priceValMin > span:last-child").text($(this).val());
            $("#priceValMax > span:last-child").text($(this).val());

            let max = sliderPrice.element.dataset.sliderMax;
            $("#priceValMin > span:first-child").text(0);
            $("#priceValMax > span:first-child").text(max);

            $("#minPrice").val(0);
            $("#maxPrice").val(max);

            sliderPrice.destroy();
            initSliderPrice();
        });

        // Clear filter
        $('body').on('click', 'button.btn-clear', function() {
            sliderDistance.setValue(0);
            sliderPrice.setValue([0, 5000]);
            //sliderPrice.destroy();

            // Distance - ville
            $("#city").val('');
            $("#inputCity").val('');
            $("#distanceVal").text('0km');
            $("#distance").val(0);
            
            // Prix
            let max = sliderPrice.element.dataset.sliderMax;
            $("#priceValMin > span:first-child").text(0);
            $("#priceValMax > span:first-child").text(max);
            
            $("#minPrice").val(0);
            $("#maxPrice").val(max);
            
            // Input text, date ...
            $("#advanced-search input[type=date], #advanced-search input[type=text], #advanced-search input[type=datetime-local]").val("");
            
            // Checkbox
            $("#advanced-search input[type=checkbox]").prop("checked", false);
        });

        initSliderDistance(0);
        initSliderPrice();
    }

    
    /**
     * Affichage carte
     */
     runMapFilter = () => { 

        var map = L.map('map').setView([0, 0], 2);
        let geocoder = L.Control.Geocoder.nominatim();
        let inputHidden = document.getElementById('city');
        let inputHiddenCity = document.getElementById('inputCity');

        let control = L.Control.geocoder({
            collapsed: false,
            placeholder: 'Search here...',
            position: 'topleft',
            geocoder: geocoder
        }).on('markgeocode', function(e) {
            if (e.geocode && e.geocode.center) {
                let lat = e.geocode.center.lat;
                let lng = e.geocode.center.lng;
                let name = e.geocode.name;
                
                let newCoords = {
                    "lat": lat,
                    "lng": lng
                };
                newCoords = JSON.stringify(newCoords);

                let leafletControlGeocoderForm = document.querySelector('#search-results .leaflet-control-geocoder-form input');
                leafletControlGeocoderForm.value = name;

                if (inputHidden) inputHidden.value = newCoords;
                if (inputHiddenCity) inputHiddenCity.value = name;
            }
        }).addTo(map);
        
        // Créer l'objet "map" et l'insèrer dans l'élément HTML qui a l'ID "map"
        // Leaflet ne récupère pas les cartes (tiles) sur un serveur par défaut. Nous devons lui préciser où nous souhaitons les récupérer. Ici, openstreetmap.fr
        /*L.tileLayer('https://{s}.tile.openstreetmap.fr/osmfr/{z}/{x}/{y}.png', {
            attribution: '',
            minZoom: 1,
            maxZoom: 20
        }).addTo(map);*/
        
        document.getElementById('searchmap').appendChild(document.querySelector('#search-results .leaflet-control-geocoder.leaflet-bar'));

        if (inputHiddenCity) {
            let leafletControlGeocoderForm = document.querySelector('#search-results .leaflet-control-geocoder-form input');
            leafletControlGeocoderForm.value = inputHiddenCity.value;
        }
    }

    init = function() {
        this.runTypeSearch();
        this.setScore();
        this.runDonetraining();
        this.runMap();
        this.runKeepSearch();
        this.sliderSearch();
        this.runMapFilter();

        $('[data-toggle="tooltip"]').tooltip;
    }
}


$(document).ready(function() {
    var searchResults = new SearchResults();
    searchResults.init();
});
