import $ from 'jquery';
import '../scss/app.scss';

require('bootstrap');

//require('popper');
var moment = require('moment');
require('chart.js');
require('@fortawesome/fontawesome-free/js/all.min');


class Map { 
    instanceProperty = "Map";
    boundFunction = () => {
        return this.instanceProperty;
    }

    initMap = () => {
        let map;
        let service;
        let tabResults = [];
        let marker = null;

        let $modal = $('#modal-address');
        let $modalBoby = $modal.find('.modal-body');

        let inputAddress = document.getElementById('address-google-map');
        let inputHiddenAddress = document.querySelector('form input[type="hidden"].input_user_address');
        let inputHiddenLat = document.querySelector('form input[type="hidden"].user_lat');
        let inputHiddenLng = document.querySelector('form input[type="hidden"].user_lng');

        // Creation d'un marker
        let createMarker = function (result) {
            marker = new google.maps.Marker({
                position: result.geometry.location,
                map,
                title: result.business_status ? `${result.name} - ${result.formatted_address}` : result.formatted_address,
                draggable: true,
            });
            map.setCenter(result.geometry.location);
            map.setZoom(12);

            google.maps.event.addListener(marker, 'dragend', function() 
            {
                geocodePosition(marker.getPosition());
            });
        };

        function geocodePosition(pos) {
           let geocoder = new google.maps.Geocoder();
           geocoder.geocode
            ({
                latLng: pos
            }, 
                function(results, status) 
                {
                    if (status == google.maps.GeocoderStatus.OK) 
                    {
                        updateFields(results[0]);
                    } else {
                        let zeroResultsHTML = `<p>${translationsJS && translationsJS.no_result_found ? translationsJS.no_result_found : 'No results'}</p>`;
                        $modalBoby.html(zeroResultsHTML);
                        $modal.modal('show');
                    }
                }
            );
        }

        // Mise à jour des champs cachés
        let updateFields = function (result) {
            let title = result.business_status ? `${result.name} - ${result.formatted_address}` : result.formatted_address;

            let coords = {
                title: title,
                lat: result.geometry.location.lat(),
                lng: result.geometry.location.lng(),
            }

            if (inputAddress) inputAddress.value = title;
            if (inputHiddenAddress) inputHiddenAddress.value = JSON.stringify(coords);
            if (inputHiddenLat) inputHiddenLat.value = result.geometry.location.lat();
            if (inputHiddenLng) inputHiddenLng.value = result.geometry.location.lng();

            if ($("#map.not-allowed").length > 0)
                $("#map").removeClass("not-allowed");
        };

        $('body').on('input', '#address-google-map', function(e) {
            if($(this).val().length > 0)
                $('#btn-geocode').prop('disabled', false);
            else {
                inputHiddenAddress.value = '';
                inputHiddenLat.value = '';
                inputHiddenLng.value = '';
                $('#btn-geocode').prop('disabled', true);
            }
        });

        $('body').on('keydown', '#address-google-map', function(e) {
            if(e.key === 'Enter') {
                e.preventDefault();
                $(this).closest('.input-group').find('button').trigger('click');      
            }
        });

        // Au clic sur rechercher
        $('body').on('click', '#btn-geocode', function() {

            $(this).prop('disabled', true);
            $(this).find('.spinner-border').toggleClass('inactive active');
            $(this).find('.fa-search').toggleClass('inactive active');

            var address = document.getElementById('address-google-map').value;
            const request = {
                query: address,
                fields: ["name", "geometry"]
            };
            service = new google.maps.places.PlacesService(map);
            service.textSearch(request, (results, status) => {               
                
                if (status === google.maps.places.PlacesServiceStatus.OK && results) {                      
                    // Plusieurs resultats
                    if (results.length > 1) {
                        let listLocationHTML = '<ul>'
                        for (let i = 0; i < results.length; i++) {
                            tabResults[i] = results[i];
                            let titleHTML = results[i].business_status ? `<p>${results[i].name}</p><p>${results[i].formatted_address}</p>` : `<p>${results[i].formatted_address}</p>`;
                            listLocationHTML += `<li data-id="${i}">${titleHTML}</li>`;

                        }
                        listLocationHTML += '</ul>'
                        $modalBoby.html(listLocationHTML);
                        $modal.modal('show')
                    }
                    // 1 seul resultat
                    if (results.length == 1) {
                        if (marker) marker.setMap(null); // Suppression marker
                        createMarker(results[0]);
                        updateFields(results[0]);
                    }
                } else {
                    // Pas de resultats
                    if (marker) marker.setMap(null); // Suppression marker
                    let zeroResultsHTML = `<p>${translationsJS && translationsJS.no_result_found ? translationsJS.no_result_found : 'No results'}</p>`;
                    $modalBoby.html(zeroResultsHTML);
                    $modal.modal('show');

                    if ($("#map").length > 0)
                        $("#map").addClass("not-allowed");
                }

                $(this).find('.spinner-border').toggleClass('inactive active');
                $(this).find('.fa-search').toggleClass('inactive active');
                setTimeout(function() {
                    $('#btn-geocode').prop('disabled', false);
                }, 200);
            })
        });

        // Selection d'une adresse via la modal
        $('body').on('click', '#modal-address li', function() {
            if (marker) marker.setMap(null); // Suppression marker
            var id = $(this).attr('data-id');
            $modal.modal('hide');
            createMarker(tabResults[id]);
            updateFields(tabResults[id]);
        });
        
        $('#modal-address').on('hidden.bs.modal', function (e) {
            $modalBoby.children().remove();
        })

        function initialize() {
            let geocoder = new google.maps.Geocoder();
            var latlng = new google.maps.LatLng(0, 0);
            var mapOptions = {
                zoom: 1,
                center: latlng,
                scaleControl: false,
                mapTypeControl: false,
                navigationControl: false,
                streetViewControl: false,
                fullscreenControl: false,
            }
            map = new google.maps.Map(document.getElementById('map'), mapOptions);

            // Affichage du marker en edition
            if (inputHiddenLat && inputHiddenLat.value != ''
                && inputHiddenLng && inputHiddenLng.value != ''
                && inputHiddenAddress && inputHiddenAddress.value != '') {
                    let inputHiddenAddressVal = JSON.parse(inputHiddenAddress.value);
                    let result = {};
                    result.geometry = {};
                    result.geometry.location = {lat: parseFloat(inputHiddenLat.value), lng: parseFloat(inputHiddenLng.value)};
                    result.name = inputHiddenAddressVal && inputHiddenAddressVal.title ? inputHiddenAddressVal.title : '';
                    createMarker(result);

                    if (inputAddress) inputAddress.value = result.name;
                    $('#btn-geocode').prop('disabled', false);
            }
        }
        initialize(); 
    }

    init = function() {
        this.initMap();
    }
}

$(document).ready(function() { 
    let map = new Map();
    map.init();
});
