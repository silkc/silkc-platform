//import $ from 'jquery';
import $ from 'jquery';
import autocomplete from 'autocompleter';

//import '../../css/bootstrap-extended.css';

// any CSS you import will output into a single css file (app.css in this case)
import '../../scss/elements/header.scss';
import '../../scss/recruiter.scss';


const bootstrap = require('bootstrap');
//require('popper');
var moment = require('moment');
require('chart.js');
require('@fortawesome/fontawesome-free/js/all.min');
const bootbox = require('bootbox/bootbox');

require('bootstrap-select');
import 'bootstrap-select/dist/css/bootstrap-select.min.css'

class Recruiter {
    instanceProperty = "Recruiter";
    boundFunction = () => {
        return this.instanceProperty;
    }

    tplSkill = (skill, rmv = false, associated = false) => {

        return `<li class="list-group-item">
            <div class="d-flex flex-nowrap justify-content-between">
                <div>
                    <span>${skill.preferredLabel}</span>
                </div>
                <div>
                    <a href="#" class="${associated ? 'associated' : ''} ${rmv ? 'rmv' : 'add'}" data-id="${skill.id}" data-name="${skill.preferredLabel}">
                        <i class="fas ${rmv ? 'fa-minus' : 'fa-plus'}"></i>
                    </a>
                </div>
            </div>
        </li>`;
    }

    /**
     * Duplicate a position
     */
    duplicatePosition = () => {
        $('body').on('click', '#list-positions .clone', function(e) {
            e.preventDefault();

        });
    }

    /**
     * Ajout de compétences à un position
     */
     addSkillsToPosition = () => {

        let _this = this; 

        $('body').on('click', '.add-skill button', function() {

            let status = true;
            let type = $(this).closest('.add-skill').attr('data-type');
            let skillNameInput = $(this).closest('.add-skill').find('.input-autocomplete');
            let skillIdInput = skillNameInput.siblings('input[type="hidden"]');
            let skillName = skillNameInput.val();
            let skillId = skillIdInput.val();
            let ul = type == "required" ? $('#skills-required') : $('#skills-not-occupations-acquired');
            
            if (!skillName) return false;

            status = _this.addSkillToHiddenField(skillId);
            if (status === false) {
                skillNameInput.val('');
                skillIdInput.val('');
                return false;
            }

            let ulOccupationsAcquired = $('#skills-occupations');
            let statusAppend = true;

            if (type != "required" && ulOccupationsAcquired) {
                if (ulOccupationsAcquired.find('li').length > 0) {
                    ulOccupationsAcquired.find('li').each(function(k) {
                        let li = $(this);
                        if (li.find('[data-id="' + skillId + '"]').length > 0) {
                            li.find('a').trigger('click');
                            statusAppend = false;
                        }
                    });
                }
            }

            skillNameInput.val('');
            skillIdInput.val('');

            if (!statusAppend) return false;
            let data = {id: skillId, preferredLabel: skillName};
            let html = _this.tplSkill(data, true);
            $(html).appendTo(ul);

            _this.resetAffectedUsers();
        });
    }


     addSkillToHiddenField = (skillId) => {
        let inputSkillsList = $('body').find('#hidden_positionSkills');
        let skillsList = JSON.parse(inputSkillsList.val()) || {};
        skillId = parseInt(skillId);

        if (skillsList.includes(skillId))
            return false;

        skillsList = [skillId, ...skillsList];

        inputSkillsList.val(JSON.stringify(skillsList))
    }


    /**
     * Suppression de compétences à un position
     */
     removeSkillToHiddenField = (skillId) => {

        let _this = this;
        let inputSkillsList = $('#hidden_positionSkills');
        skillId = parseInt(skillId);

        if (skillId && inputSkillsList) {
            let skillsList = inputSkillsList.val();
            if (skillsList) {
                skillsList = JSON.parse(skillsList);

                if (skillsList.includes(skillId)) {
                    skillsList = skillsList.filter(function (el) {
                        return el != skillId;
                    });
                    inputSkillsList.val(JSON.stringify(skillsList));
                }
            }
        }
    }

