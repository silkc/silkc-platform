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

    /**
     * Add new training
     */
    dotjs = () => {    
        doT.templateSettings = {
            evaluate:    /\[\[([\s\S]+?)\]\]/g,
            interpolate: /\[\[=([\s\S]+?)\]\]/g,
            encode:      /\[\[!([\s\S]+?)\]\]/g,
            use:         /\[\[#([\s\S]+?)\]\]/g,
            define:      /\[\[##\s*([\w\.$]+)\s*(\:|=)([\s\S]+?)#\]\]/g,
            conditional: /\[\[\?(\?)?\s*([\s\S]*?)\s*\]\]/g,
            iterate:     /\[\[~\s*(?:\]\]|([\s\S]+?)\s*\:\s*([\w$]+)\s*(?:\:\s*([\w$]+))?\s*\]\])/g,
            varname: 'it',
            strip: true,
            append: true,
            selfcontained: false
        };
    }
    
    /**
     * Add new training
     */
    addTraining = () => {    
        $('body').on('click', '#add-training', function(e) {
            e.preventDefault();

            let tpl = document.getElementById('tpl_training');
            if (!tpl) return false;
            let tempFn = doT.template(tpl.textContent);
            let id = $('body').find('#list-trainings').children().length;
            let name = $('#training-input').val();
            let json = {k : id, name : name};
            $('body').find('#list-trainings .collapse').collapse('hide');
            let html = tempFn( json );
            $(html).prependTo('#list-trainings');
            $('#training-input').val('');

            // Trigger pour reinitialiser la recherche autocomplete dans le fichier main.js
            const event = new Event('newTraining');
            document.dispatchEvent(event);
        });
    }

    /**
     * Duplicate a training
     */
    duplicateTraining = () => {
        $('body').on('click', '#list-trainings .clone', function(e) {
            e.preventDefault();

            let _this = this;
            $(_this).hide().siblings('.loader').css('display', 'inline-block');
            let id = $(_this).attr('data-id');
            if (!id) return false;

            let baseUrl = '/duplicate_training';
            let params = $.param({'training_id': id});

            let url = `${baseUrl}?${params}`;
            if (url) {
                $.ajax({
                    type: "GET",
                    url: url,
                    success: function (data, textStatus, jqXHR) {
                        if (!data || data == undefined) return false;
                        data = JSON.parse(data);
                        let json = data.shift();
                        let tpl = document.getElementById('tpl_training');
                        if (!tpl) return false;
                        let tempFn = doT.template(tpl.textContent);
                        json.k = $('body').find('#list-trainings').children().length;
                        $('body').find('#list-trainings .collapse').collapse('hide');
                        let html = tempFn( json );
                        $(html).prependTo('#list-trainings');

                        // Trigger pour reinitialiser la recherche autocomplete dans le fichier main.js
                        const event = new Event('newTraining');
                        document.dispatchEvent(event);
                    },
                    error : function(jqXHR, textStatus, errorThrown){},
                    complete : function(jqXHR, textStatus ){
                        $(_this).css('display', 'inline-block').siblings('.loader').hide();
                    }
                });
            } else {
                $(_this).css('display', 'inline-block').siblings('.loader').hide();
            }
        });
    }

    /**
     * Ajout de compétences à un training
     */
     addSkillsToTraining = () => {

        let _this = this; 

        $('body').on('click', '.add-skill button', function() {

            let type = $(this).closest('.add-skill');
            let skillName = $(this).closest('.add-skill').find('.input-autocomplete');
            let inputSkillToAdd = skillName.siblings('input[type="hidden"]');
            let inputSkillsList = $(this).closest('form[name="training"]').find('.hidden_trainingSkills');

            _this.addSkills(type, skillName, inputSkillToAdd, inputSkillsList);
        });
    }


     addSkills = (type, skillName, inputSkillToAdd = false, inputSkillsList, skillIdToAdd = false) => {

        let skillToAdd = {};
        let skillsList = {};

        if ((inputSkillToAdd && inputSkillsList) || (skillIdToAdd && inputSkillsList)) {
            skillToAdd = (inputSkillToAdd) ? inputSkillToAdd.val() : skillIdToAdd;
            skillsList = inputSkillsList.val();

            if (skillsList) {
                skillsList = JSON.parse(inputSkillsList.val());
                if (type.hasClass('required')) {
                    if ('required' in skillsList) {
                        if (type.hasClass('required'))
                            skillsList.required = [skillToAdd, ...skillsList.required];
                    } else {
                        if (type.hasClass('required'))
                            skillsList.required = [skillToAdd];
                    }
                }
                if (type.hasClass('acquired')) {
                    if ('acquired' in skillsList) {
                        if (type.hasClass('acquired'))
                            skillsList.acquired = [skillToAdd, ...skillsList.acquired];
                    } else {
                        if (type.hasClass('acquired'))
                            skillsList.acquired = [skillToAdd];
                    }
                }
            } else {
                skillsList = {};
                if (type.hasClass('required'))
                    skillsList.required = [skillToAdd];
                if (type.hasClass('acquired'))
                    skillsList.acquired = [skillToAdd];
            }
            
            inputSkillsList.val(JSON.stringify(skillsList))

            let html = `<div class="skill">
                            <div class="d-flex flex-nowrap justify-content-between">
                                <div class="d-flex flex-nowrap align-items-center">
                                    <span>${typeof skillName == "string" ? skillName : skillName.val()}</span>
                                </div>
                                <div>
                                    <a href="" class="text-danger rmv" data-id="${skillToAdd}" data-type="${type.hasClass('required') ? 'required' : 'acquired'}" title="Remove this skill">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </div>
                            </div>
                        </div>`;

            $(html).appendTo(type.find('.content-list-skill'));
            if (typeof skillName != "string") skillName.val('');
            if (inputSkillToAdd) inputSkillToAdd.val('');
        }
    }

    /**
     * Suppression de compétences à un training
     */
     removeSkillsToTraining = () => {

        $('body').on('click', 'form .content-list-skill .rmv', function(e) {
            e.preventDefault();

            let _this = this;
            let type = $(_this).attr('data-type');
            let id = $(_this).attr('data-id');
            let inputSkillsList = $(this).closest('form[name="training"]').find('.hidden_trainingSkills');

            if (type && id && inputSkillsList) {
                let skillsList = inputSkillsList.val();
                if (skillsList) {
                    skillsList = JSON.parse(skillsList);
                    if (type in skillsList) {
                        if (skillsList[type].includes(id)) {
                            skillsList[type] = skillsList[type].filter(function (el) {
                                return el != id;
                            });
                            inputSkillsList.val(JSON.stringify(skillsList));
                            $(_this).closest('div.skill').remove();
                        }
                    }
                }
            }
        });
    }

    selectSkillsByOccupation = () => {

        let _this = this;

        $('body').on('change', '.occupations-select select', function () {
            let occupation_id = $(this).val();

            let baseUrl = '/apip/occupation_skills';
            let params = $.param({'occupation': occupation_id});

            let url = `${baseUrl}?${params}`;

            if (occupation_id && url) {
                $.ajax({
                    type: "GET",
                    url: url,
                    success: function (data, textStatus, jqXHR) {
                        let modal = $('#modal_skills');
                        let html = `<div class="form-check check-all">
                                        <input class="form-check-input" type="checkbox" id="check-all">
                                        <label class="form-check-label" for="flexCheckDefault">
                                            Check All
                                        </label>
                                    </div>`;
                        if (data && data.length > 0) {
                            for (let i = 0; i < data.length; i++) {
                                html += `<div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="${data[i].id}" data-name="${data[i].skill.preferredLabel}">
                                                <label class="form-check-label" for="flexCheckDefault">
                                                    ${data[i].skill.preferredLabel}
                                                </label>
                                            </div>`


                                if (i == data.length - 1) {
                                    $(html).appendTo(modal.find('.modal-body'))
                                    modal.modal('show');
                                }
                            }
                        }
                    },
                    error : function(jqXHR, textStatus, errorThrown){},
                    complete : function(jqXHR, textStatus ){
                        //$(_this).css('display', 'inline-block').siblings('.loader').hide();
                    }
                });
            } else {
                //$(_this).css('display', 'inline-block').siblings('.loader').hide();
            }
        });

        $('body').on('change', '#modal_skills #check-all', function () {
            $('#modal_skills .form-check-input').prop('checked', $(this).prop('checked'));
        });

        $('#modal_skills').on('hidden.bs.modal', function (e) {
            $(this).find('.modal-body').children().remove();
            $('body').find('.occupations-select select').val($('.occupations-select select option:first').val());
        });

        $('body').on('click', '#modal_skills .add', function () {
            let modal = $('#modal_skills');
            modal.find('.form-check-input:not(#check-all)').each(function() {
                if (this.checked) {
                    let id = $(this).val();
                    let name = $(this).attr('data-name');
                    let type = $('.add-skill.acquired');
                    let skillName = $(this).attr('data-name');
                    let inputSkillsList = $('body').find('form[name="training"]').find('.hidden_trainingSkills');
                    _this.addSkills(type, skillName, false, inputSkillsList, id);
                }
            });
            modal.modal('hide');
        });
    }

    init = function() {
        this.dotjs();
        this.addTraining();
        this.duplicateTraining();
        this.addSkillsToTraining();
        this.removeSkillsToTraining();
        this.selectSkillsByOccupation();
    }
}

$(document).ready(function() {
    var institutional = new Institutional();
    institutional.init();
});
