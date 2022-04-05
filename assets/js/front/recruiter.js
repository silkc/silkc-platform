//import $ from 'jquery';
import $ from 'jquery';
import autocomplete from 'autocompleter';

//import '../../css/bootstrap-extended.css';

// any CSS you import will output into a single css file (app.css in this case)
import '../../scss/elements/header.scss';
import '../../scss/recruiter.scss';


import 'datatables.net';
import 'datatables.net-select-dt';
import 'datatables.net-dt/css/jquery.dataTables.min.css';
import 'datatables.net-select-dt/css/select.dataTables.min.css';

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
                    <span class="badge" data-toggle="tooltip" title="" data-original-title="${skill.description}"><i class="fas fa-info-circle"></i></span>
                </div>
                <div>
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input ${associated ? 'associated' : ''} ${rmv ? 'rmv-skill' : 'add-skill'}" 
                                            id="skill_id-${id}" data-id="${id}" 
                                            data-name="${skill.preferredLabel}" 
                                            value="1" 
                                            ${associated ? 'checked="checked"' : ''}>
                        <label class="switch-custom custom-control-label" for="skill_id-${id}"></label>
                    </div>
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
            let html = _this.tplSkill(data, true, true);
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

        $('body').on('click', '.ul-skills:not(#skills-occupations) .rmv-skill', function(e) {
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
                                    if (ulNotOccupation.find('.rmv-skill[data-id="' + data[i].skill.id + '"]').length > 0) {
                                        ulNotOccupation.find('.rmv-skill[data-id="' + data[i].skill.id + '"]').closest('.list-group-item').remove();
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
                                    if (ul.find('.add-skill').length > 0) {
                                        $('body').find('.skills-associated').attr('id', 'all-associated').html(translationsJS && translationsJS.all_associated ? translationsJS.all_associated : 'All associated')
                                    } else {
                                        $('body').find('.skills-associated').attr('id', 'all-unassociated').html(translationsJS && translationsJS.all_unassociated ? translationsJS.all_unassociated : 'All unassociated')
                                    }
                                }
                            }
                        }
                    });

                    $('[data-toggle="tooltip"]').tooltip();
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
                    $(this).removeClass('no-linked');
                    let skill = $(this).find('input[type="checkbox"].add-skill');
                    skill.prop('checked', true);
                    let skillId = skill.attr('data-id');
                    
                    skill.addClass('associated');
                    skill.toggleClass('add-skill rmv-skill');
                    
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
                    $(this).addClass('no-linked');
                    let skill = $(this).find('input[type="checkbox"].rmv-skill');
                    skill.prop('checked', false);
                    let skillId = skill.attr('data-id');
            
                    skill.removeClass('associated');
                    skill.toggleClass('rmv-skill add-skill');
                    
                    _this.removeSkillToHiddenField(skillId);
                });

                $(this).attr('id', 'all-associated').html(translationsJS && translationsJS.all_associated ? translationsJS.all_associated : 'All associated')
            }

            _this.resetAffectedUsers();
        });

        $('body').on('click', '#skills-occupations .add-skill', function(e) {

            $(this).closest('li').removeClass('no-linked');

            let skillId = $(this).attr('data-id');
            
            $(this).addClass('associated');
            $(this).toggleClass('add-skill rmv-skill');
            
            _this.addSkillToHiddenField(skillId);
            _this.resetAffectedUsers();
        });
        
        $('body').on('click', '#skills-occupations .rmv-skill', function(e) {
            $(this).closest('li').addClass('no-linked');
            
            let skillId = $(this).attr('data-id');
            
            $(this).removeClass('associated');

            $(this).toggleClass('rmv-skill add-skill');

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
                    let suggestions = data.filter((n) =>
                        n.preferredLabel != undefined
                            ? n.preferredLabel.toLowerCase().includes(text) || n.altLabels.toLowerCase().includes(text)
                            : n.name != undefined
                            ? n.name.toLowerCase().includes(text)
                            : ""
                    );
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
                let suggestions = data.filter((n) =>
                n.preferredLabel != undefined
                    ? n.preferredLabel.toLowerCase().includes(search) || n.altLabels.toLowerCase().includes(search)
                    : n.name.toLowerCase().includes(search)
                );
                if (!search || search.length == 0) {
                    input.value = '';
                    if (hiddenField) {
                        hiddenField.value = '';
                        elemsDisabled.prop('disabled', true);
                    }
                }
                if (!search || search.length == 0 || suggestions.length == 0) {
                    if (hiddenField) {
                        hiddenField.value = "";
                    }
                }
            });

            /* Si on sort du champs de recherche sans avoir sélectionner un item, on sélectionne la première proposition
            Si il n'y a pas de propositions, on vide le champs */
            input.addEventListener('focusout', function() {
                let search = this.value.toLowerCase();
                let suggestions = data.filter(n => (n.preferredLabel != undefined) 
                    ? n.preferredLabel.toLowerCase().startsWith(search) || n.altLabels.toLowerCase().includes(search)
                    : n.name.toLowerCase().startsWith(search));

                if (hiddenField.value == "") {
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

    runCalculateAffectedUsers = () => {
        const $inputSkillsList = $('input#hidden_positionSkills');
        const $button = $('button#display-affected-users');
        const $resultContainer = $('p#affected-users');

        $('body').on('click', '#show-sent-history', function(e) {

            let skillsList = JSON.parse($inputSkillsList.val()) || {};
            let positionId = $('#position_id').val().length > 0 ? $('#position_id').val() : ''
            if (!positionId) return false;
            let data = {skills: skillsList};
            let url = '/api/get_logs_send_mails/' + positionId;
            let token = $('body').attr('data-token');

            $.ajax({
                type: "GET",
                dataType: 'json',
                data: data,
                headers: {"X-auth-token": token},
                url: url,
                success: function (data, textStatus, jqXHR) {
                    if (data && data.result) {
                        let $modal = $('#common-modal');
                        if ($modal) {
                            $modal.find('.modal-dialog').addClass('modal-lg');
                            $modal.find('.modal-title').html(translationsJS && translationsJS.title_show_sent_history ? translationsJS.title_show_sent_history : 'Email send history');
                            let contentHTML = '';
                            if (data.dates && data.dates.length > 0) {
                                contentHTML += '<table id="status-details-email" class="display" style="width:100%"><thead><tr>'
                                contentHTML += '<th>' + translationsJS.date + '</th>'
                                contentHTML += '<th>' + translationsJS.count_users + '</th>'
                                contentHTML += '<th>' + translationsJS.count_errors + '</th>'
                                contentHTML += '<th></th>'
                                contentHTML += '</tr></thead>'
                                contentHTML += '<tbody>' 
                                for(let i = 0; i < data.dates.length; i++) {

                                    let errors = data.dates[i].errors ? data.dates[i].errors.join('<br />') : '';

                                    contentHTML += '<tr>'
                                    contentHTML += '<td>' + data.dates[i].date + '</td>'
                                    contentHTML += '<td>' + data.dates[i].countUsers + '</td>'
                                    if(data.dates[i].countErrors > 0) {
                                        contentHTML += '<td><div class="d-flex justify-content-between"><span>' + data.dates[i].countErrors + '</span><span class="detail-error btn-link" style="cursor: pointer;"><i class="fas fa-search-plus"></i></span></div></td>'
                                    } else {
                                        contentHTML += '<td><div class="d-flex justify-content-between"><span>' + data.dates[i].countErrors + '</span></div></td>'
                                    }
                                    contentHTML += '<td>' + errors + '</td>'
                                    contentHTML += '</tr>'
                                }
                                contentHTML += '</tbody></table>'
                            } else {
                                contentHTML += '<p>' + translationsJS.not_user + '</p>';
                            }
                            
                            $(contentHTML).appendTo($modal.find('.modal-body'));

                            let table = $('#status-details-email').DataTable({
                                searching: false, 
                                info: false,
                                lengthChange: false,
                                order: [[ 1, 'asc' ]],
                                "columns": [
                                    { "data": "date" },
                                    { "data": "countUsers" },
                                    { "data": "countErrors" },
                                    { "data": "errors", "visible": false }
                                ],
                                language: {
                                    search: translationsJS && translationsJS.datatable_search ? translationsJS.datatable_search : 'Search:',
                                    paginate: {
                                        first: translationsJS && translationsJS.datatable_first ? translationsJS.datatable_first : 'First:',
                                        previous: translationsJS && translationsJS.datatable_previous ? translationsJS.datatable_previous : 'Previous:',
                                        next: translationsJS && translationsJS.datatable_next ? translationsJS.datatable_next : 'Next:',
                                        last: translationsJS && translationsJS.datatable_last ? translationsJS.datatable_last : 'Last:'
                                    }
                                }
                            });

                            function format (d) {
                                return '<table cellpadding="5" cellspacing="0" border="0" style="padding-left:50px;">'+
                                    '<tr>'+
                                        '<td>' + d && d.errors ? d.errors : '' + '</td>'+
                                    '</tr>'+
                                '</table>';
                            }

                            $('#status-details-email tbody').on('click', '.detail-error', function () {
                                var tr = $(this).closest('tr');
                                var row = table.row( tr );
                                if ( row.child.isShown() ) {
                                    row.child.hide();
                                    tr.removeClass('shown');
                                }
                                else {
                                    row.child( format(row.data()) ).show();
                                    tr.addClass('shown');
                                }
                            } );

                            $('#common-modal').modal('show');
                        }
                    }
                },
                error : function(jqXHR, textStatus, errorThrown){
                    $resultContainer.addClass('hidden');
                    bootbox.alert('An error occured');
                },
                complete : function(jqXHR, textStatus ){}
            });
        });
        $('body').on('click', '#view-list-user', function(e) {
            let token = $('body').attr('data-token');
            let skillsList = JSON.parse($inputSkillsList.val()) || {};
            let url = '/api/search_affected_users';
            let data = {skills: skillsList};

            $.ajax({
                type: "GET",
                dataType: 'json',
                data: data,
                headers: {"X-auth-token": token},
                url: '/api/search_affected_users',
                success: function (data, textStatus, jqXHR) {
                    if (data && data.result) {
                        let $modal = $('#common-modal');

                        if ($modal) {
                            $modal.find('.modal-dialog').addClass('modal-lg');
                            $modal.find('.modal-title').html(translationsJS && translationsJS.user_details ? translationsJS.user_details : 'User details');
                            let contentHTML = '';
                            if (data.affected_users && data.affected_users.length > 0) {
                                contentHTML += '<table id="user-details-email" class="display" style="width:100%"><thead><tr>'
                                contentHTML += '<th>' + translationsJS.username + '</th>'
                                contentHTML += '<th>' + translationsJS.firstname + '</th>'
                                contentHTML += '<th>' + translationsJS.lastname + '</th>'
                                contentHTML += '<th>' + translationsJS.e_mail + '</th>'
                                contentHTML += '</tr></thead>'
                                contentHTML += '<tbody>'
                                for(let i = 0; i < data.affected_users.length; i++) {
                                    contentHTML += '<tr>'
                                    contentHTML += '<td>' + data.affected_users[i].username + '</td>'
                                    contentHTML += '<td>' + data.affected_users[i].firstname + '</td>'
                                    contentHTML += '<td>' + data.affected_users[i].lastname + '</td>'
                                    contentHTML += '<td>' + data.affected_users[i].email + '</td>'
                                    contentHTML += '</tr>'
                                }
                                contentHTML += '</tbody></table>'
                            } else {
                                contentHTML += '<p>' + translationsJS.not_user + '</p>';
                            }
                            
                            $(contentHTML).appendTo($modal.find('.modal-body'));

                            $('#user-details-email').DataTable({
                                searching: false, 
                                info: false,
                                lengthChange: false,
                                order: [[ 1, 'asc' ]],
                                language: {
                                    search: translationsJS && translationsJS.datatable_search ? translationsJS.datatable_search : 'Search:',
                                    paginate: {
                                        first: translationsJS && translationsJS.datatable_first ? translationsJS.datatable_first : 'First:',
                                        previous: translationsJS && translationsJS.datatable_previous ? translationsJS.datatable_previous : 'Previous:',
                                        next: translationsJS && translationsJS.datatable_next ? translationsJS.datatable_next : 'Next:',
                                        last: translationsJS && translationsJS.datatable_last ? translationsJS.datatable_last : 'Last:'
                                    }
                                }
                            });

                            $('#common-modal').modal('show');
                        }
                    }
                },
                error : function(jqXHR, textStatus, errorThrown){
                    $resultContainer.addClass('hidden');
                    bootbox.alert('An error occured');
                },
                complete : function(jqXHR, textStatus ){}
            });
        });

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
                            ${data.data.count_listening > 0 ? 
                                `<span data-toggle="tooltip" title="${translationsJS.user_details}" style="cursor: help;" id="view-list-user">
                                <i class="fas fa-eye"></i>
                            </span>` : ''}
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
        const $inputSkillsList = $('input#hidden_positionSkills');

        $('body').on('click', '#send-email-position', function() {
            let $button = $(this);

            $button.attr('disabled', 'disabled').find('span.loader').remove();
            $button.append('<span class="loader">&nbsp;<i class="fas fa-spinner fa-spin"></i></span>');

            let token = $('body').attr('data-token');
            function formatDate(date, format) {
                const map = {
                    mm: date.getMonth() + 1,
                    dd: date.getDate(),
                    yyyy: date.getFullYear()
                }
                
                return format.replace(/mm|dd|yyyy/gi, matched => map[matched])
            }

            let skillsList = JSON.parse($inputSkillsList.val()) || {};
            let positionId = $('#position_id').val().length > 0 ? $('#position_id').val() : ''
            if (!positionId) return false;
            let data = {skills: skillsList};
            let url = '/api/send_position_to_affected_users/' + positionId;

            $.ajax({
                type: "GET",
                dataType: 'json',
                data: data,
                headers: {"X-auth-token": token},
                url: url,
                success: function (data, textStatus, jqXHR) {
                    $button.find('span.loader').remove();

                    if (data && data.result) {
                        let $modal = $('#common-modal');

                        let sendSuccess = translationsJS && translationsJS.sendSuccess ? translationsJS.sendSuccess : 'email sent successfully';
                        let sendError = translationsJS && translationsJS.sendError ? translationsJS.sendError : 'error';
                        let sendsSuccess = translationsJS && translationsJS.sendsSuccess ? translationsJS.sendsSuccess : 'emails sent successfully';
                        let sendsError = translationsJS && translationsJS.sendsError ? translationsJS.sendsError : 'error';

                        let msgSuccess = data.countUsers > 1 ? sendsSuccess : sendSuccess;
                        let msgError = data.countUsers > 1 ? sendsSuccess : sendError;

                        if ($modal) {
                            $modal.find('.modal-title').html(translationsJS && translationsJS.summary ? translationsJS.summary : 'Summary');
                            let contentHTML = `<div class="send-mail-modal">
                            <p style="font-size: 1.1rem;" class="text-success"><span>${data.countUsers}</span> <span>${msgSuccess}</span></p>
                            <p style="font-size: 1.1rem;" class="text-danger"><span>${data.countErrors}</span> <span>${msgError}</span></p>
                            </div>`;
                            $(contentHTML).appendTo($modal.find('.modal-body'));
                            $('#common-modal').modal('show');
                        }
                    }


                    $('#no-send-email-position-info').hide();
                    $('#send-email-position-info').show().find('span').text(formatDate(new Date(), 'yyyy/mm/dd'));
                },
                error : function(jqXHR, textStatus, errorThrown){
                    bootbox.alert('An error occured');
                },
                complete : function(jqXHR, textStatus ){
                    $button.prop('disabled', false).find('span.loader').remove();
                }
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

    viewEmailTemplate = () => {

        let _this = this;
        
        $('body').on('click', '#view-email-template', function() {
            
            const $inputSkillsList = $('input#hidden_positionSkills');

            let $button = $(this);
    
            $button.attr('disabled', 'disabled').find('span.loader').remove();
            $button.append('<span class="loader">&nbsp;<i class="fas fa-spinner fa-spin"></i></span>');

            let token = $('body').attr('data-token');
            let skillsList = JSON.parse($inputSkillsList.val()) || {};
            let positionId = $('#position_id').val().length > 0 ? $('#position_id').val() : ''
            if (!positionId) return false;
            let data = {skills: skillsList};
            let url = '/api/template_send_position_to_affected_users/' + positionId;

            $.ajax({
                type: "GET",
                dataType: 'json',
                data: data,
                headers: {"X-auth-token": token},
                url: url,
                success: function (data, textStatus, jqXHR) {
                    $button.find('span.loader').remove();

                    if (data && data.result) {
                        let $modal = $('#common-modal');

                        if ($modal) {
                            $modal.find('.modal-title').html(translationsJS && translationsJS.tpl_email ? translationsJS.tpl_email : 'Email template');
                            $modal.find('.modal-dialog').addClass('modal-lg');
                            let contentHTML = data.html;
                            $(contentHTML).appendTo($modal.find('.modal-body'));
                            $('#common-modal').modal('show');
                        }
                    }

                },
                error : function(jqXHR, textStatus, errorThrown){
                    bootbox.alert('An error occured');
                },
                complete : function(jqXHR, textStatus ){
                    $button.prop('disabled', false).find('span.loader').remove();
                }
            });
        });
    }

    init = function() {
        this.runAutocompletion();
        this.duplicatePosition();
        this.addSkillsToPosition();
        this.addSkillOccupation();
        this.getSkillsFromOccupation();
        this.removeSkillsToPosition();
        this.displayMessage();
        this.runCalculateAffectedUsers();
        this.sendEmailPosition();
        this.viewEmailTemplate();

        $('#common-modal').on('hidden.bs.modal', function (e) {
            $(this).find('.modal-title').children().remove();
            $(this).find('.modal-body').children().remove();
            $(this).find('.modal-footer').find('.btn-add-user').remove();
            $(this).find('.modal-dialog').removeClass('modal-lg');
            $('#send-email-position').removeAttr('disabled');
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
            $('html, body').animate({scrollTop:0}, 0);
        });
    }
}

$(document).ready(function() {
    var recruiter = new Recruiter();
    recruiter.init();
});