    removeSkillsToPosition = () => {

        let _this = this;

        $('body').on('click', '.ul-skills:not(#skills-occupations) .rmv', function(e) {
            e.preventDefault();

            let skillId = $(this).attr('data-id');
            let inputSkillsList = $('#hidden_positionSkills');

            _this.removeSkillToHiddenField(skillId);
            $(this).closest('.list-group-item').remove();
            _this.resetAffectedUsers();
        });
    }


    getSkillsFromOccupation = () => {

        let _this = this;

        $('body').on('change', '.occupations-select select', function () {
            let select = $(this);
            let occupation_id = $(this).val();
            let description = select.find('option:selected').attr('data-description') || '';
            let baseUrl = '/apip/occupation_skills';
            let params = $.param({'occupation': occupation_id});
            let url = `${baseUrl}?${params}`;
            let ul = $('#skills-occupations');
            let ulNotOccupation = $('#skills-not-occupations-acquired');

            // Si aucune selection d'un metier
            if (!occupation_id || !url) {
                // Suppression de la description du métier sélectionné
                select.closest('.form-group').find('p.occupation_description').remove();

                if (ul.find('li').length > 0) {
                    ul.find('li').each(function(k) {
                        let li = $(this);
                        if (li.find('.associated').length > 0) {
                            $(ulNotOccupation).append(li);
                        }

                        if (k == ul.find('li').length - 1) {
                            ul.children().remove();
                        }
                    });
                }

                // Affichage "tout associer/tout desassocier"
                if (ul.find('li').length == 0)
                    $('body').find('.skills-associated').hide();
                else
                    $('body').find('.skills-associated').show();

                return false;
            } else {
                select.closest('.form-group').find('p.occupation_description').remove();
                select.closest('.form-group').append('<p class="occupation_description mt-2" style="font-size: 0.9rem"><em>' + description + '</em></p>');
            }

            let skillsList = {};
            skillsList.acquired = [];
            let inputSkillsList = $('body').find('#hidden_positionSkills');
            if (inputSkillsList) {
                skillsList = inputSkillsList.val();
                if (skillsList && skillsList.length > 0) {
                    skillsList = JSON.parse(inputSkillsList.val());
                }
            }

            $.ajax({
                type: "GET",
                url: url,
                success: function (data, textStatus, jqXHR) {
                    // Si il y a des comptences liées (dans la partie competences liées au metier)
                    // on les deplace dans la partie non liée au metier
                    let dfd = $.Deferred();
                    if (ul.find('li').length > 0) {
                        ul.find('li').each(function(k) {
                            let li = $(this);
                            if (li.find('.associated').length > 0) {
                                $(ulNotOccupation).append(li);
                            }

                            if (k == ul.find('li').length - 1) {
                                ul.children().remove();
                                dfd.resolve();
                            }
                        });
                    } else {
                        dfd.resolve();
                    }

                    dfd.done(function( n ) {
                        let html = ``;
                        if (data && data.length > 0) {
                            for (let i = 0; i < data.length; i++) {
                                if (skillsList.includes(data[i].skill.id)) {
                                    if (ulNotOccupation.find('.rmv[data-id="' + data[i].skill.id + '"]').length > 0) {
                                        ulNotOccupation.find('.rmv[data-id="' + data[i].skill.id + '"]').closest('.list-group-item').remove();
                                    }

                                    html += _this.tplSkill(data[i].skill, true, true);
                                } else
                                    html += _this.tplSkill(data[i].skill);

                                if (i == data.length - 1) {
                                    $(html).appendTo(ul);

                                    // Affichage "tout associer/tout desassocier"
                                    if (ul.find('li').length > 0) {
                                        $('body').find('.skills-associated').show();
                                    } else {
                                        $('body').find('.skills-associated').hide();
                                    }
                                    if (ul.find('.add').length > 0) {
                                        $('body').find('.skills-associated').attr('id', 'all-associated').html(translationsJS && translationsJS.all_associated ? translationsJS.all_associated : 'All associated')
                                    } else {
                                        $('body').find('.skills-associated').attr('id', 'all-unassociated').html(translationsJS && translationsJS.all_unassociated ? translationsJS.all_unassociated : 'All unassociated')
                                    }
                                }
                            }
                        }
                    });
                },
                error : function(jqXHR, textStatus, errorThrown){},
                complete : function(jqXHR, textStatus ){}
            });

            _this.resetAffectedUsers();
        });

        let idOccupation = $('.occupations-select select').val();
        if (idOccupation) {
            $('.occupations-select select').val(idOccupation).trigger('change');
        }
    }

