//import $ from 'jquery';
import $ from 'jquery';
import autocomplete from 'autocompleter';

//import '../../css/bootstrap-extended.css';

// any CSS you import will output into a single css file (app.css in this case)
import '../../scss/elements/header.scss';
import '../../scss/institutional.scss';


require('bootstrap');
//require('popper');
var moment = require('moment');
require('chart.js');
require('@fortawesome/fontawesome-free/js/all.min');
const bootbox = require('bootbox/bootbox');

require('bootstrap-select');
import 'bootstrap-select/dist/css/bootstrap-select.min.css'

class Institutional {
    instanceProperty = "Institutional";
    boundFunction = () => {
        return this.instanceProperty;
    }

    tplSkill = (skill, type, rmv = false, associated = false) => {

        let lang = $('body').attr('lang');
        let id = skill.id;
        if (skill.translations) {
            let skills = skill.translations;
            skills.forEach(sk => {
                if (lang == sk.locale) skill = sk;
            });
        }

        return `<li class="list-group-item ${!associated ? 'no-linked' : ''}">
            <div class="d-flex flex-nowrap justify-content-between">
                <div>
                    <span>${skill.preferredLabel}</span>
                </div>
                <div>
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input ${associated ? 'associated' : ''} ${rmv ? 'rmv-skill' : 'add-skill'}" 
                                            id="skill_id-${id}" data-id="${id}" 
                                            data-name="${skill.preferredLabel}" 
                                            data-type="${type}" value="1" 
                                            ${associated ? 'checked="checked"' : ''}>
                        <label class="switch-custom custom-control-label" for="skill_id-${id}"></label>
                    </div>
                </div>
            </div>
        </li>`;
    }

    /**
     * Duplicate a training
     */
    duplicateTraining = () => {
        $('body').on('click', '#list-trainings .clone', function(e) {
            e.preventDefault();

        });
    }

    /**
     * Ajout de compétences à un training
     */
     addSkillsToTraining = () => {

        let _this = this; 

        $('body').on('click', '.add-skill-gp button', function() {

            let status = true;
            let type = $(this).closest('.add-skill-gp').attr('data-type');
            let skillNameInput = $(this).closest('.add-skill-gp').find('.input-autocomplete');
            let skillIdInput = skillNameInput.siblings('input[type="hidden"]');
            let skillName = skillNameInput.val();
            let skillId = skillIdInput.val();
            let ul = type == "required" ? $('#skills-required') : $('#skills-not-occupations-acquired');
            
            if (!skillName) return false;

            status = _this.addSkillToHiddenField(type, skillId);
            if (status === false) {
                skillNameInput.val('');
                skillIdInput.val('');
                return false;
            }

            let ulOccupationsAcquired = $('#skills-occupations-acquired');
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
            let html = _this.tplSkill(data, type, true, true);
            $(html).appendTo(ul);
        });
    }


     addSkillToHiddenField = (type, skillId) => {

        let skillsList = {};
        let inputSkillsList = $('body').find('#hidden_trainingSkills');
        skillsList = inputSkillsList.val();
        skillId = parseInt(skillId);

        if (skillsList) {
            skillsList = JSON.parse(inputSkillsList.val());
            if (type == 'required') {
                if ('required' in skillsList) {
                    if (type == 'required') {
                        if (skillsList.required.includes(skillId)) return false;
                        skillsList.required = [skillId, ...skillsList.required];
                    }
                } else {
                    if (type == 'required') {
                        if (skillsList.required.includes(skillId)) return false;
                        skillsList.required = [skillId];
                    }
                }
            }
            if (type == 'acquired') {
                if ('acquired' in skillsList) {
                    if (type == 'acquired') {
                        if (skillsList.acquired.includes(skillId)) return false;
                        skillsList.acquired = [skillId, ...skillsList.acquired];
                    }
                } else {
                    if (type == 'acquired') {
                        if (skillsList.acquired.includes(skillId)) return false;
                        skillsList.acquired = [skillId];
                    }
                }
            }
        } else {
            skillsList = {};
            if (type == 'required') {
                if (skillsList.required.includes(skillId)) return false;
                skillsList.required = [skillId];
            }
            if (type == 'acquired') {
                if (skillsList.acquired.includes(skillId)) return false;
                skillsList.acquired = [skillId];
            }
        }
        
        inputSkillsList.val(JSON.stringify(skillsList))
    }


