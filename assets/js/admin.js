import $ from 'jquery';
require('bootstrap');

import 'datatables.net';
import 'datatables.net-select-dt';
import 'datatables.net-dt/css/jquery.dataTables.min.css';
import 'datatables.net-select-dt/css/select.dataTables.min.css';

// any CSS you import will output into a single css file (app.css in this case)
import '../scss/elements/header.scss';
import '../scss/admin.scss';


//require('popper');
var moment = require('moment');
require('chart.js');
require('@fortawesome/fontawesome-free/js/all.min');

class Admin {
    instanceProperty = "Admin";
    boundFunction = () => {
        return this.instanceProperty;
    }

    /**
     * Affichage des messages de mises à jour
     */
    runDatatableHome = () => {
        let notificationsIds = [];
        let setInputHidden = function (ids = []) {
            $('.btn-read').prop('disabled', (ids.length > 0) ? false : true);
            $('#notifications-read').val(JSON.stringify(ids));
        };

        /*$('body').on('click', '.btn-read', function (e) {
            e.preventDefault();

            let url = 'admin/read';
            let ids = $('#notifications-read').val();

            $.ajax({
                url: url,
                type: "POST",
                dataType: 'json',
                data: ids,
                success: function (data, textStatus, jqXHR) {
                    tableNotification.rows().every( function ( rowIdx, tableLoop, rowLoop ) {
                        let node = this.node();
                        if ($(node).find('input.editor-active').is(':checked')) {
                            $(node).removeClass('font-weight-bold');
                        }
                        $(node).find('input.editor-active').prop('checked', false);
                    });
                },
                error: function () {},
                complete: function() {}
            });
        });*/

        let tableNotification = $('#datatable-subject').DataTable({
            searching: false, 
            info: false,
            lengthChange: false,
            select: {
                style:    'multi',
                selector: 'td:not(:first-child)' // no row selection on last column
            },
            columnDefs: [
                { targets: [0], orderable: false}
            ],
            order: [[ 1, 'asc' ]]
        });

        $('#datatable-subject').on('change', 'input.editor-active', function () {
            let tr = $(this).closest('tr');
            let notificationId = tr.attr('data-id');
            let idx = tr.index();
            if ($(this).is(':checked')) {
                tableNotification.row(':eq(' + idx + ')', { page: 'current' }).select(); 
                if (notificationsIds.indexOf(notificationId) == -1) {
                    notificationsIds.push(notificationId);
                }
                setInputHidden(notificationsIds);
            } else {
                tableNotification.row(':eq(' + idx + ')', { page: 'current' }).deselect(); 
                if (notificationsIds.indexOf(notificationId) != -1) {
                    notificationsIds.splice(notificationsIds.indexOf(notificationId), 1); 
                }
                setInputHidden(notificationsIds);
            }
        });

        $(".selectAll").on("click", function(e) {
            if ($(this).is(":checked")) {
                let cpt = 0;
                let totalRowCount = tableNotification.data().length;
                tableNotification.rows().select();
                tableNotification.rows().every( function ( rowIdx, tableLoop, rowLoop ) {
                    let node = this.node();
                    $(node).find('input.editor-active').prop('checked', true);
                    let tr = $(node);
                    let notificationId = tr.attr('data-id');
                    if (notificationsIds.indexOf(notificationId) == -1) {
                        notificationsIds.push(notificationId);
                    }
                    cpt++;
                    if (cpt == totalRowCount) {
                        setInputHidden(notificationsIds);
                    }
                }); 
            } else {
                let cpt = 0;
                let totalRowCount = tableNotification.data().length;
                tableNotification.rows().deselect(); 
                tableNotification.rows().every( function ( rowIdx, tableLoop, rowLoop ) {
                    let node = this.node();
                    $(node).find('input.editor-active').prop('checked', false);
                    let tr = $(node);
                    let notificationId = tr.attr('data-id');
                    if (notificationsIds.indexOf(notificationId) != -1) {
                        notificationsIds.splice(notificationsIds.indexOf(notificationId), 1); 
                    }
                    cpt++;
                    if (cpt == totalRowCount) {
                        setInputHidden(notificationsIds);
                    }
                });
            }
        });
    }

    /**
     * Affichage des messages de mises à jour
     */
     runDatatableWork = () => {
        let table = $('#datatable-work').DataTable({
            searching: false, 
            info: false,
            lengthChange: false,
            columnDefs: [
                { targets: [4], orderable: false}
            ],
            order: [[ 1, 'asc' ]]
        });

        $('.selectAll').on('click', function(e) {
            if ($(this).is(':checked'))
                table.rows().select(); 
            else
                table.rows( ).deselect();
        });
    }

    /**
     * Affichage detail de la formation dans une modal
     */
     seeDetailWork = () => {

        $('body').on('click', '#content-work a.see-detail', function(e) {
            e.preventDefault();

            let name = $(this).attr('data-name');
            let description = $(this).attr('data-description');
            let $modal = $('#common-modal');

            if ($modal) {
                $modal.find('.modal-title').html(name);
                $(`<p>${description}</p>`).appendTo($modal.find('.modal-body'));
                $('#common-modal').modal('show');
            }
        });

        $('#common-modal').on('hidden.bs.modal', function (e) {
            $(this).find('.modal-title').children().remove();
            $(this).find('.modal-body').children().remove();
        });
    }

    /**
     * Affichage carte
     */
     runMap = () => { 

        $('#admin #personal_informations-tab[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            let inputHidden = document.getElementById('user_address');
            var map = null;

            if (inputHidden) {
                let coords = inputHidden.value;

                if (coords) {
                    coords = JSON.parse(coords);
                    map = L.map('map').setView([coords.lat, coords.lng], 10);
                } else {
                    map = L.map('map').setView([0, 0], 2);
                }
                
                let geocoder = L.Control.Geocoder.nominatim();
                
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
                            "city": name,
                            "lat": lat,
                            "lng": lng
                        };
                        newCoords = JSON.stringify(newCoords);
                        
                        let leafletControlGeocoderForm = document.querySelector('.leaflet-control-geocoder-form input');
                        leafletControlGeocoderForm.value = name;
                        inputHidden.value = newCoords;
                    }
                }).addTo(map);
                
                // Créer l'objet "map" et l'insèrer dans l'élément HTML qui a l'ID "map"
                // Leaflet ne récupère pas les cartes (tiles) sur un serveur par défaut. Nous devons lui préciser où nous souhaitons les récupérer. Ici, openstreetmap.fr
                L.tileLayer('https://{s}.tile.openstreetmap.fr/osmfr/{z}/{x}/{y}.png', {
                    attribution: '',
                    minZoom: 1,
                    maxZoom: 20
                }).addTo(map);
                
                document.getElementById('searchmap').appendChild(document.querySelector('.leaflet-control-geocoder.leaflet-bar'));

                if (coords) {
                    let marker = L.marker([coords.lat, coords.lng]).addTo(map); // Markeur
                    marker.bindPopup(coords.city); // Bulle d'info

                    let leafletControlGeocoderForm = document.querySelector('.leaflet-control-geocoder-form input');
                    leafletControlGeocoderForm.value = coords.city;
                }
            }
        });
    }

    init = function() {
        this.runDatatableHome();
        this.runDatatableWork();
        this.seeDetailWork();
        this.runMap();

        $('[data-toggle="tooltip"]').tooltip();
    }
}





$(document).ready(function() {
    let admin = new Admin();
    admin.init();
});