    addSkillOccupation = () => {

        let _this = this;

        $('body').on('click', '#all-associated', function(e) {
            e.preventDefault();

            let ul = $('#skills-occupations');
            if (ul.find('li').length > 0) {
                ul.find('li').each(function(k) {
                    let skill = $(this).find('a.add');
                    let skillId = skill.attr('data-id');
                    
                    skill.addClass('associated');
                    
                    skill.toggleClass('add rmv');
                    skill.children().remove();
                    skill.append('<i class="fas fa-minus"></i>');
                    
                    _this.addSkillToHiddenField(skillId);
                });

                $(this).attr('id', 'all-unassociated').html(translationsJS && translationsJS.all_unassociated ? translationsJS.all_unassociated : 'All unassociated')
            }

            _this.resetAffectedUsers();
        });

        $('body').on('click', '#all-unassociated', function(e) {
            e.preventDefault();

            let ul = $('#skills-occupations');
            if (ul.find('li').length > 0) {
                ul.find('li').each(function(k) {
                    let skill = $(this).find('a.rmv');
                    let skillId = skill.attr('data-id');
                    let type = skill.attr('data-type');
            
                    skill.removeClass('associated');

                    skill.toggleClass('rmv add');
                    skill.children().remove();
                    skill.append('<i class="fas fa-plus"></i>');
                    
                    _this.removeSkillToHiddenField(skillId);
                });

                $(this).attr('id', 'all-associated').html(translationsJS && translationsJS.all_associated ? translationsJS.all_associated : 'All associated')
            }

            _this.resetAffectedUsers();
        });

        $('body').on('click', '#skills-occupations .add', function(e) {
            e.preventDefault();

            let skillId = $(this).attr('data-id');
            let type = $(this).attr('data-type');
            
            $(this).addClass('associated');
            
            $(this).toggleClass('add rmv');
            $(this).children().remove();
            $(this).append('<i class="fas fa-minus"></i>');
            
            _this.addSkillToHiddenField(skillId);
            _this.resetAffectedUsers();
        });
        
        $('body').on('click', '#skills-occupations .rmv', function(e) {
            e.preventDefault();
            
            let skillId = $(this).attr('data-id');
            let type = $(this).attr('data-type');
            
            $(this).removeClass('associated');

            $(this).toggleClass('rmv add');
            $(this).children().remove();
            $(this).append('<i class="fas fa-plus"></i>');

            _this.removeSkillToHiddenField(skillId);
            _this.resetAffectedUsers();
        });
    }