    /**
     * Suppression de compétences à un training
     */
     removeSkillToHiddenField = (type, skillId) => {

        let _this = this;
        let inputSkillsList = $('#hidden_trainingSkills');
        skillId = parseInt(skillId);

        if (type && skillId && inputSkillsList) {
            let skillsList = inputSkillsList.val();
            if (skillsList) {
                skillsList = JSON.parse(skillsList);
                if (type in skillsList) {
                    if (skillsList[type].includes(skillId)) {
                        skillsList[type] = skillsList[type].filter(function (el) {
                            return el != skillId;
                        });
                        inputSkillsList.val(JSON.stringify(skillsList));
                    }
                }
            }
        }
    }

    removeSkillsToTraining = () => {

        let _this = this;

        $('body').on('click', '.ul-skills:not(#skills-occupations-acquired) .rmv-skill', function(e) {
            e.preventDefault();
            
            let type = $(this).attr('data-type');
            let skillId = $(this).attr('data-id');
            let inputSkillsList = $('#hidden_trainingSkills');

            _this.removeSkillToHiddenField(type, skillId);
            $(this).closest('.list-group-item').remove();
        });
    }


    getSkillsFromOccupation = () => {

        let _this = this;
        let lang = $('body').attr('lang');

        $('body').on('change', '.occupations-select select', function () {
            let select = $(this);
            let occupation_id = $(this).val();
            let description = select.find('option:selected').attr('data-description') || '';
            let baseUrl = '/apip/occupation_skills';
            let params = $.param({'occupation': occupation_id});
            let url = `${baseUrl}?${params}`;
            let ul = $('#skills-occupations-acquired');                             
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
            let inputSkillsList = $('body').find('#hidden_trainingSkills');
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
                                // Si il y a des comptences liées (dans la partie competences non liées au metier)
                                // on les deplace dans la partie liée au metier
                                if ('acquired' in skillsList
                                    && skillsList.acquired.includes(data[i].skill.id)) {
                                    if (ulNotOccupation.find('.rmv-skill[data-id="' + data[i].skill.id + '"]').length > 0) {
                                        ulNotOccupation.find('.rmv-skill[data-id="' + data[i].skill.id + '"]').closest('.list-group-item').remove();
                                    }
                                    html += _this.tplSkill(data[i].skill, "acquired", true, true);
                                } else {
                                    html += _this.tplSkill(data[i].skill, "acquired");
                                }
                                if (i == data.length - 1) {
                                    $(html).appendTo(ul);

                                    // Affichage "tout associer/tout desassocier"
                                    if (ul.find('li').length > 0) {
                                        $('body').find('.skills-associated').css('display', 'inline-block');
                                    } else {
                                        $('body').find('.skills-associated').hide();
                                    }
                                    if (ul.find('.add-skill').length > 0) {
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

            let ul = $('#skills-occupations-acquired');
            if (ul.find('li').length > 0) {
                ul.find('li').each(function(k) {
                    $(this).removeClass('no-linked');
                    let skill = $(this).find('input[type="checkbox"].add-skill');
                    skill.prop('checked', true);
                    let skillId = skill.attr('data-id');
                    let type = skill.attr('data-type');
                    
                    skill.addClass('associated');
                    skill.toggleClass('add-skill rmv-skill');
                    
                    _this.addSkillToHiddenField(type, skillId);
                });

                $(this).attr('id', 'all-unassociated').html(translationsJS && translationsJS.all_unassociated ? translationsJS.all_unassociated : 'All unassociated')
            }
        });

        $('body').on('click', '#all-unassociated', function(e) {
            e.preventDefault();

            let ul = $('#skills-occupations-acquired');
            if (ul.find('li').length > 0) {
                ul.find('li').each(function(k) {
                    $(this).addClass('no-linked');
                    let skill = $(this).find('input[type="checkbox"].rmv-skill');
                    skill.prop('checked', false);
                    let skillId = skill.attr('data-id');
                    let type = skill.attr('data-type');
            
                    skill.removeClass('associated');
                    skill.toggleClass('rmv-skill add-skill');
                    
                    _this.removeSkillToHiddenField(type, skillId);
                });

                $(this).attr('id', 'all-associated').html(translationsJS && translationsJS.all_associated ? translationsJS.all_associated : 'All associated')
            }
        });

        $('body').on('click', '#skills-occupations-acquired .add-skill', function(e) {

            $(this).closest('li').removeClass('no-linked');

            let skillId = $(this).attr('data-id');
            let type = $(this).attr('data-type');
            
            $(this).addClass('associated');
            $(this).toggleClass('add-skill rmv-skill');
            
            _this.addSkillToHiddenField(type, skillId);
        });
        
        $('body').on('click', '#skills-occupations-acquired .rmv-skill', function(e) {
            
            $(this).closest('li').addClass('no-linked');
            
            let skillId = $(this).attr('data-id');
            let type = $(this).attr('data-type');
            
            $(this).removeClass('associated');

            $(this).toggleClass('rmv-skill add-skill');

            _this.removeSkillToHiddenField(type, skillId);
        });
    }

    
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
                    div.textContent = (item.preferredLabel != undefined) ? item.preferredLabel : (item.name != undefined) ? item.name : ''; // preferredLabel => table ESCO, name => table training
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
                let suggestions = data.filter(n => (n.preferredLabel != undefined) ? n.preferredLabel.toLowerCase().includes(search) : n.name.toLowerCase().includes(search));
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
                            || (url.includes("trainings") && 'trainings' in datas)
                        )) {
                        
                        let data = {};
                        if (url.includes("skills"))
                            data = JSON.parse(datas.skills);
                        if (url.includes("occupations"))
                            data = JSON.parse(datas.occupations);
                        if (url.includes("trainings"))
                            data = JSON.parse(datas.trainings);

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
                                if (url.includes("trainings"))
                                    datas.trainings = JSON.stringify(data);

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

        let inputHidden = document.getElementById('institution_address');
        var map = null;
        let inputHiddenLat = document.querySelector('form input[type="hidden"].user_lat');
        let inputHiddenLng = document.querySelector('form input[type="hidden"].user_lng');

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

                    inputHiddenLat.value = lat;
                    inputHiddenLng.value = lng;
                    
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

                                            <div class="grp-google-map">
                                                <div class="input-group flex-nowrap mb-2">
                                                    <input class="form-control" type="text" placeholer="address" id="address-google-map-modal-training">
                                                    <span class="input-group-append">
                                                        <button type="button" class="btn btn-primary" id="btn-geocode-modal-training" disabled="disabled">
                                                            <div class="spinner-border spinner-border-sm inactive" role="status">
                                                                <span class="sr-only">Loading...</span>
                                                            </div>
                                                            <i class="fa fa-search active"></i>
                                                        </button>
                                                    </span>
                                                </div>
                                                <div id="map-modal-training" style="height: 250px;"></div>
                                            </div>
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
            let $institutionDropdown = $('select[name="training[user]"]');

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

    runMapTraining = () => { 

        $('#institutional #list-trainings').on('shown.bs.collapse', function (e) {
            let blcMap = e.target.querySelector('.blc-map');
            if (blcMap) {
                let mapContent = blcMap.querySelector('.map');
                let trainingAddress = blcMap.querySelector('.training_address');
                let trainingAddressHidden = blcMap.querySelector('.training_address_hidden');

                if (mapContent.innerHTML != '') return false;

                let map = null;
                let coords = trainingAddressHidden.value;
    
                if (!coords) return false;

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
                    let marker = new google.maps.Marker({
                        position: result.geometry.location,
                        map,
                        title: result.business_status ? `${result.name} - ${result.formatted_address}` : result.formatted_address,
                    });
                    map.setCenter(result.geometry.location);
                    map.setZoom(10);
                }
            }

        })
   }

