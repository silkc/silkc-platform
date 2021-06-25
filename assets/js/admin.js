import $ from 'jquery';
require('bootstrap');

import 'datatables.net';
import 'datatables.net-select-dt';
import 'datatables.net-dt/css/jquery.dataTables.min.css';
import 'datatables.net-select-dt/css/select.dataTables.min.css';

// any CSS you import will output into a single css file (app.css in this case)
import '../scss/elements/header.scss';
import '../scss/admin.scss';



var moment = require('moment');
require('chart.js');
require('@fortawesome/fontawesome-free/js/all.min');
require('bootstrap-select');

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

     runDatatableTask = () => {
        let table = $('#datatable-task').DataTable({
            searching: false, 
            info: false,
            lengthChange: false,
            columnDefs: [
                { targets: [4], orderable: false}
            ],
            order: [[ 1, 'asc' ]]
        });
    }

     runDatatableWork = () => {
        let table = $('#datatable-work').DataTable({
            searching: true, 
            info: false,
            lengthChange: false,
            columnDefs: [
                { targets: [2], orderable: false},
                { width: '20px', targets: 2 }
            ],
            fixedColumns: true,
            order: [[ 1, 'asc' ]]
        });
    }

    runDatatableSkill = () => {
        let table = $('#datatable-skill').DataTable({
            searching: true,
            info: false,
            lengthChange: false,
            columnDefs: [
                { targets: [2], orderable: false},
                { width: '20px', targets: 2 }
            ],
            fixedColumns: true,
            order: [[ 1, 'asc' ]]
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

    getDetails = () => {

        $('body').on('click', '#content-work .btn-info', function() {

            let $modal = $('#common-modal');
            let id = $(this).attr('data-id');
            let url = '/api/skills_by_occupation/' + id;
            $.ajax({
                type: "GET",
                url: url,
                async: true,
                success: function (data, textStatus, jqXHR) {
                    let dataOccupation =  data && data.occupation != undefined ? data.occupation : false;
                    let dataSkills =  data && data.skills != undefined && data.skills.length > 0 ? data.skills : [];

                    if (dataOccupation) {
                        $modal.find('.modal-title').html(dataOccupation.preferredLabel ? dataOccupation.preferredLabel : '');
                        $(`<p>${dataOccupation.description ? dataOccupation.description : ''}</p>`).appendTo($modal.find('.modal-body'));  
                    }

                    let htmlEssential = '';
                    let htmlOptional = '';

                    if (dataSkills && dataSkills.length > 0 && $modal) {
                        for (let k = 0; k < dataSkills.length; k++) { 
                            let li = `<li>
                                        <span class="link-description" tabindex="${k}" data-toggle="popover" data-trigger="focus" title="${dataSkills[k].skill.preferredLabel}" data-content="${dataSkills[k].skill.description}">
                                            ${dataSkills[k].skill.preferredLabel}
                                        </span>
                                    </li>`;
                            if (dataSkills[k].relationType == 'essential')
                                htmlEssential += li;
                            if (dataSkills[k].relationType == 'optional')
                                htmlOptional += li;

                            if (k == dataSkills.length - 1) {
                                $(`<h1>Essential skills</h1><ul>${htmlEssential}</ul>`).appendTo($modal.find('.modal-body'));
                                $(`<h1>Optional skills</h1><ul>${htmlOptional}</ul>`).appendTo($modal.find('.modal-body'));

                                $('#common-modal').find('.modal-dialog').addClass('modal-lg').addClass('modal-content-work');
                                $('#common-modal').modal('show');

                                $('#common-modal .modal-content-work [data-toggle="popover"]').popover();
                            }
                        }
                    } else {
                        $('#common-modal').find('.modal-dialog').addClass('modal-lg').addClass('modal-content-work');
                        $('#common-modal').modal('show');
                    }
                }
            });
        });

        $('body').on('click', '#content-skill .btn-info', function() {

            let $modal = $('#common-modal');
            let id = $(this).attr('data-id');
            let url = '/apip/skills/' + id;
            $.ajax({
                type: "GET",
                url: url,
                async: true,
                success: function (data, textStatus, jqXHR) {
                    if (data) {
                        $modal.find('.modal-title').html(data.preferredLabel ? data.preferredLabel : '');
                        $(`<p>${data.description ? data.description : ''}</p>`).appendTo($modal.find('.modal-body'));
                        $(`<p><strong>URI :</strong> <a href="${data.conceptUri ? data.conceptUri : '#'}">${data.conceptUri ? data.conceptUri : 'NC'}</a></p>`).appendTo($modal.find('.modal-body'));
                        $(`<p><strong>Skill type :</strong> <em>${data.skillType ? data.skillType : 'NC'}</em></p>`).appendTo($modal.find('.modal-body'));
                        $('#common-modal').modal('show');
                    }
                }
            });
        });

        $('body').on('click', '#content-training .see-detail', function() {

            let $modal = $('#common-modal');
            let id = $(this).attr('data-id');
            let url = '/apip/trainings/' + id;
            $.ajax({
                type: "GET",
                url: url,
                async: true,
                success: function (data, textStatus, jqXHR) {
                    if (data) {

                        let requireSkillsHTML = '';
                        let acquireSkillsHTML = '';

                        if (data.trainingSkills && data.trainingSkills.length > 0 ) {
                            for (let k in data.trainingSkills) {
                                let skill = data.trainingSkills[k].skill;
                                if (data.trainingSkills[k].isRequired) {
                                    requireSkillsHTML += `<li class="list-group-item d-flex justify-content-between align-items-center">
                                                            ${skill.preferredLabel ? skill.preferredLabel : ''}
                                                        </li>`;
                                }
                                if (data.trainingSkills[k].isToAcquire) {
                                    acquireSkillsHTML += `<li class="list-group-item d-flex justify-content-between align-items-center">
                                                            ${skill.preferredLabel ? skill.preferredLabel : ''}
                                                        </li>`;
                                }
                            }
                        }

                        let modalBodyHTML = `<div class="row">
								<div class="col-md-12 detail-training">

									<div class="row mb-3">
										<div class="col-lg-4">
											<span class="title">Name</span>
										</div>
										<div class="col-lg-8">
											<span>${data.name ? data.name : ''}</span>
										</div>
									</div>

									<div class="row mb-3">
										<div class="col-lg-4">
											<span class="title">Location</span>
										</div>
										<div class="col-lg-8">
											<div>${data.location ? data.location : ''}</div>
										</div>
									</div>

									<div class="row mb-3">
										<div class="col-lg-4">
											<span class="title">Duration</span>
										</div>
										<div class="col-lg-8">
											<span>${data.duration ? data.duration : ''}</span>
										</div>
									</div>

									<div class="row mb-3">
										<div class="col-lg-4">
											<span class="title">Description</span>
										</div>
										<div class="col-lg-8">
											<p class="text-justify m-0">
                                                ${data.description ? data.description : '-'}
											</p>
										</div>
									</div>

									<div class="row mb-3">
										<div class="col-lg-4">
											<span class="title">Price</span>
										</div>
										<div class="col-lg-8">
											<span>${data.price ? data.price : ''}</span>
										</div>
									</div>
									<div class="mb-3">
                                        <span class="required-skills d-block mb-3 title">Required_skills</span>
                                        <ul class="list-group">
                                            ${requireSkillsHTML && requireSkillsHTML.length > 0 ? requireSkillsHTML : '<li>-</li>'}
                                        </ul>
									</div>

									<div class="mb-3">
                                    <span class="required-skills d-block mb-3 title">Required_skills</span>
                                        <ul class="list-group">
                                            ${acquireSkillsHTML && acquireSkillsHTML.length > 0 ? acquireSkillsHTML : '<li>-</li>'}
                                        </ul>
									</div>
								</div>
							</div>`;


                        $modal.find('.modal-title').html(data.name ? data.name : '');
                        $(modalBodyHTML).appendTo($modal.find('.modal-body'));
                        $('#common-modal').find('.modal-dialog').addClass('modal-lg');
                        $('#common-modal').modal('show');
                    }
                }
            });
        });
    }

    init = function() {
        this.runDatatableHome();
        this.runDatatableTask();
        this.runDatatableWork();
        this.runDatatableSkill();
        this.seeDetailWork();
        this.runMap();
        this.getDetails();

        $('[data-toggle="tooltip"]').tooltip();
    }
}





$(document).ready(function() {
    let admin = new Admin();
    admin.init();
});