    resetAffectedUsers = () => {
        const $button = $('button#display-affected-users');
        const $resultContainer = $('p#affected-users');
        $button.show();
        $resultContainer.addClass('hidden');
    };

    
    /**
     * Autocompletion inputs 
     * (ajouter l'attribut data-url et la class input-autocomplete à l'input de type text)
     */
     runAutocompletion = () => {
        let inputs = document.getElementsByClassName('input-autocomplete');
        let datas = {};
        let lang = $('body').attr('lang');

        let runAutocomplete = function (data, input) {

            let elemsDisabled = $(input).closest('form').find('.disabled-search');
            let name = input.getAttribute('name');
            let hiddenField = document.getElementById('hidden_' + name);
            let loader = document.getElementById('loader_' + name);
            let minLength = 2;

            $(input).closest('form').attr('autocomplete', 'off');

            autocomplete({
                input: input,
                minLength: minLength,
                emptyMsg: 'No elements found',
                render: function(item, currentValue) {
                    /*if (item.translations) {
                        item = item.translations[Object.keys(item.translations)[0]]
                    }*/
                    let div = document.createElement('div');
                    div.dataset.id = item.id;                    
                    div.textContent = (item.preferredLabel != undefined) ? item.preferredLabel : (item.name != undefined) ? item.name : ''; // preferredLabel => table ESCO, name => table position
                    return div;

                },
                fetch: function(text, callback) {
                    text = text.toLowerCase();
                    let suggestions = data.filter(n => (n.preferredLabel != undefined) ? n.preferredLabel.toLowerCase().includes(text) : (n.name != undefined) ? n.name.toLowerCase().includes(text) : '' );
                    callback(suggestions);
                },
                onSelect: function(item) {
                    if ($(item).attr('data-associated') == true) return false;

                    input.value = (item.preferredLabel != undefined) ? item.preferredLabel : item.name;
                    elemsDisabled.prop('disabled', false);
                    if (hiddenField && item.id) {
                        hiddenField.value = item.id;
                    }
                }
            });

            /* Si on vide le champs
            On desactive le bouton de recherche */
            input.addEventListener('keyup', function() {
                let search = this.value.toLowerCase();
                if (!search || search.length == 0) {
                    input.value = '';
                    if (hiddenField) {
                        hiddenField.value = '';
                        elemsDisabled.prop('disabled', true);
                    }
                }
            });

            /* Si on sort du champs de recherche sans avoir sélectionner un item, on sélectionne la première proposition
            Si il n'y a pas de propositions, on vide le champs */
            input.addEventListener('focusout', function() {
                let search = this.value.toLowerCase();
                let suggestions = data.filter(n => (n.preferredLabel != undefined) ? n.preferredLabel.toLowerCase().startsWith(search) : n.name.toLowerCase().startsWith(search));
                if (suggestions && suggestions.length > 0 && search.length > 0) {
                    let suggestion = suggestions[0];
                    input.value = (suggestion.preferredLabel != undefined) ? suggestion.preferredLabel : (suggestion.name != undefined) ? suggestion.name : '';
                    if (hiddenField) hiddenField.value = (suggestion.id != undefined) ? suggestion.id : '';
                    elemsDisabled.prop('disabled', false);
                } else {
                    input.value = '';
                    if (hiddenField) {
                        hiddenField.value = '';
                        elemsDisabled.prop('disabled', true);
                    }
                }
            });

            if (loader) {
                loader.style.display = 'none';
                input.disabled = false;
            }
        }
        
        if (inputs) {
            for (var i = 0; i < inputs.length; i++) {
                let input = inputs[i];
                let baseUrl = input.getAttribute('data-url');
                if (!baseUrl.includes("trainings"))
                    baseUrl = baseUrl + "/main/locale/" + lang;
                let formats = input.getAttribute('data-formats') || 'json';
                let pagination = input.getAttribute('data-pagination') || false;
                let params = $.param({'formats': formats, 'pagination': pagination});

                let url = `${baseUrl}?${params}`;


                if (url && input) {
                    if (datas
                        && (
                            (url.includes("skills") && 'skills' in datas)
                            || (url.includes("occupations") && 'occupations' in datas)
                            || (url.includes("positions") && 'positions' in datas)
                        )) {
                        
                        let data = {};
                        if (url.includes("skills"))
                            data = JSON.parse(datas.skills);
                        if (url.includes("occupations"))
                            data = JSON.parse(datas.occupations);
                        if (url.includes("positions"))
                            data = JSON.parse(datas.positions);

                        runAutocomplete(data, input);
                        
                    } else {
                        $.ajax({
                            type: "GET",
                            url: url,
                            success: function (data, textStatus, jqXHR) {
                                if (url.includes("skills"))
                                    datas.skills = JSON.stringify(data);
                                if (url.includes("occupations"))
                                    datas.occupations = JSON.stringify(data);
                                if (url.includes("positions"))
                                    datas.positions = JSON.stringify(data);

                                runAutocomplete(data, input);
                            }
                        });
                    }
                }
            }
        }
    }
  
