import $ from 'jquery';
require('bootstrap');
const bootbox = require('bootbox/bootbox');

import 'datatables.net';
import 'datatables.net-select-dt';
import 'datatables.net-dt/css/jquery.dataTables.min.css';
import 'datatables.net-select-dt/css/select.dataTables.min.css'; 
import 'datatables.net-fixedcolumns'

// any CSS you import will output into a single css file (app.css in this case)
import '../scss/elements/header.scss';
import '../scss/admin.scss';

var moment = require('moment');
require('chart.js');
require('@fortawesome/fontawesome-free/js/all.min');
require('bootstrap-select');


function renderDate(date, format = 'DD MMMM YYYY to HH:mm') {
    let lang = $('body').attr('lang');
    if (
        date == undefined ||
        (typeof date != 'string' && typeof date != 'object') ||
        (typeof date == 'string' && date.length == 0)
    )
        return '-';

    const oDate = moment(date);
    if (oDate === null)
        return '-';

    /*if (oDate > moment().startOf('day') && oDate < moment().endOf('day')) {
        return "Today at " + oDate.locale('en').format('HH:mm');
    }
    else if (oDate > moment().subtract(1, 'day').startOf('day') && oDate < moment().subtract(1, 'day').endOf('day')) {
        return "Yesterday at " + oDate.locale('en').format('HH:mm');
    }*/

    return oDate.locale(lang).format(format);
}

function getKeyByValue(object, value) {
    return Object.keys(object).find(key => object[key] === value);
}

function truncate(stringToTruncate, maxLength) {
    var trimmedString = stringToTruncate.substr(0, maxLength);
    return trimmedString.substr(0, Math.min(trimmedString.length, trimmedString.lastIndexOf(" ")))
}

let tradsDatatable = {
    search: translationsJS && translationsJS.datatable_search ? translationsJS.datatable_search : 'Search:',
    loadingRecords:  "&nbsp;",
    processing: '<div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div>',
    zeroRecords: "&nbsp;",
    paginate: {
        first: translationsJS && translationsJS.datatable_first ? translationsJS.datatable_first : 'First:',
        previous: translationsJS && translationsJS.datatable_previous ? translationsJS.datatable_previous : 'Previous:',
        next: translationsJS && translationsJS.datatable_next ? translationsJS.datatable_next : 'Next:',
        last: translationsJS && translationsJS.datatable_last ? translationsJS.datatable_last : 'Last:'
    }
};

class Admin {
    instanceProperty = "Admin";
    boundFunction = () => {
        return this.instanceProperty;
    }


    runDataTableEmpty = (table, $table) => {
        if (table.data().count() == 0) {
            $table.find('.dataTables_empty').text(translationsJS && translationsJS.datatable_zeroRecords ? translationsJS.datatable_zeroRecords : "No data available in table");
        }
    }

    /**
     * Affichage des messages de mises à jour
     */
    runDatatableHome = () => {

        let that = this;

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
            ajax: {
                url: '/admin/notifications',
                type: 'GET',
                dataSrc: function (json) {
                    return json.notifications;
                }
            },
            initComplete: function(settings, json) {
                that.runDataTableEmpty(tableNotification, $('#datatable-subject'));
            },
            processing: true,
            columns: [
                { "className": "dt-center", "orderable": false, "targets": 0, "render":
                    function (data, type, row) {
                        return '<input type="checkbox" class="editor-active">';
                    },
                },
                { "render":
                    function (data, type, row) {
                        return renderDate(row.createdAt, 'YYYY-MM-DD - HH:mm');
                    },
                },

                { data: 'title' },
            ],
            order: [[ 1, 'asc' ]],
            language: tradsDatatable
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

        let that = this;

        let table = $('#datatable-task').DataTable({
            searching: false, 
            info: false,
            lengthChange: false,
            ajax: {
                url: '/admin/tasks',
                type: 'GET',
                dataSrc: function (json) {
                    return json.to_validated_trainings;
                }
            },
            processing: true,
            initComplete: function(settings, json) {
                that.runDataTableEmpty(table, $('#datatable-task'));
            },
            columns: [
                { data: 'name' },
                { "className": "space-nowrap", "render":
                    function (data, type, row) {
                        return renderDate(row.createdAt, 'YYYY-MM-DD - HH:mm');
                    },
                },
                { "render":
                    function (data, type, row) {
                        let user = row.user;
                        let html = '-';
                        if (user !== null) {
                            html = user.roles != undefined && getKeyByValue(user.roles, 'ROLE_INSTITUTION') != undefined ? user.username : user.firstname + ' ' + user.lastname;
                            html += `<i class="fas fa-info-circle m" data-toggle="tooltip" title="${user.email}"></i>`;
                         }

                        return html;
                    },
                },
                { "className": "space-nowrap", "render":
                    function (data, type, row) {
                        let html = '';
                        if (row.isValidated == true)
                            html += `<span class="text-warning">${translationsJS && translationsJS.approved ? translationsJS.approved : 'Approved'}</span>`;
                        else if (row.isRejected == true)
                            html += `<span class="text-success">${ translationsJS && translationsJS.rejected ? translationsJS.rejected : 'Rejected'}</span>`;
                        else 
                            html += translationsJS && translationsJS.pending ? translationsJS.pending : 'Pending'

                        return html;
                    },
                },
                { "className": "dt-center", "orderable": false, "width": "0%", "render":
                    function (data, type, row) {

                        let html = '<div class="d-flex justify-content-center" style="width: 102px; margin: 0; padding: 0;">';

                        if (row.isValidated == false) {

                            html += `<button class="btn btn-sm btn-success approve_training" data-id="${ row.id }" data-toggle="tooltip" title="${ translationsJS && translationsJS.approve ? translationsJS.approve : 'Approve'}">
                                        <i class="fas fa-check"></i>
                                    </button>`;
                        } else {
                            html += `<button class="btn btn-sm btn-warning reject_training" data-id="${ row.id }" data-toggle="tooltip" title="${ translationsJS && translationsJS.reject ? translationsJS.reject : 'Reject'}">
                                        <i class="fas fa-ban"></i>
                                    </button>`;
                        }

                        html += `<button class="btn btn-sm btn-primary see-detail ml-1 mr-1" data-id="${ row.id }" data-toggle="tooltip" title="${ translationsJS && translationsJS.detail ? translationsJS.detail : 'Detail'}">
                                    <i class="fas fa-search-plus"></i>
                                </button>`;

                        html += `<button class="btn btn-sm btn-danger delete_training" data-id="${ row.id }" data-toggle="tooltip" title="${ translationsJS && translationsJS.delete ? translationsJS.delete : 'Delete'}">
                                    <i class="fas fa-trash-alt"></i>
                                </button>`;

                        html += '</div>';
                        return html;
                    },
                },
            ],
            //fixedColumns: true,
            order: [[ 1, 'asc' ]],
            language: tradsDatatable
        });
    }

