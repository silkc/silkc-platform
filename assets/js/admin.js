import $ from 'jquery';
require('bootstrap');
const bootbox = require('bootbox/bootbox');

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
                {targets: [4], orderable: false},
            ],
            order: [[ 1, 'asc' ]]
        });
    }

     runDatatableInstitution = () => {
        let table = $('#datatable-institution').DataTable({
            searching: false, 
            info: false,
            lengthChange: false,
            columnDefs: [
                {targets: [3], orderable: false},
            ],
            order: [[ 1, 'asc' ]]
        });
    }

     runDatatableUsers = () => {
        let table = $('#datatable-user').DataTable({
            searching: false, 
            info: false,
            lengthChange: false,
            columnDefs: [
                {targets: [7], orderable: false},
            ],
            order: [[ 1, 'desc' ]]
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
            order: [[ 0, 'asc' ]]
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
            order: [[ 0, 'asc' ]]
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
    }

    /**
     * Affichage carte
     */
     runMap = () => { 

        let initMap = function () {
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
        }

        $('#admin #personal_informations-tab[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            initMap();
        });

        initMap();
    }

    getDetails = () => {

        $('body').on('click', '#content-work .get-info', function() {

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

        $('body').on('click', '#content-skill .get-info', function() {

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


        let getModalTrainings = function(_this, $modal) {
            let id = $(_this).attr('data-id');
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
                                    requireSkillsHTML += `<li>
                                                            <span class="link-description" tabindex="${k}" data-toggle="popover" data-trigger="focus" title="" data-content="${skill.description ? skill.description : ''}" data-original-title="${skill.preferredLabel ? skill.preferredLabel : ''}">
                                                                ${skill.preferredLabel ? skill.preferredLabel : 'N/A'}
                                                            </span>
                                                        </li>`;
                                }
                                if (data.trainingSkills[k].isToAcquire) {
                                    acquireSkillsHTML += `<li>
                                                            <span class="link-description" tabindex="${k}" data-toggle="popover" data-trigger="focus" title="" data-content="${skill.description ? skill.description : ''}" data-original-title="${skill.preferredLabel ? skill.preferredLabel : ''}">
                                                                ${skill.preferredLabel ? skill.preferredLabel : 'N/A'}
                                                            </span>
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
											<span>${data.name ? data.name : 'N/A'}</span>
										</div>
									</div>

									<div class="row mb-3">
										<div class="col-lg-4">
											<span class="title">Location</span>
										</div>
										<div class="col-lg-8">
											<div>${data.location ? data.location : 'N/A'}</div>
										</div>
									</div>

									<div class="row mb-3">
										<div class="col-lg-4">
											<span class="title">Duration</span>
										</div>
										<div class="col-lg-8">
											<span>${data.duration ? data.duration : 'N/A'}</span>
										</div>
									</div>

									<div class="row mb-3">
										<div class="col-lg-4">
											<span class="title">Description</span>
										</div>
										<div class="col-lg-8">
											<p class="text-justify m-0">
                                                ${data.description ? data.description : 'N/A'}
											</p>
										</div>
									</div>

									<div class="row mb-3">
										<div class="col-lg-4">
											<span class="title">Price</span>
										</div>
										<div class="col-lg-8">
											<span>${data.price ? data.price : 'N/A'}</span>
										</div>
									</div>
									<div class="mb-3">
                                        <span class="required-skills d-block mb-3 title">Required skills</span>
                                        <ul>
                                            ${requireSkillsHTML && requireSkillsHTML.length > 0 ? requireSkillsHTML : 'N/A'}
                                        </ul>
									</div>

									<div class="mb-3">
                                    <span class="required-skills d-block mb-3 title">Acquired skills</span>
                                        <ul>
                                            ${acquireSkillsHTML && acquireSkillsHTML.length > 0 ? acquireSkillsHTML : 'N/A'}
                                        </ul>
									</div>
								</div>
							</div>`;


                        let editHTML = `<div class="row blc-edit-training">
                                            <div class="col-md-12 text-right">
                                                <a href="/training/edit/${id}" class="btn btn-primary">Edit</a>
                                            </div>
                                        </div>`;

                        $modal.find('.modal-title').html(data.name ? data.name : '');
                        $(modalBodyHTML).appendTo($modal.find('.modal-body'));
                        $(editHTML).prependTo($modal.find('.modal-footer'));
                        $modal.find('.modal-dialog').addClass('modal-lg');

                        $modal.find('[data-toggle="popover"]').popover();

                        $modal.modal('show');
                    }
                }
            });
        }
        $('body').on('click', '#content-training .see-detail', function() {
            let $modal = $('#common-modal');
            getModalTrainings(this, $modal);
        });

        $('body').on('click', '#content-tasks .see-detail', function() {
            let $modal = $('#common-modal');
            getModalTrainings(this, $modal);
        });
        
        $('body').on('click', '#content-work .btn-related-trainings-work', function() {

            let $modal = $('#common-modal');
            let id = $(this).attr('data-id');
            let url = '/admin/get_occupation_related_trainings/' + id;

            $.ajax({
                type: "GET",
                url: url,
                async: true,
                success: function (data, textStatus, jqXHR) { 
                    if (data) {
                        let occupation = data.occupation ? data.occupation : false;
                        $modal.find('.modal-title').html(occupation && occupation.preferredLabel ? occupation.preferredLabel : '');
                        let dataTrainings = data.trainings && data.trainings.length > 0 ? data.trainings : false;
                        if (dataTrainings && dataTrainings.length > 0) {
                            let trainingsHTML = '<ul>'
                            for (let k in dataTrainings) {
                                trainingsHTML += `<li><span class="lk-open-training" data-id="${dataTrainings[k].id ? dataTrainings[k].id : ''}">${dataTrainings[k].name ? dataTrainings[k].name : ''}</span></li>`;
                                
                                if (k == dataTrainings.length - 1) {
                                    trainingsHTML += '</ul>'
                                    $(trainingsHTML).appendTo($modal.find('.modal-body'));
                                    $('#common-modal').modal('show');
                                }
                            }
                        } else {
                            $('<p>No trainings</p>').appendTo($modal.find('.modal-body'));
                            $('#common-modal').modal('show');
                        }
                    }
                }
            });
        });

        
        $('body').on('click', '#content-skill .btn-related-trainings', function() {

            let $modal = $('#common-modal');
            let id = $(this).attr('data-id');
            let url = '/admin/get_skill_related_trainings/' + id;
            $.ajax({
                type: "GET",
                url: url,
                async: true,
                success: function (data, textStatus, jqXHR) { 
                    if (data) {
                        let skill = data.skill ? data.skill : false;
                        $modal.find('.modal-title').html(skill && skill.preferredLabel ? skill.preferredLabel : '');
                        let dataTrainings = data.trainings && data.trainings.length > 0 ? data.trainings : false;
                        if (dataTrainings && dataTrainings.length > 0) {
                            let trainingsHTML = '<ul>'
                            for (let k in dataTrainings) {
                                trainingsHTML += `<li><span class="lk-open-training" data-id="${dataTrainings[k].id ? dataTrainings[k].id : ''}">${dataTrainings[k].name ? dataTrainings[k].name : ''}</span></li>`;
                                
                                if (k == dataTrainings.length - 1) {
                                    trainingsHTML += '</ul>'
                                    $(trainingsHTML).appendTo($modal.find('.modal-body'));
                                    $('#common-modal').modal('show');
                                }
                            }
                        } else {
                            $('<p>No trainings</p>').appendTo($modal.find('.modal-body'));
                            $('#common-modal').modal('show');
                        }
                    }
                }
            });
        });

        $('body').on('click', '.lk-open-training', function() {
            let $modal = $('#common-modal-2');
            getModalTrainings(this, $modal);
        })
    }

    /**
     * Actions sur les trainings
     */
     runTrainingsActions = () => {
        // APPROVE
        $('body').on('click', 'button.approve_training', function() {
            let $button = $(this);
            let id = $button.attr('data-id');
            let url = '/admin/approve_training/' + id;
            bootbox.confirm({message : 'Are you sure you want to approve this training?', buttons : { cancel : { label : 'Cancel'}, confirm : { label : 'Yes'}}, callback : function(result) {
                if (result == true) {
                    $.ajax({
                        type: "POST",
                        url: url,
                        async: true,
                        success: function (data, textStatus, jqXHR) {
                            if (data.result != undefined && data.result == true) {
                                let $td = $button.closest('tr').find('td:eq(3)');
                                if ($td && $td.length > 0)
                                    $td.html('<span class="text-success">Approve</span>');
                                $button.removeClass('btn-success').addClass('btn-warning');
                                $button.find('i, svg').removeClass('fa-check').addClass('fa-ban');
                                $button.removeClass('approve_training').addClass('reject_training').attr('data-original-title', 'Reject');
                            } else {
                                bootbox.alert('An error occured');
                            }
                        }
                    });
                }
            }});
        });
        // REJECT
        $('body').on('click', 'button.reject_training', function() {
            let $button = $(this);
            let id = $button.attr('data-id');
            let url = '/admin/reject_training/' + id;
            bootbox.confirm({message : 'Do you really want to reject this training?', buttons : { cancel : { label : 'Cancel'}, confirm : { label : 'Yes'}}, callback : function(result) {
                    if (result == true) {
                        $.ajax({
                            type: "POST",
                            url: url,
                            async: true,
                            success: function (data, textStatus, jqXHR) {
                                if (data.result != undefined && data.result == true) {
                                    let $td = $button.closest('tr').find('td:eq(3)');
                                    if ($td && $td.length > 0)
                                        $td.html('<span class="text-warning">Reject</span>');
                                    $button.removeClass('btn-warning').addClass('btn-success');
                                    $button.find('i, svg').removeClass('fa-ban').addClass('fa-check');
                                    $button.removeClass('reject_training').addClass('approve_training').attr('data-original-title', 'Approve');
                                } else {
                                    bootbox.alert('An error occured');
                                }
                            }
                        });
                    }
                }});
        });
        // DELETE
        $('body').on('click', 'button.delete_training', function() {
            let $button = $(this);
            let id = $button.attr('data-id');
            let url = '/admin/delete_training/' + id;
            bootbox.confirm({message : 'Are you sure you want to delete this training?', buttons : { cancel : { label : 'Cancel'}, confirm : { label : 'Yes'}}, callback : function(result) {
                    if (result == true) {
                        $.ajax({
                            type: "POST",
                            url: url,
                            async: true,
                            success: function (data, textStatus, jqXHR) {
                                if (data.result != undefined && data.result == true) {
                                    $('#datatable-task').DataTable()
                                        .row( $button.closest('tr') )
                                        .remove()
                                        .draw();
                                    
                                } else {
                                    bootbox.alert('An error occured');
                                }
                            }
                        });
                    }
                }});
        });
    }

    /**
     * Actions sur les utilisateurs
     */
    runUsersActions = () => {
        // SUSPEND
        $('body').on('click', 'button.suspend_user', function() {
            let $button = $(this);
            let id = $button.attr('data-id');
            let url = '/admin/suspend_user/' + id;
            bootbox.confirm({message : 'Are you sure you want to suspend this user?', buttons : { cancel : { label : 'Cancel'}, confirm : { label : 'Yes'}}, callback : function(result) {
                if (result == true) {
                    $.ajax({
                        type: "POST",
                        url: url,
                        async: true,
                        success: function (data, textStatus, jqXHR) {
                            if (data.result != undefined && data.result == true) {
                                let $td = $button.closest('tr').find('td:eq(5)');
                                if ($td && $td.length > 0)
                                    $td.html('<span class="text-warning">yes</span>');
                                $button.removeClass('btn-warning').addClass('btn-success');
                                $button.removeClass('suspend_user').addClass('unsuspend_user').attr('data-original-title', 'Unsuspend');
                            } else {
                                bootbox.alert('An error occured');
                            }
                        }
                    });
                }
            }});
        });
        // UNSUSPEND
        $('body').on('click', 'button.unsuspend_user', function() {
            let $button = $(this);
            let id = $button.attr('data-id');
            let url = '/admin/unsuspend_user/' + id;
            bootbox.confirm({message : 'Are you sure you want to unsuspend this user?', buttons : { cancel : { label : 'Cancel'}, confirm : { label : 'Yes'}}, callback : function(result) {
                    if (result == true) {
                        $.ajax({
                            type: "POST",
                            url: url,
                            async: true,
                            success: function (data, textStatus, jqXHR) {
                                if (data.result != undefined && data.result == true) {
                                    let $td = $button.closest('tr').find('td:eq(5)');
                                    if ($td && $td.length > 0)
                                        $td.html('<span class="text-success">no</span>');
                                    $button.removeClass('btn-success').addClass('btn-warning');
                                    $button.removeClass('unsuspend_user').addClass('suspend_user').attr('data-original-title', 'Suspend');
                                } else {
                                    bootbox.alert('An error occured');
                                }
                            }
                        });
                    }
                }});
        });
        // SUSPECT
        $('body').on('click', 'button.suspect_user', function() {
            let $button = $(this);
            let id = $button.attr('data-id');
            let url = '/admin/suspect_user/' + id;
            bootbox.confirm({message : 'Are you sure you want to mark this user as "suspect"', buttons : { cancel : { label : 'Cancel'}, confirm : { label : 'Yes'}}, callback : function(result) {
                    if (result == true) {
                        $.ajax({
                            type: "POST",
                            url: url,
                            async: true,
                            success: function (data, textStatus, jqXHR) {
                                if (data.result != undefined && data.result == true) {
                                    let $td = $button.closest('tr').find('td:eq(6)');
                                    if ($td && $td.length > 0)
                                        $td.html('<span class="text-warning">yes</span>');
                                    $button.removeClass('btn-warning').addClass('btn-success');
                                    $button.removeClass('suspect_user').addClass('raise_suspicion').attr('data-original-title', 'Raise suspicion');
                                } else {
                                    bootbox.alert('An error occured');
                                }
                            }
                        });
                    }
                }});
        });
        // RAISE SUSPICION
        $('body').on('click', 'button.raise_suspicion', function() {
            let $button = $(this);
            let id = $button.attr('data-id');
            let url = '/admin/raise_suspicion/' + id;
            bootbox.confirm({message : 'Are you sure you want to raise suspicion for this user?', buttons : { cancel : { label : 'Cancel'}, confirm : { label : 'Yes'}}, callback : function(result) {
                    if (result == true) {
                        $.ajax({
                            type: "POST",
                            url: url,
                            async: true,
                            success: function (data, textStatus, jqXHR) {
                                if (data.result != undefined && data.result == true) {
                                    let $td = $button.closest('tr').find('td:eq(6)');
                                    if ($td && $td.length > 0)
                                        $td.html('<span class="text-success">no</span>');
                                    $button.removeClass('btn-success').addClass('btn-warning');
                                    $button.removeClass('raise_suspicion').addClass('suspect_user').attr('data-original-title', 'Mark user as "suspect"');
                                } else {
                                    bootbox.alert('An error occured');
                                }
                            }
                        });
                    }
                }});
        });
    }

    init = function() {
        this.runDatatableHome();
        this.runDatatableTask();
        this.runDatatableInstitution();
        this.runDatatableUsers();
        this.runDatatableWork();
        this.runDatatableSkill();
        this.seeDetailWork();
        this.runMap();
        this.getDetails();
        this.runUsersActions();
        this.runTrainingsActions();

        $('[data-toggle="tooltip"]').tooltip();

        $('#common-modal').on('hidden.bs.modal', function (e) {
            $(this).find('.modal-title').children().remove();
            $(this).find('.modal-body').children().remove();
            $(this).find('.modal-footer').find('.blc-edit-training').remove();
        });
        
        $('#common-modal-2').on('hidden.bs.modal', function (e) {
            $(this).find('.modal-title').children().remove();
            $(this).find('.modal-body').children().remove();
            $(this).find('.modal-footer').find('.blc-edit-training').remove();
        });
    }
}





$(document).ready(function() {
    let admin = new Admin();
    admin.init();
});