    /**
     * Affichage des messages de mises à jour
     */
     displayMessage = () => {
        if ($('.message-flash').children().length > 0) {
            setTimeout(function() {
                $('.message-flash').children().remove();
            }, 2000);
        }
    }
        
    /**
     * Affichage carte
     */
     runMap = () => {

        let inputHidden = document.getElementById('recruiter_address');
        var map = null;

        if (inputHidden) {
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
                placeholder: translationsJS && translationsJS.search_here ? translationsJS.search_here : 'Search here...',
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
        
    /**
     * Affichage carte modal
     */
     runMapModal = () => {

        let inputHidden = document.getElementById('recruiter-location');
        var map = null;
        map = L.map('map-modal').setView([0, 0], 1);
        let geocoder = L.Control.Geocoder.nominatim();
        
        if (inputHidden) {
            let control = L.Control.geocoder({
                collapsed: false,
                placeholder: translationsJS && translationsJS.search_here ? translationsJS.search_here : 'Search here...',
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
            
            document.getElementById('searchmap-modal').appendChild(document.querySelector('.leaflet-control-geocoder.leaflet-bar'));
        }
    }
    

    runModalAddUser = () => {
        let _this = this;

        $('body').on('click', '.btn-modal-add-user', function() {
            let $modal = $('#common-modal');

            if ($modal) {
                $modal.find('.modal-title').html(translationsJS && translationsJS.location ? translationsJS.location :'Add institution');
                let buttonSubmit = `<button type="button" class="btn btn-primary btn-add-user">${translationsJS && translationsJS.add ? translationsJS.add : 'Add'}</button>`;
                let formAddUser = `<div class="form-add-user">
                                        <div class="form-group">
                                            <label for="institution-name">${translationsJS && translationsJS.name ? translationsJS.name : 'Name'}</label>
                                            <input type="text" class="form-control" id="institution-name">
                                            </div>
                                            <div class="form-group">
                                            <label for="institution-location">${translationsJS && translationsJS.location ? translationsJS.location : 'Location'}</label>
                                            <input type="hidden" class="form-control" id="institution-location">
                                            <div id="searchmap-modal"></div>
                                            <div id="map-modal"></div>
                                        </div>
                                    </div>`;
                $(formAddUser).appendTo($modal.find('.modal-body'));
                $(buttonSubmit).appendTo($modal.find('.modal-footer'));
                $('#common-modal').modal('show');
            }
        });

        $('body').on('click', '.btn-add-user', function() {
            let $button = $(this);
            $button.data('keep-content', $(this).html());
            $button.html('<i class="fas fa-spinner fa-spin"></i>');
            let $modal = $('#common-modal');
            let name = $modal.find('input#institution-name').val();
            let address = $modal.find('input#institution-location').val();
            let $institutionDropdown = $('select[name="position[user]"]');

            if (!name) return false;

            let token = $('body').attr('data-token');
            let url = '/api/add_institution';
            let data = {};
            data.name = name ? name : '';
            data.address = address ? JSON.stringify(address) : '';

            if ($modal) {
                $.ajax({
                    url: url,
                    type: "POST",
                    dataType: 'json',
                    data: data,
                    headers: {"X-auth-token": token},
                    success: function (data, textStatus, jqXHR) {
                        $button.html($button.data('keep-content'));

                        if (data.result != undefined && data.result == true && data.institution != undefined) {
                            $modal.modal('hide');
                            $institutionDropdown.selectpicker('deselectAll');
                            $institutionDropdown.append(`<option selected="selected" value="${data.institution.id}">${data.institution.username}</option>`);
                            $institutionDropdown.selectpicker('refresh').val(data.institution.id);
                        } else {
                            bootbox.alert('An error occured');
                        }
                    },
                    error: function () {
                        $button.html($button.data('keep-content'));
                        bootbox.alert('An error occured');
                    },
                    complete: function () {
                        $modal.modal('hide');
                    }
                });

            }
        });

        $('#common-modal').on('shown.bs.modal', function (e) {
            if ($(this).find('.form-add-user').length > 0) {
                _this.runMapModal();
            }
        });
    }

    runMapPosition = () => {

        $('#recruiter #list-positions').on('shown.bs.collapse', function (e) {
            let blcMap = e.target.querySelector('.blc-map');
            if (blcMap) {
                let mapContent = blcMap.querySelector('.map');
                let positionAddress = blcMap.querySelector('.position_address');
                let positionAddressHidden = blcMap.querySelector('.position_address_hidden');

                if (mapContent.innerHTML != '') return false;

                let map = null;
                let coords = positionAddressHidden.value;
    
                if (!coords) return false;

                if (/^[\],:{}\s]*$/.test(coords.replace(/\\["\\\/bfnrtu]/g, '@').
                replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g, ']').
                replace(/(?:^|:|,)(?:\s*\[)+/g, ''))) {

                    coords = JSON.parse(coords);
                    map = L.map(mapContent).setView([coords.lat, coords.lng], 10);
                    
                    let geocoder = L.Control.Geocoder.nominatim();
                    
                    let control = L.Control.geocoder({
                        collapsed: false,
                        placeholder: translationsJS && translationsJS.search_here ? translationsJS.search_here : 'Search here...',
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

                        positionAddress.value = coords.city;
                    }
                } else {
                    blcMap.innerHTML = '<input type="text" class="form-control" disabled="disabled" name="name" value="' + coords + '">';
                }
            }

        })
   }

   runMapAddPosition = () => {

        let inputHidden = document.querySelector('form[name="position"] #position_location');
        let inputHiddenLat = document.querySelector('form[name="position"] #position_latitude');
        let inputHiddenLng = document.querySelector('form[name="position"] #position_longitude');
        var map = null;

        if (inputHidden) {
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
                placeholder: (window.location.href).indexOf('recruiter') != -1 ?  translationsJS && translationsJS.address ? translationsJS.address : 'Address' : translationsJS && translationsJS.city ? translationsJS.city : 'City',
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
                    inputHiddenLat.value = lat;
                    inputHiddenLng.value = lng;
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
            

            let $buttonSearch = $('button.leaflet-control-geocoder-icon');
            let $inputSearch = $('.leaflet-control-geocoder.leaflet-bar input');
            let timeout = false;
            $inputSearch.on('keyup', function() {
                if (timeout) clearTimeout(timeout);
                timeout = setTimeout(function() {
                    $buttonSearch.trigger('click');
                }, 1000);
            });

            if (coords) {
                let marker = L.marker([coords.lat, coords.lng]).addTo(map); // Markeur
                marker.bindPopup(coords.city); // Bulle d'info

                let leafletControlGeocoderForm = document.querySelector('.leaflet-control-geocoder-form input');
                leafletControlGeocoderForm.value = coords.city;
            }
        }
   }

    runCalculateAffectedUsers = () => {
        const $inputSkillsList = $('input#hidden_positionSkills');
        const $button = $('button#display-affected-users');
        const $resultContainer = $('p#affected-users');

        $('body').on('click', 'button#display-affected-users', function(e) {
            e.preventDefault();

            $button.hide();
            $resultContainer.removeClass('hidden').find('span:last').html('<i class="fas fa-spinner fa-spin"></i>');

            let skillsList = JSON.parse($inputSkillsList.val()) || {};

            let token = $('body').attr('data-token');
            let url = '/api/add_institution';
            let data = {skills: skillsList};

            $.ajax({
                type: "GET",
                dataType: 'json',
                data: data,
                headers: {"X-auth-token": token},
                url: '/api/search_affected_users',
                success: function (data, textStatus, jqXHR) {
                    $resultContainer
                        .find('span:not([data-toggle=tooltip]):last')
                        .html(`
                            ${translationsJS && translationsJS.number_total_of_affected_users ? translationsJS.number_total_of_affected_users : 'Number total of affected users:'} <strong class="ml-1 mr-1">${data.data.count_all}</strong>
                            <span data-toggle="tooltip" title="${translationsJS.number_total_of_affected_users_tuto}" style="cursor: help;">
                                <i class="fas fa-info-circle"></i>
                            </span>
                            <br>
                            ${translationsJS && translationsJS.number_of_interested_users ? translationsJS.number_of_interested_users : 'Number of interested users:'} <strong class="ml-1 mr-1">${data.data.count_listening}</strong>
                            <span data-toggle="tooltip" title="${translationsJS.number_of_interested_users_tuto}" style="cursor: help;">
                                <i class="fas fa-info-circle"></i>
                            </span>
                        `);

                    $resultContainer.find('[data-toggle="tooltip"]').tooltip();
                },
                error : function(jqXHR, textStatus, errorThrown){
                    $resultContainer.addClass('hidden');
                    bootbox.alert('An error occured');
                },
                complete : function(jqXHR, textStatus ){}
            });
        });
    }

    sendEmailPosition = () => {
        let _this = this;

        $('body').on('click', '#send-email-position', function() {
            let token = $('body').attr('data-token');
            function formatDate(date, format) {
                const map = {
                    mm: date.getMonth() + 1,
                    dd: date.getDate(),
                    yyyy: date.getFullYear()
                }
                
                return format.replace(/mm|dd|yyyy/gi, matched => map[matched])
            }
            
            let positionId = $('#position_id').val().length > 0 ? $('#position_id').val() : ''
            if (!positionId) return false;
            let url = '/api/send_position_to_affected_users/' + positionId;


            $.ajax({
                type: "GET",
                dataType: 'json',
                headers: {"X-auth-token": token},
                url: url,
                success: function (data, textStatus, jqXHR) {
                    let tplMessage = `<div class="container message-flash">
                                        <div class=" mt-5 mb-5 alert alert-success alert-dismissible">
                                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                            ${translationsJS && translationsJS.an_email_has_been_sent ? translationsJS.an_email_has_been_sent : 'Email has been sent'}
                                        </div>
                                    </div>`
                    $(tplMessage).insertAfter('#main-header');

                    $('html, body').animate({scrollTop:0},200);
                    _this.displayMessage();

                    $('#no-send-email-position-info').hide();
                    $('#send-email-position-info').show().find('span').text(formatDate(new Date(), 'yyyy/mm/dd'));
                },
                error : function(jqXHR, textStatus, errorThrown){
                    bootbox.alert('An error occured');
                },
                complete : function(jqXHR, textStatus ){}
            });
        });
    }
    

    displayMessage = () => {
        if ($('.message-flash').children().length > 0) {
            setTimeout(function() {
                $('.message-flash').children().remove();
            }, 2000);
        }
    }

    init = function() {
        this.runAutocompletion();
        this.duplicatePosition();
        this.addSkillsToPosition();
        this.addSkillOccupation();
        this.getSkillsFromOccupation();
        this.removeSkillsToPosition();
        this.displayMessage();
        this.runMap();
        this.runMapPosition();
        this.runMapAddPosition();
        this.runModalAddUser();
        this.runCalculateAffectedUsers();
        this.sendEmailPosition();

        $('#common-modal').on('hidden.bs.modal', function (e) {
            $(this).find('.modal-title').children().remove();
            $(this).find('.modal-body').children().remove();
            $(this).find('.modal-footer').find('.btn-add-user').remove();
            $(this).find('.modal-dialog').removeClass('modal-lg');
        });
        
        $('#common-modal-2').on('hidden.bs.modal', function (e) {
            $(this).find('.modal-title').children().remove();
            $(this).find('.modal-body').children().remove();
            $(this).find('.modal-footer').find('.btn-add-user').remove();
            $(this).find('.modal-dialog').removeClass('modal-lg');
        });

        $('[data-toggle="tooltip"]').tooltip();

        // TABS
        let hash = location.hash.replace(/^#/, ''); 
        if (hash) {
            $('#recruiter [data-toggle="tab"][href="#' + hash + '"]').tab('show');
        }
        $('#recruiter [data-toggle="tab"]').on('shown.bs.tab', function (e) {
            window.location.hash = e.target.hash;
        });
    }
}

$(document).ready(function() {
    var recruiter = new Recruiter();
    recruiter.init();
});