     runDatatableInstitution = () => {

        let that = this;

        let table = $('#datatable-institution').DataTable({
            searching: false, 
            info: false,
            lengthChange: false,
            columnDefs: [
                {targets: [3], orderable: false},
            ],
            processing: true,
            initComplete: function(settings, json) {
                that.runDataTableEmpty(table, $('#datatable-institution'));
            },
            order: [[ 1, 'asc' ]],
            language: tradsDatatable
        });
    }

     runDatatableUsers = () => {
        let that = this;

        let lang = $('body').attr('lang');
        let table = $('#datatable-user').DataTable({
            searching: true, 
            info: false,
            lengthChange: false,
            ajax: {
                url: '/admin/users',
                type: 'GET',
                dataSrc: function (json) {
                    return json.users;
                }
            },
            processing: true,
            initComplete: function(settings, json) {
                that.runDataTableEmpty(table, $('#datatable-user'));
            },
            columns: [
                { data: 'id' },
                { data: 'firstname' },
                { data: 'lastname' },
                { data: 'username' },
                { "className": "dt-center", "render":
                    function (data, type, row) {
                        let html = `<span class="badge badge-info">`;

                        if (getKeyByValue(row.roles, 'ROLE_ADMIN') != undefined) {
                            html += translationsJS && translationsJS.user_role_admin ? translationsJS.user_role_admin : 'admin';
                        } else if (getKeyByValue(row.roles, 'ROLE_INSTITUTION') != undefined) {
                            html += translationsJS && translationsJS.user_role_institution ? translationsJS.user_role_institution: 'institution';
                        } else if (getKeyByValue(row.roles, 'ROLE_USER') != undefined) {
                            html += translationsJS && translationsJS.user_role_user ? translationsJS.user_role_user: 'user';
                        }
                        html += `</span>`;
                        return html;
                    },
                },
                { "className": "dt-center", "render":
                    function (data, type, row) {
                        let html = '';
                        if (row.isSuspended == true ) {
                            html += `<span class="text-warning">${translationsJS && translationsJS.yes ? translationsJS.yes : 'Yes'}</span>`;
                        } else {
                            html += `<span class="text-success">${translationsJS && translationsJS.no ? translationsJS.no : 'No'}</span>`;
                        }
                        return html;
                    },
                },
                { "className": "dt-center", "render":
                    function (data, type, row) {
                        let html = '';
                        if (row.isSuspected == true ) {
                            html += `<span class="text-warning">${translationsJS && translationsJS.yes ? translationsJS.yes : 'Yes'}</span>`;
                        } else {
                            html += `<span class="text-success">${translationsJS && translationsJS.no ? translationsJS.no : 'No'}</span>`;
                        }
                        return html;
                    },
                },
                { "className": "dt-center", "orderable": false, "render":
                    function (data, type, row) {

                        let html = `<div class="d-flex justify-content-center"><a href="/${lang}/edit_user/${ row.id }" class="btn btn-sm btn-info" data-id="${ row.id }" data-toggle="tooltip" title="${translationsJS && translationsJS.edit ? translationsJS.edit : 'Edit'}">
                                        <i class="fas fa-user-edit"></i>
                                    </a>`;

                        if (row.isSuspended == false) {
                            html += `<button class="btn btn-sm btn-warning suspend_user ml-1 mr-1" data-id="${ row.id }" data-toggle="tooltip" title="${translationsJS && translationsJS.suspend ? translationsJS.suspend : 'Suspend'}">
                                        <i class="fas fa-ban"></i>
                                    </button>`
                        } else {
                            html += `<button class="btn btn-sm btn-success unsuspend_user ml-1 mr-1" data-id="${ row.id }" data-toggle="tooltip" title="${translationsJS && translationsJS.unsuspend ? translationsJS.unsuspend : 'Unsuspend'}">
                                        <i class="fas fa-ban"></i>
                                    </button>`
                        }
                        if (row.isSuspected == false) {
                            html += `<button class="btn btn-sm btn-warning suspect_user" data-id="${ row.id }" data-toggle="tooltip" title="${translationsJS && translationsJS.suspect ? translationsJS.suspect : 'Suspect'}">
                                        <i class="fas fa-exclamation-circle"></i>
                                    </button>`
                        } else {
                            html += `<button class="btn btn-sm btn-success raise_suspicion" data-id="${ row.id }" data-toggle="tooltip" title="${translationsJS && translationsJS.raise_suspicion ? translationsJS.raise_suspicion : 'Mark suspicious'} ">
                                        <i class="fas fa-exclamation-circle"></i>
                                    </button>`
                        }
                        html += '</div>';

                        return html;
                    },
                },
            ],
            order: [[ 1, 'desc' ]],
            language: tradsDatatable
        });
    }

