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

class Institutional {
    instanceProperty = "Institutional";
    boundFunction = () => {
        return this.instanceProperty;
    }

    tplSkill = (skill, type, rmv = false, associated = false) => {

        return `<li class="list-group-item">
            <div class="d-flex flex-nowrap justify-content-between">
                <div>
                    <span>${skill.preferredLabel}</span>
                </div>
                <div>
                    <a href="#" class="${associated ? 'associated' : ''} ${rmv ? 'rmv' : 'add'}" data-id="${skill.id}" data-name="${skill.preferredLabel}" data-type="${type}">
                        <i class="fas ${rmv ? 'fa-minus' : 'fa-plus'}"></i>
                    </a>
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

        $('body').on('click', '.add-skill button', function() {

            let status = true;
            let type = $(this).closest('.add-skill').attr('data-type');
            let skillNameInput = $(this).closest('.add-skill').find('.input-autocomplete');
            let skillIdInput = skillNameInput.siblings('input[type="hidden"]');
            let skillName = skillNameInput.val();
            let skillId = skillIdInput.val();
            let ul = type == "required" ? $('#skills-required') : $('#skills-not-occupations-acquired');
            
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
            let html = _this.tplSkill(data, type, true);
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

        $('body').on('click', '.ul-skills:not(#skills-occupations-acquired) .rmv', function(e) {
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

        $('body').on('change', '.occupations-select select', function () {
            let occupation_id = $(this).val();
            let baseUrl = '/apip/occupation_skills';
            let params = $.param({'occupation': occupation_id});
            let url = `${baseUrl}?${params}`;
            let ul = $('#skills-occupations-acquired');
            let ulNotOccupation = $('#skills-not-occupations-acquired');

            if (!occupation_id || !url) {
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
                return false;
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
                                    if (ulNotOccupation.find('.rmv[data-id="' + data[i].skill.id + '"]').length > 0) {
                                        ulNotOccupation.find('.rmv[data-id="' + data[i].skill.id + '"]').closest('.list-group-item').remove();
                                    }
                                    html += _this.tplSkill(data[i].skill, "acquired", true, true);
                                } else {
                                    html += _this.tplSkill(data[i].skill, "acquired");
                                }
                                if (i == data.length - 1) {
                                    $(html).appendTo(ul);
                                }
                            }
                        }
                    });
                },
                error : function(jqXHR, textStatus, errorThrown){},
                complete : function(jqXHR, textStatus ){}
            });
        });

    }

    addSkillOccupation = () => {

        let _this = this;

        $('body').on('click', '#skills-occupations-acquired .add', function(e) {
            e.preventDefault();

            let skillId = $(this).attr('data-id');
            let type = $(this).attr('data-type');
            
            $(this).addClass('associated');
            
            $(this).toggleClass('add rmv');
            $(this).children().remove();
            $(this).append('<i class="fas fa-minus"></i>');
            
            _this.addSkillToHiddenField(type, skillId);
        });
        
        $('body').on('click', '#skills-occupations-acquired .rmv', function(e) {
            e.preventDefault();
            
            let skillId = $(this).attr('data-id');
            let type = $(this).attr('data-type');
            
            $(this).removeClass('associated');

            $(this).toggleClass('rmv add');
            $(this).children().remove();
            $(this).append('<i class="fas fa-plus"></i>');

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
                    let div = document.createElement('div');
                    div.dataset.id = item.id;                    
                    div.textContent = (item.preferredLabel != undefined) ? item.preferredLabel : (item.name != undefined) ? item.name : ''; // preferredLabel => table ESCO, name => table training
                    return div;

                },
                fetch: function(text, callback) {
                    text = text.toLowerCase();
                    let suggestions = data.filter(n => (n.preferredLabel != undefined) ? n.preferredLabel.toLowerCase().startsWith(text) : (n.name != undefined) ? n.name.toLowerCase().startsWith(text) : '' );
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
                            async: false,
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
    
    init = function() {
        this.runAutocompletion();
        this.duplicateTraining();
        this.addSkillsToTraining();
        this.addSkillOccupation();
        this.getSkillsFromOccupation();
        this.removeSkillsToTraining();
    }
}

$(document).ready(function() {
    var institutional = new Institutional();
    institutional.init();
});
