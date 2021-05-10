//import $ from 'jquery';
import $ from 'jquery';
import doT from 'dot';

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

            let data = {id: skillId, preferredLabel: skillName};
            let html = _this.tplSkill(data, type, true);
            $(html).appendTo(ul);

            skillNameInput.val('');
            skillIdInput.val('');
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

    init = function() {
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