     runDatatableWork = () => {
        let that = this;

        let table = $('#datatable-work').DataTable({
            searching: true, 
            info: false,
            lengthChange: false,
            ajax: {
                url: '/admin/occupations',
                type: 'GET',
                dataSrc: function (json) {
                    return json.occupations;
                }
            },
            processing: true,
            initComplete: function(settings, json) {
                that.runDataTableEmpty(table, $('#datatable-work'));
            },
            columns: [
                { data: 'preferredLabel' },
                { "render":
                    function (data, type, row) {
                        let html = row.description && row.description.length > 0 ? truncate(row.description, 100) : '';
                        return html;
                    },
                },
                { "className": "dt-center", "orderable": false, "render":
                    function (data, type, row) {
                        return `<div class="d-flex justify-content-center"><button class="btn btn-info get-info mr-1" data-id="${ row.id }"><i class="fas fa-search-plus"></i></button>
                                <button class="btn btn-info btn-related-trainings-work" data-id="${ row.id }"><i class="fas fa-link"></i> 
                                    ${translationsJS && translationsJS.see_related_trainings ? translationsJS.see_related_trainings : 'See related trainings'}
                                </button></div>`;
                    },
                },
            ],
            //fixedColumns: true,
            order: [[ 0, 'asc' ]],
            language: tradsDatatable
        });
    }

    runDatatableSkill = () => {
        let that = this;
        let table = $('#datatable-skill').DataTable({
            searching: true,
            info: false,
            lengthChange: false,
            ajax: {
                url: '/admin/skills',
                type: 'GET',
                dataSrc: function (json) {
                    return json.skills;
                }
            },
            processing: true,
            initComplete: function(settings, json) {
                that.runDataTableEmpty(table, $('#datatable-skill'));
            },
            columns: [
                { data: 'preferredLabel' },
                { "render":
                    function (data, type, row) {
                        let html = row.description && row.description.length > 0 ? truncate(row.description, 100) : '';
                        return html;
                    },
                },
                { "className": "dt-center", "orderable": false, "render":
                    function (data, type, row) {

                        return `<div class="d-flex justify-content-center"><button class="btn btn-info get-info mr-1" data-id="${ row.id }"><i class="fas fa-search-plus"></i></button>
                                <button class="btn btn-info btn-related-trainings" data-id="${ row.id }"><i class="fas fa-link"></i> 
                                    ${translationsJS && translationsJS.see_related_trainings ? translationsJS.see_related_trainings : 'See related trainings'}
                                </button></div>`;
                    },
                },
            ],
            //fixedColumns: true,
            order: [[ 0, 'asc' ]],
            language: tradsDatatable
        });
    }