   runMapAddTraining = () => { 
        let copyLocationBtn = document.getElementById('copy-location');

        // Copy cliboard de la location
        if (copyLocationBtn) {
            copyLocationBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                if (copyLocationBtn.disabled == true)
                    return false;

                const elem = this;
                const locationValue = this.getAttribute('data-location');

                navigator.clipboard.writeText(locationValue).then(function() {
                    bootbox.alert("Copying location to clipboard was successful: <br><br>" + locationValue);
                }, function(err) {
                    bootbox.alert('An error occured');
                });
            });
        }
   }
    
    /**
     * Affichage carte modal
     */
     runMapModal = () => { 
        var map;
        var service;
        var tabResults = [];
        var marker = null;

        let $modal = $('#modal-address');
        let $modalBoby = $modal.find('.modal-body');

        let inputAddress = document.getElementById('address-google-map-modal-training');
        let inputHiddenAddress = document.querySelector('#institution-location');

        $modal.removeAttr('id').attr('id', 'modal-address-modal');

        // Creation d'un marker
        let createMarker = function (result) {
            marker = new google.maps.Marker({
                position: result.geometry.location,
                map,
                title: result.business_status ? `${result.name} - ${result.formatted_address}` : result.formatted_address,
            });
            map.setCenter(result.geometry.location);
            map.setZoom(10);
        };

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
        };
        $('body').on('input', '#address-google-map-modal-training', function() {
            if($(this).val().length > 0)
                $('#btn-geocode-modal-training').prop('disabled', false);
            else
                $('#btn-geocode-modal-training').prop('disabled', true);
        });

        
        $('body').on('keydown', '#address-google-map-modal-training', function(e) {
            if(e.key === 'Enter') {
                e.preventDefault();
                $(this).closest('.input-group').find('button').trigger('click');      
            }
        });

        // Au clic sur rechercher
        $('body').on('click', '#btn-geocode-modal-training', function() {

            $('#btn-geocode-modal-training').prop('disabled', true);
            $('#btn-geocode-modal-training').find('.spinner-border').toggleClass('inactive active');
            $('#btn-geocode-modal-training').find('.fa-search').toggleClass('inactive active');

            var address = document.getElementById('address-google-map-modal-training').value;
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
                    let zeroResultsHTML = `<p>No results</p>`;
                    $modalBoby.html(zeroResultsHTML);
                    $modal.modal('show');
                }

                $(this).find('.spinner-border').toggleClass('inactive active');
                $(this).find('.fa-search').toggleClass('inactive active');
                setTimeout(function() {
                    $('#btn-geocode-modal-training').prop('disabled', false);
                }, 200);
            })
        });

        // Selection d'une adresse via la modal
        $('body').on('click', '#modal-address-modal li', function() {
            if (marker) marker.setMap(null); // Suppression marker
            var id = $(this).attr('data-id');
            $modal.modal('hide');
            createMarker(tabResults[id]);
            updateFields(tabResults[id]);
        });
        
        $('#modal-address-modal').on('hidden.bs.modal', function (e) {
            $modalBoby.children().remove();
        })

        function initialize() {
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
            map = new google.maps.Map(document.getElementById('map-modal-training'), mapOptions);
        }
        initialize();    
    }

    init = function() {
        this.runAutocompletion();
        this.duplicateTraining();
        this.addSkillsToTraining();
        this.addSkillOccupation();
        this.getSkillsFromOccupation();
        this.removeSkillsToTraining();
        this.displayMessage();
        //this.runMap();
        this.runMapTraining();
        this.runMapAddTraining();
        this.runModalAddUser();

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
            $('#institutional [data-toggle="tab"][href="#' + hash + '"]').tab('show');
        }
        $('#institutional [data-toggle="tab"]').on('shown.bs.tab', function (e) {
            window.location.hash = e.target.hash;
        });
    }
}

$(document).ready(function() {
    var institutional = new Institutional();
    institutional.init();
});