    runDatatableTraining = () => {
        let that = this;
        let table = $('#datatable-training').DataTable({
            searching: true,
            info: false,
            lengthChange: false,
            ajax: {
                url: '/admin/trainings',
                type: 'GET',
                dataSrc: function (json) {
                    return json.trainings;
                }
            },
            processing: true,
            initComplete: function(settings, json) {
                that.runDataTableEmpty(table, $('#datatable-training'));
            },
            columns: [
                { data: 'name' },
                { "render":
                    function (data, type, row) {
                        return renderDate(row.createdAt, 'YYYY-MM-DD - HH:mm');
                    },
                },
                { "render":
                    function (data, type, row) {
                        let user = row.user;
                        let html = '-';
                        if (user !== null) {
                            html = user.roles != undefined && getKeyByValue(user.roles, 'ROLE_INSTITUTION') != undefined ? user.username : user.firstname + ' ' + user.lastname;
                            html += `<i class="fas fa-info-circle" data-toggle="tooltip" title="${user.email}"></i>`;
                        }
                        return html;
                    },
                },
                {"className": "dt-center space-nowrap",  "render":
                    function (data, type, row) {
                        let html = '';

                        if (row.isValidated == true)
                            html += `<span class="text-success">${translationsJS && translationsJS.approved ? translationsJS.approved : 'Approved'}</span>`;
                        else if (row.isRejected == true)
                            html += `<span class="text-warning">${ translationsJS && translationsJS.rejected ? translationsJS.rejected : 'Rejected'}</span>`;
                        else 
                            html += translationsJS && translationsJS.pending ? translationsJS.pending : 'Pending'

                        return html;
                    },
                },
                { "className": "dt-center", "orderable": false, "render":
                    function (data, type, row) {

                        return `<button href="#" class="btn btn-primary mt-1 mb-1 see-detail" data-id="${row.id}" data-name="${row.name}" data-description="${row.description}">
                                    ${ translationsJS && translationsJS.trainings_see_details ? translationsJS.trainings_see_details : 'See details'}
                                </button>`;
                    },
                },
            ],
            //fixedColumns: true,
            order: [[ 0, 'asc' ]],
            language: tradsDatatable
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
            let mapContent = document.getElementById('map');
            var map = null;

            if (inputHidden && mapContent) {
                let coords = inputHidden.value;

                if (coords) {
                    if (/^[\],:{}\s]*$/.test(coords.replace(/\\["\\\/bfnrtu]/g, '@').
                        replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g, ']').
                        replace(/(?:^|:|,)(?:\s*\[)+/g, ''))) {
                        coords = JSON.parse(coords);
                        map = L.map('map').setView([coords.lat, coords.lng], 10);
                    } else {
                        map = L.map('map').setView([0, 0], 2);
                    }
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

    getModalDetailsWork = (id, $modal) => {
        let url = '/api/skills_by_occupation/' + id;
        $.ajax({
            type: "GET",
            url: url,
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
                            $(`<h1>${translationsJS && translationsJS.essential_skills ? translationsJS.essential_skills : 'Essential skills'}</h1><ul>${htmlEssential}</ul>`).appendTo($modal.find('.modal-body'));
                            $(`<h1>${translationsJS && translationsJS.optional_skills ? translationsJS.optional_skills : 'Optional skills'}</h1><ul>${htmlOptional}</ul>`).appendTo($modal.find('.modal-body'));

                            $modal.find('.modal-dialog').addClass('modal-lg').addClass('modal-content-work');
                            $modal.modal('show');

                            $modal.find('.modal-content-work [data-toggle="popover"]').popover();
                        }
                    }
                } else {
                    $modal.find('.modal-dialog').addClass('modal-lg').addClass('modal-content-work');
                    $modal.modal('show');
                }
            }
        });
    }

    getDetails = () => {

        let that = this;
        let lang = $('body').attr('lang');

        $('body').on('click', '#content-work .get-info', function() {
            let $modal = $('#common-modal');
            let id = $(this).attr('data-id');
            if (!id) return false;
            that.getModalDetailsWork(id, $modal);
        });

        $('body').on('click', '#content-skill .get-info', function() {

            let $modal = $('#common-modal');
            let id = $(this).attr('data-id');
            let url = '/apip/skills/' + id;
            $.ajax({
                type: "GET",
                url: url,
                success: function (data, textStatus, jqXHR) { 
                    if (data) {
                        $modal.find('.modal-title').html(data.preferredLabel ? data.preferredLabel : '');
                        $(`<p>${data.description ? data.description : ''}</p>`).appendTo($modal.find('.modal-body'));
                        $(`<p><strong>URI :</strong> <a href="${data.conceptUri ? data.conceptUri : '#'}">${data.conceptUri ? data.conceptUri : 'NC'}</a></p>`).appendTo($modal.find('.modal-body'));
                        $(`<p><strong>${translationsJS && translationsJS.essential_skills ? translationsJS.essential_skills : 'Essential skills'}</strong> <em>${data.skillType ? data.skillType : 'NC'}</em></p>`).appendTo($modal.find('.modal-body'));
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

                        let location = ``;
                        if (data.location != undefined && data.location != '') {
                            location = data.location.replace(/&/g, "&amp;")
                                            .replace(/</g, "&lt;")
                                            .replace(/>/g, "&gt;")
                                            .replace(/"/g, "&quot;")
                                            .replace(/'/g, "&#039;");
                        }

                        let dateStart = data.startAt ? renderDate(data.startAt) : false;
                        let dateEnd = data.endAt ? renderDate(data.endAt) : false;

                        let institutionName = data.user && data.user.username ? data.user.username : false;
                        let occupationId = data.occupation && data.occupation.id ? data.occupation.id : false;

                        let modalBodyHTML = `<div class="row">
								<div class="col-md-12 detail-training">

									<div class="row mb-3">
										<div class="col-lg-4">
											<span class="title">${translationsJS && translationsJS.name ? translationsJS.name : 'Name'}</span>
										</div>
										<div class="col-lg-8">
											<span>${data.name ? data.name : 'N/A'}</span>
										</div>
									</div>

									<div class="row mb-3">
										<div class="col-lg-4">
											<span class="title">${translationsJS && translationsJS.institution_name ? translationsJS.institution_name : 'Institution name'}</span>
										</div>
										<div class="col-lg-8">
											<span>${institutionName ? institutionName : 'N/A'}</span>
										</div>
									</div>

									<div class="row mb-3">
										<div class="col-lg-4">
											<span class="title">${translationsJS && translationsJS.location ? translationsJS.location : 'Location'}</span>
										</div>
										<div class="col-lg-8">
                                            <span id="location-modal">N/A</span>
                                            <input type="hidden" value='${location ? location : ''}' id="training_address_hidden" />
                                            <div id="map-modal"></div>
										</div>
									</div>

									<div class="row mb-3">
										<div class="col-lg-4">
											<span class="title">${translationsJS && translationsJS.duration ? translationsJS.duration : 'Location'}</span>
										</div>
										<div class="col-lg-8">
											<span>${data.duration ? data.duration : 'N/A'}</span>
										</div>
									</div>

                                    <div class="row mb-3">
                                        <div class="col-lg-4">
                                            <span class="title">${translationsJS && translationsJS.date ? translationsJS.date : 'Date'}</span>
                                        </div>
                                        <div class="col-lg-8">
                                            <span>From</span>
                                            <span>${dateStart ? dateStart : 'N/A'}</span>
                                            <span>to</span>
                                            <span>${dateEnd ? dateEnd : 'N/A'}</span>
                                        </div>
                                    </div>

									<div class="row mb-3">
										<div class="col-lg-4">
											<span class="title">${translationsJS && translationsJS.description ? translationsJS.description : 'Description'}</span>
										</div>
										<div class="col-lg-8">
											<p class="text-justify m-0">
                                                ${data.description ? data.description : 'N/A'}
											</p>
										</div>
									</div>

									<div class="row mb-3">
										<div class="col-lg-4">
											<span class="title">${translationsJS && translationsJS.price ? translationsJS.price : 'Price'}</span>
										</div>
										<div class="col-lg-8">
                                        <span>${data.price ? data.price : 'N/A'} ${data.currency && data.price ? data.currency.toUpperCase() : ''}</span>
										</div>
									</div>
									<div class="row mb-3">
										<div class="col-lg-4">
											<span class="title">${translationsJS && translationsJS.occupation ? translationsJS.occupation : 'Occupation'}</span>
										</div>
										<div class="col-lg-8">
                                            ${(data.occupation != undefined && data.occupation != null && data.occupation.preferredLabel) ?
											`<a href="#" class="lk-open-work" data-id="${occupationId ? occupationId : ''}">
                                                ${(data.occupation != undefined && data.occupation != null && data.occupation.preferredLabel) ?
                                                    data.occupation.preferredLabel :
                                                    'N/A'
                                                }
                                            </a>` : 'N/A'}
										</div>
									</div>
									<div class="mb-3">
                                        <span class="required-skills d-block mb-3 title">${translationsJS && translationsJS.required_skills ? translationsJS.required_skills : 'Required skills'}</span>
                                        <ul>
                                            ${requireSkillsHTML && requireSkillsHTML.length > 0 ? requireSkillsHTML : 'N/A'}
                                        </ul>
									</div>

									<div class="mb-3">
                                    <span class="required-skills d-block mb-3 title">${translationsJS && translationsJS.acquired_skills ? translationsJS.acquired_skills : 'Acquired skills'}</span>
                                        <ul>
                                            ${acquireSkillsHTML && acquireSkillsHTML.length > 0 ? acquireSkillsHTML : 'N/A'}
                                        </ul>
									</div>
								</div>
							</div>`;


                        let editHTML = `<div class="row blc-edit-training">
                                            <div class="col-md-12 text-right">
                                                <a href="/${lang}/training/edit/${id}" class="btn btn-primary">${translationsJS && translationsJS.edit ? translationsJS.edit : 'Edit'}</a>
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
                success: function (data, textStatus, jqXHR) { 
                    if (data) {
                        let occupation = data.occupation ? data.occupation : false;
                        $modal.find('.modal-title').html(occupation && occupation.preferredLabel ? occupation.preferredLabel : '');
                        let dataTrainings = data.trainings && data.trainings.length > 0 ? data.trainings : false;
                        if (dataTrainings && dataTrainings.length > 0) {
                            let trainingsHTML = '<ul>'
                            for (let k in dataTrainings) {
                                let institutionName = dataTrainings[k].user && dataTrainings[k].user.username ? dataTrainings[k].user.username : false;
                                trainingsHTML += `<li class="mb-2"><span class="lk-open-training" data-id="${dataTrainings[k].id ? dataTrainings[k].id : ''}">${dataTrainings[k].name ? dataTrainings[k].name : ''} ${institutionName ? '(' + institutionName + ')' : ''}</span></li>`;
                                
                                if (k == dataTrainings.length - 1) {
                                    trainingsHTML += '</ul>'
                                    $('#common-modal').find('.modal-dialog').addClass('modal-lg');
                                    $(trainingsHTML).appendTo($modal.find('.modal-body'));
                                    $('#common-modal').modal('show');
                                }
                            }
                        } else {
                            let no_trainings = translationsJS && translationsJS.no_trainings ? translationsJS.no_trainings : 'No training';
                            $('<p>' + no_trainings + '</p>').appendTo($modal.find('.modal-body'));
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
                            let no_trainings = translationsJS && translationsJS.no_trainings ? translationsJS.no_trainings : 'No training';
                            $('<p>' + no_trainings + '</p>').appendTo($modal.find('.modal-body'));
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

        /* ouvre detail job a partir de la modal de formation */
        $('body').on('click', '#common-modal .detail-training .lk-open-work, #common-modal-2 .detail-training .lk-open-work', function(e) {
            e.preventDefault();
            let $modal = $('#common-modal-3');
            let id = $(this).attr('data-id');
            if (!id) return false;
            that.getModalDetailsWork(id, $modal);
        });
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
            bootbox.confirm({message : translationsJS && translationsJS.are_you_sure_you_want_to_approve_this_training ? translationsJS.are_you_sure_you_want_to_approve_this_training : 'Comfirm', buttons : { cancel : { label : translationsJS && translationsJS.cancel ? translationsJS.cancel : 'Cancel'}, confirm : { label : translationsJS && translationsJS.yes ? translationsJS.yes : 'Yes'}}, callback : function(result) {
                if (result == true) {
                    $.ajax({
                        type: "POST",
                        url: url,
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
            bootbox.confirm({message : translationsJS && translationsJS.do_you_really_want_to_reject_this_training ? translationsJS.do_you_really_want_to_reject_this_training : 'Comfirm', buttons : { cancel : { label : translationsJS && translationsJS.cancel ? translationsJS.cancel : 'Cancel'}, confirm : { label : translationsJS && translationsJS.yes ? translationsJS.yes : 'Yes'}}, callback : function(result) {
                    if (result == true) {
                        $.ajax({
                            type: "POST",
                            url: url,
                            success: function (data, textStatus, jqXHR) {
                                if (data.result != undefined && data.result == true) {
                                    let $td = $button.closest('tr').find('td:eq(3)');
                                    if ($td && $td.length > 0)
                                        $td.html('<span class="text-warning">Rejected</span>');
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
            bootbox.confirm({message : translationsJS && translationsJS.are_you_sure_you_want_to_delete_this_training ? translationsJS.are_you_sure_you_want_to_delete_this_training : 'Comfirm', buttons : { cancel : { label : translationsJS && translationsJS.cancel ? translationsJS.cancel : 'Cancel'}, confirm : { label : translationsJS && translationsJS.yes ? translationsJS.yes : 'Yes'}}, callback : function(result) {
                    if (result == true) {
                        $.ajax({
                            type: "POST",
                            url: url,
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
     * Actions sur les users
     */
     runUsersTasksActions = () => {
        // APPROVE
        $('body').on('click', 'button.approve_user', function() {
            let $button = $(this);
            let id = $button.attr('data-id');
            let url = '/admin/approve_user/' + id;
            bootbox.confirm({message : translationsJS && translationsJS.are_you_sure_you_want_to_approve_this_user ? translationsJS.are_you_sure_you_want_to_approve_this_user : 'Confirm', buttons : { cancel : { label : translationsJS && translationsJS.cancel ? translationsJS.cancel : 'Cancel'}, confirm : { label : translationsJS && translationsJS.yes ? translationsJS.yes : 'Yes'}}, callback : function(result) {
                if (result == true) {
                    $.ajax({
                        type: "POST",
                        url: url,
                        success: function (data, textStatus, jqXHR) {
                            if (data.result != undefined && data.result == true) {
                                let $td = $button.closest('tr').find('td:eq(2)');
                                if ($td && $td.length > 0)
                                    $td.html('<span class="text-success">Approve</span>');
                                $button.removeClass('btn-success').addClass('btn-warning');
                                $button.find('i, svg').removeClass('fa-check').addClass('fa-ban');
                                $button.removeClass('approve_user').addClass('reject_user').attr('data-original-title', 'Reject');
                            } else {
                                bootbox.alert('An error occured');
                            }
                        }
                    });
                }
            }});
        });
        // REJECT
        $('body').on('click', 'button.reject_user', function() {
            let $button = $(this);
            let id = $button.attr('data-id');
            let url = '/admin/reject_user/' + id;
            bootbox.confirm({message : translationsJS && translationsJS.do_you_really_want_to_reject_this_user ? translationsJS.do_you_really_want_to_reject_this_user : 'Confirm', buttons : { cancel : { label : translationsJS && translationsJS.cancel ? translationsJS.cancel : 'Cancel'}, confirm : { label : translationsJS && translationsJS.yes ? translationsJS.yes : 'Yes'}}, callback : function(result) {
                    if (result == true) {
                        $.ajax({
                            type: "POST",
                            url: url,
                            success: function (data, textStatus, jqXHR) {
                                if (data.result != undefined && data.result == true) {
                                    let $td = $button.closest('tr').find('td:eq(2)');
                                    if ($td && $td.length > 0)
                                        $td.html('<span class="text-warning">Reject</span>');
                                    $button.removeClass('btn-warning').addClass('btn-success');
                                    $button.find('i, svg').removeClass('fa-ban').addClass('fa-check');
                                    $button.removeClass('reject_user').addClass('approve_user').attr('data-original-title', 'Approve');
                                } else {
                                    bootbox.alert('An error occured');
                                }
                            }
                        });
                    }
                }});
        });
        // DELETE
        $('body').on('click', 'button.delete_user', function() {
            let $button = $(this);
            let id = $button.attr('data-id');
            let url = '/admin/delete_user/' + id;
            bootbox.confirm({message : translationsJS && translationsJS.are_you_sure_you_want_to_delete_this_user ? translationsJS.are_you_sure_you_want_to_delete_this_user : 'Confirm', buttons : { cancel : { label : translationsJS && translationsJS.cancel ? translationsJS.cancel : 'Cancel'}, confirm : { label : translationsJS && translationsJS.yes ? translationsJS.yes : 'Yes'}}, callback : function(result) {
                    if (result == true) {
                        $.ajax({
                            type: "POST",
                            url: url,
                            success: function (data, textStatus, jqXHR) {
                                if (data.result != undefined && data.result == true) {
                                    $('#datatable-institution').DataTable()
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
            bootbox.confirm({message : translationsJS && translationsJS.are_you_sure_you_want_to_suspend_this_user ? translationsJS.are_you_sure_you_want_to_suspend_this_user : 'Confirm', buttons : { cancel : { label : translationsJS && translationsJS.cancel ? translationsJS.cancel : 'Cancel'}, confirm : { label : translationsJS && translationsJS.yes ? translationsJS.yes : 'Yes'}}, callback : function(result) {
                if (result == true) {
                    $.ajax({
                        type: "POST",
                        url: url,
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
            bootbox.confirm({message : translationsJS && translationsJS.are_you_sure_you_want_to_unsuspend_this_user ? translationsJS.are_you_sure_you_want_to_unsuspend_this_user : 'Confirm', buttons : { cancel : { label : translationsJS && translationsJS.cancel ? translationsJS.cancel : 'Cancel'}, confirm : { label : translationsJS && translationsJS.yes ? translationsJS.yes : 'Yes'}}, callback : function(result) {
                    if (result == true) {
                        $.ajax({
                            type: "POST",
                            url: url,
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
            bootbox.confirm({message : translationsJS && translationsJS.are_you_sure_you_want_to_mark_this_user_as_suspect ? translationsJS.are_you_sure_you_want_to_mark_this_user_as_suspect : 'Confirm', buttons : { cancel : { label : translationsJS && translationsJS.cancel ? translationsJS.cancel : 'Cancel'}, confirm : { label : translationsJS && translationsJS.yes ? translationsJS.yes : 'Yes'}}, callback : function(result) {
                    if (result == true) {
                        $.ajax({
                            type: "POST",
                            url: url,
                            success: function (data, textStatus, jqXHR) {
                                if (data.result != undefined && data.result == true) {
                                    let $td = $button.closest('tr').find('td:eq(6)');
                                    if ($td && $td.length > 0) {
                                        let yes = translationsJS && translationsJS.yes ? translationsJS.yes : 'Yes';
                                        $td.html('<span class="text-warning">yes</span>');
                                    }
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
            bootbox.confirm({message : translationsJS && translationsJS.are_you_sure_you_want_to_raise_suspicion_for_this_user ? translationsJS.are_you_sure_you_want_to_raise_suspicion_for_this_user : 'Confirm', buttons : { cancel : { label : translationsJS && translationsJS.cancel ? translationsJS.cancel : 'Cancel'}, confirm : { label : translationsJS && translationsJS.yes ? translationsJS.yes : 'Yes'}}, callback : function(result) {
                    if (result == true) {
                        $.ajax({
                            type: "POST",
                            url: url,
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

    /*runMapTraining = () => { 

        $('#common-modal').on('shown.bs.modal', function (e) {
            let blcMap = e.target.querySelector('.blc-map');
            if (blcMap) {
                let mapContent = blcMap.querySelector('.map');
                let trainingAddress = blcMap.querySelector('.training_address');
                let trainingAddressHidden = blcMap.querySelector('.training_address_hidden');

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
   }*/

   
    /**
     * Affichage carte modal
     */
     runMapModal = (numModal = '') => { 
         
         let inputHidden = document.getElementById('training_address_hidden');
         let modalId = ''
         if (numModal != '') {
             modalId = '#common-modal-' + numModal;
            } else {
                modalId = '#common-modal';
            }
            
        let locationModal = document.querySelector(modalId + ' #location-modal');
        let mapContent = document.querySelector("#map-modal");

        if (!mapContent || mapContent.innerHTML != "") {
            locationModal.innerHTML = coords ? coords : 'N/A';
            $('#map-modal').hide();
        };

        var map = null;
        let coords = inputHidden.value;

        if (!coords) {
            locationModal.innerHTML = coords ? coords : 'N/A';
            $('#map-modal').hide();
        };

        if (/^[\],:{}\s]*$/.test(coords.replace(/\\["\\\/bfnrtu]/g, '@').
        replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g, ']').
        replace(/(?:^|:|,)(?:\s*\[)+/g, '')) && coords.length > 0) {

            coords = JSON.parse(coords);


            let geocoder = new google.maps.Geocoder();
            var latlng = new google.maps.LatLng(0, 0);
            var mapOptions = {
                zoom: 1,
                center: latlng,
                scrollwheel: false,
                scaleControl: false,
                mapTypeControl: false,
                navigationControl: false,
                streetViewControl: false,
                fullscreenControl: false,
            }
            map = new google.maps.Map(mapContent, mapOptions);

            // Affichage du marker
            let marker = new google.maps.Marker({
                position: {lat: coords.lat, lng: coords.lng},
                map,
                title: coords.title,
            });
            locationModal.innerHTML = coords.title;
            map.setCenter({lat: coords.lat, lng: coords.lng});
            map.setZoom(12);
        } else {
            locationModal.innerHTML = coords ? coords : 'N/A';
            $('#map-modal').hide();
        }
    }

    init = function() {

        let _this = this;
        let hrefLocation = window.location.href;

        this.runDatatableHome();
        this.runDatatableTask();
        this.runDatatableInstitution();
        this.runDatatableUsers();
        this.runDatatableWork();
        this.runDatatableSkill();
        this.runDatatableTraining();
        this.seeDetailWork();
        //this.runMap();
        //this.runMapTraining();
        this.getDetails();
        this.runUsersActions();
        this.runTrainingsActions();
        this.runUsersTasksActions();

        $('[data-toggle="tooltip"]').tooltip();

        $('#common-modal').on('shown.bs.modal', function (e) {
            if ($(this).find('#training_address_hidden').length > 0) {
                _this.runMapModal();
            }
        });

        $('#common-modal-2').on('shown.bs.modal', function (e) {
            if ($(this).find('#training_address_hidden').length > 0) {
                _this.runMapModal(2);
            }
        });

        $('#common-modal').on('hidden.bs.modal', function (e) {
            $(this).find('.modal-title').children().remove();
            $(this).find('.modal-body').children().remove();
            $(this).find('.modal-footer').find('.blc-edit-training').remove();
            $(this).find('.modal-dialog').removeClass('modal-lg');

            if ($('.modal.show').length > 0) {
                $('body').addClass('modal-open');
            }
        });
        
        $('#common-modal-2').on('hidden.bs.modal', function (e) {
            $(this).find('.modal-title').children().remove();
            $(this).find('.modal-body').children().remove();
            $(this).find('.modal-footer').find('.blc-edit-training').remove();
            $(this).find('.modal-dialog').removeClass('modal-lg');

            if ($('.modal.show').length > 0) {
                $('body').addClass('modal-open');
            }
        });
        
        $('#common-modal-3').on('hidden.bs.modal', function (e) {
            $(this).find('.modal-title').children().remove();
            $(this).find('.modal-body').children().remove();
            $(this).find('.modal-footer').find('.blc-edit-training').remove();
            $(this).find('.modal-dialog').removeClass('modal-lg');

            if ($('.modal.show').length > 0) {
                $('body').addClass('modal-open');
            }
        });

        // TABS
        let hash = location.hash.replace(/^#/, ''); 
        if (hash) {
            $('#admin [data-toggle="tab"][href="#' + hash + '"]').tab('show');
        }
        $('#admin [data-toggle="tab"]').on('shown.bs.tab', function (e) {

            // Ajustement des colonnes
            $('#datatable-task').DataTable().columns.adjust().draw();
            $('#datatable-work').DataTable().columns.adjust().draw();
            $('#datatable-skill').DataTable().columns.adjust().draw();
            $('#datatable-training').DataTable().columns.adjust().draw();
            $('#datatable-user').DataTable().columns.adjust().draw();

            /*if (e.target.hash == "#content-personal_informations") {
                _this.runMap();
            }*/
            window.location.hash = e.target.hash;
        });

        // CREATE USER
        if (hrefLocation.indexOf('create_user') != -1) {
           /* _this.runMap();*/
        }
    }
}

$(document).ready(function() {
    let admin = new Admin();
    admin.init();
});