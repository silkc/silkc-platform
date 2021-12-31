import $ from 'jquery';
import autocomplete from 'autocompleter';
//import '../../css/bootstrap-extended.css';

// any CSS you import will output into a single css file (app.css in this case)
import '../../scss/elements/header.scss';
import '../../scss/account.scss';

require('bootstrap');

require('bootstrap-star-rating');
require('bootstrap-star-rating/css/star-rating.css');
require('bootstrap-star-rating/themes/krajee-svg/theme.css');
//require('popper');
var moment = require('moment');
require('chart.js');
require('@fortawesome/fontawesome-free/js/all.min');

function renderDate(date, format = 'DD MMMM YYYY to HH:mm') {
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

    return oDate.locale('en').format(format);
}


Object.size = function(obj) {
    var size = 0,
      key;
    for (key in obj) {
      if (obj.hasOwnProperty(key)) size++;
    }
    return size;
  };

class Account { 
    instanceProperty = "Account";
    boundFunction = () => {
        return this.instanceProperty;
    }

    tplJob = (occupation) => {

        let lang = $('body').attr('lang');

        return `<li class="list-group-item d-flex justify-content-between align-items-center">
            <div>
                <a href="#" class="detail" 
                            data-name="${occupation.name}" 
                            data-description="${occupation.description}" 
                            data-id="${occupation.id}">
                    <span>${occupation.name}</span>
                    <i class="fas fa-question-circle text-primary"></i>
                </a>
            </div>
            <div class="d-inline-flex align-items-center justify-content-end">
                <a href="#" class="detail mr-2" data-toggle="tooltip" title="${translationsJS && translationsJS.number_of_associated_skills_click_for_more_details ? translationsJS.number_of_associated_skills_click_for_more_details : 'Number of associated skills - click for more details'}" 
                        data-name="${occupation.name}" 
                        data-description="${occupation.description}" 
                        data-id="${occupation.id}">
                    <span class="badge badge-success">${occupation.skills ? occupation.skills.length : 0}</span>
                </a>
                <a href="#" class="text-danger item rmv" data-toggle="tooltip" title="${translationsJS && translationsJS.remove_this_job ? translationsJS.remove_this_job : 'Remove this job'}" 
                        data-name="${occupation.name}" 
                        data-type="${occupation.type}" 
                        data-id="${occupation.id}">
                    <i class="fas fa-trash-alt text-danger"></i>
                </a>
                ${occupation.type == 'desiredOccupations' ? 
                    `<a href="/${lang ? lang : 'en'}/search_results/occupation/${occupation.id}" 
                        class="search-training ml-2" 
                        data-toggle="tooltip" 
                        title="${translationsJS && translationsJS.search_for_training_required_for_this_job ? translationsJS.search_for_training_required_for_this_job : 'Search for training required for this job'}">
                        <i class="fas fa-search-plus text-primary"></i>
                    </a>` : '' }
            </div>
        </li>`;
    }

    tplTraining = (training, user_id) => {
        return `<li class="list-group-item d-flex justify-content-between align-items-center">
            <div>
                <span>${training.name}</span>
            </div>
            <div class="d-inline-flex align-items-center justify-content-end">
                <div class="d-flex flex-row justify-content-start align-items-center mr-4">
                    <span class="rating" data-value="${training.avgMark}"></span>
                    <span
                            title="${translationsJS ? translationsJS.training_rating_tooltip_js + ' ' +  training.totalMark + ' ' + translationsJS.training_rating_voters_tooltip_js : ''}"
                            data-toggle="tooltip"
                            style="font-size: 0.8rem;"
                    >
                        (${training.totalMark})
                    </span>
                </div>

                <button class="btn btn-link p-0 see-detail mr-2" data-id="${training.id}" data-toggle="tooltip" title="${translationsJS && translationsJS.user_formation_details_tooltip ? translationsJS.user_formation_details_tooltip : 'Skills linked to this training'}">
                    <i class="fas fa-search text-primary"></i>
                </button>
                <button class="btn btn-link p-0 text-danger item rmv mr-2" data-toggle="tooltip" title="${translationsJS && translationsJS.user_formation_unlink_tooltip ? translationsJS.user_formation_unlink_tooltip : 'Remove this training'}" data-name="${training.name}" data-id="${training.id}">
                    <i class="fas fa-unlink text-danger"></i>
                </button>
                <button class="btn btn-link p-0 text-success feedback" data-toggle="tooltip" title="${translationsJS && translationsJS.user_formation_feedback_tooltip ? translationsJS.user_formation_feedback_tooltip : 'Provide feedback'}" data-name="${training.name}" data-id="${training.id}" data-user-id="${user_id}">
                    <i class="fas fa-plus text-primary"></i>
                </button>
            </div>
        </li>`;
    }

    tplSkill = (skill) => {

        return `<div class="card" data-key'="${skill.k}">
            <div class="card-header" id="heading_${skill.k}">
                <div class="d-flex flex-nowrap justify-content-between">
                    <div class="d-flex flex-nowrap align-items-center" data-toggle="collapse" data-target="#collapse_other_skills_${skill.k}" aria-expanded="false" aria-controls="collapse_other_skills_${skill.k}">
                        <a class="text-primary icon mr-2" data-toggle="collapse" href="#skills_other_${skill.k}">
                            <i class="fas fa-chevron-circle-right"></i>
                        </a>
                        <a data-toggle="collapse" href="#skills_other_${skill.k}">
                            <span>${skill.name}</span>
                        </a>
                    </div>
                    <div class="d-inline-flex align-items-center justify-content-end">
                        <a href="" class="text-danger rmv item" data-toggle="tooltip" title="${translationsJS && translationsJS.remove_this_skill ? translationsJS.remove_this_skill : 'Remove this skill'}" data-name="${skill.name}" data-id="${skill.id}">
                            <i class="fas fa-trash-alt"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div id="collapse_other_skills_${skill.k}" class="collapse" aria-labelledby="heading_${skill.k}" data-parent="#list-other_skills">
                <div class="card-body">
                    ${skill.description}
                </div>
            </div>
        </div>`;
    }

    tplMessageFlash = (status = true) => {

        if (status) {
            return `<div class="container message-flash">
                <div class=" mt-5 mb-5 alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    ${translationsJS && translationsJS.updated_data ? translationsJS.updated_data : 'Updated data'}
                </div>
            </div>`;
        } else {
            return `<div class="container message-flash">
                <div class=" mt-5 mb-5 alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    ${translationsJS && translationsJS.update_failed ? translationsJS.update_failed : 'Update failed'}
                </div>
            </div>`;
        }
    }

    runDetail = () => {
        $('body').on('click', '.list-job .detail', function(e) {
            e.preventDefault();

            let $this = $(this);

            let name = $this.attr('data-name');
            let id = $this.attr('data-id');
            let description = $this.attr('data-description');
            let $modal = $('#common-modal');
            
            if ($modal && id) {
                
                let url = '/api/skills_by_occupation/' + id;
                
                $.ajax({
                    type: "GET",
                    url: url,
                    success: function (data, textStatus, jqXHR) {
                        if (data) {
                            $modal.find('.modal-title').html(name);
                            $(`<p>${description}</p>`).appendTo($modal.find('.modal-body'));

                            if (data.skills && data.skills.length > 0) {
                                let dataSkills = data.skills;
                                let htmlEssential = '';
                                let htmlOptional = '';
                                for (var k = 0; k < dataSkills.length; k++) {
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
                                        $(`<h1 style="font-size: 1rem;">${translationsJS && translationsJS.essential_skills ? translationsJS.essential_skills : 'Essential skills'}</h1><ul>${htmlEssential}</ul>`).appendTo($modal.find('.modal-body'));
                                        $(`<h1 style="font-size: 1rem;">${translationsJS && translationsJS.optional_skills ? translationsJS.optional_skills : 'Optional skills'}</h1><ul>${htmlOptional}</ul>`).appendTo($modal.find('.modal-body'));
                                        $('#common-modal').find('.modal-dialog').addClass('modal-lg');
                                        $('#common-modal').modal('show');
                                        $('#common-modal [data-toggle="popover"]').popover();
                                    }
                                }
                            } else {
                                $('#common-modal').find('.modal-dialog').addClass('modal-lg');
                                $('#common-modal').modal('show');
                            }
                        }
                    },
                    error : function(resultat, statut, erreur){},
                    complete : function(resultat, statut, erreur){}
                });
            }
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
                    input.dataset.totalmark = (item.totalMark != undefined) ? item.totalMark : 0;
                    input.dataset.avgmark = (item.avgMark != undefined) ? item.avgMark : 0;
                    input.dataset.description = (item.description != undefined) ? item.description : '';
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
                    input.dataset.description = '';
                    if (hiddenField) {
                        hiddenField.value = '';
                        elemsDisabled.prop('disabled', true);
                    }
                }
            });

            /* Si on sort du champs de recherche sans avoir sélectionner un item, on sélectionne la première proposition
            Si il n'y a pas de propositions, on vide le champs */
            input.addEventListener('focusout', function() {
                if (
                    hiddenField && hiddenField.value != undefined &&
                    (
                        (typeof hiddenField.value == 'string' && hiddenField.value.length > 0) ||
                        (typeof hiddenField.value == 'number' && hiddenField.value > 0)
                    )
                )
                    return true;

                let search = this.value.toLowerCase();
                let suggestions = data.filter(n => (n.preferredLabel != undefined) ? n.preferredLabel.toLowerCase().includes(search) : n.name.toLowerCase().includes(search));
                if (suggestions && suggestions.length > 0 && search.length > 0) {
                    let suggestion = suggestions[0];
                    input.value = (suggestion.preferredLabel != undefined) ? suggestion.preferredLabel : (suggestion.name != undefined) ? suggestion.name : '';
                    input.dataset.description = (suggestion.description != undefined) ? suggestion.description : '';
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
                let params = $.param({'formats': formats, 'pagination': false});

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
                            //async: false,
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
     * Ajout d'un job
     */
    addJob = () => {

        let _this = this;

        $('body').on('click', '.add-job button', function(e) {
            e.preventDefault();

            let btn = this;
            $(btn).attr('disabled', 'disabled');
            let loader = `<div class="spinner-border text-light spinner-button mr-1" role="status">
            <span class="sr-only">Loading...</span>
            </div>`;
            $(btn).html(loader);
            
            let occupation = {};
            let inputJobs = $('body').find('#jobs[type="hidden"]');
            let div = $(this).closest('.add-job');
            let fieldset = div.closest('fieldset');
            let ulJobs = fieldset.find('.list-job .list-group');
            let inputJobToAdd = div.find('input[type="hidden"]');
            let inputAutocomplete = div.find('.input-autocomplete');
            let jobDescription = inputAutocomplete.attr('data-description');
            let name = inputAutocomplete.val();
            let jobsList = {};
            jobsList.currentOccupations = [];
            jobsList.previousOccupations = [];
            jobsList.desiredOccupations = [];

            inputAutocomplete.attr('disabled', 'disabled');

            if (inputJobToAdd && inputJobToAdd.val()) {
                let jobIdToAdd = inputJobToAdd.val();

                inputJobToAdd.val();
                
                if (!jobIdToAdd) return false;

                occupation.description = jobDescription;
                occupation.id = jobIdToAdd;
                occupation.name = name;

                if (inputJobs && inputJobs.val()) {
                    jobsList = JSON.parse(inputJobs.val());
                }
                
                // ajouter plusieurs current job
                /*if (div.hasClass('add-current-job')) {
                    if ('currentOccupations' in jobsList) {
                        if (Object.size(jobsList.currentOccupations) == 0) {
                            jobsList.currentOccupations = [jobIdToAdd];
                        } else { 
                            for (let k in jobsList.currentOccupations) {
                                if (jobsList.currentOccupations[k] == jobIdToAdd) return false;
                                if (k == jobsList.currentOccupations.length - 1) jobsList.currentOccupations = [jobIdToAdd, ...jobsList.currentOccupations];
                            }                        
                        }
                    } else {
                        jobsList.currentOccupations = [jobIdToAdd];
                    }
                }*/
                // ajouter un seul current job
                if (div.hasClass('add-current-job')) {
                    jobsList.currentOccupations = [jobIdToAdd];
                }
                
                if (div.hasClass('add-previous-job')) {
                    if ('previousOccupations' in jobsList) {
                        if (Object.size(jobsList.previousOccupations) == 0) {
                            jobsList.previousOccupations = [jobIdToAdd];
                        } else {
                            for (let k in jobsList.previousOccupations) {
                                if (jobsList.previousOccupations[k] == jobIdToAdd) return false;
                                if (k == jobsList.previousOccupations.length - 1) jobsList.previousOccupations = [jobIdToAdd, ...jobsList.previousOccupations];
                            } 
                        }
                    } else {
                        jobsList.previousOccupations = [jobIdToAdd];
                    }
                }
                
                if (div.hasClass('add-desired-job')) {
                    if ('desiredOccupations' in jobsList) {
                        if (Object.size(jobsList.desiredOccupations) == 0) {
                            jobsList.desiredOccupations = [jobIdToAdd];
                        } else {
                            for (let k in jobsList.desiredOccupations) {
                                if (jobsList.desiredOccupations[k] == jobIdToAdd) return false;
                                if (k == jobsList.desiredOccupations.length - 1) jobsList.desiredOccupations = [jobIdToAdd, ...jobsList.desiredOccupations];
                            } 
                        }
                    } else {
                        jobsList.desiredOccupations = [jobIdToAdd];
                    }
                }

                inputJobs.val(JSON.stringify(jobsList))

                if (ulJobs) {
                    let url = '/api/skills_by_occupation/' + jobIdToAdd;
                    $.ajax({
                        type: "GET",
                        url: url,
                        success: function (data, textStatus, jqXHR) {
                            if (data) {
                                let type = div.hasClass('add-desired-job') ? 'desiredOccupations' : div.hasClass('add-current-job') ? 'currentOccupations' : div.hasClass('add-previous-job') ? 'previousOccupations' : '';
                                occupation.type = div.hasClass('add-desired-job') ? 'desiredOccupations' : div.hasClass('add-current-job') ? 'currentOccupations' : div.hasClass('add-previous-job') ? 'previousOccupations' : '';
                                occupation.skills = data.skills != undefined ? data.skills : false;
                                let li = _this.tplJob(occupation);
                                
                                if (div.hasClass('add-current-job')) {
                                    $(ulJobs).html(li);
                                } else {
                                    $(ulJobs).append(li);
                                }
                            }

                            $('[data-toggle="tooltip"]').tooltip();
                        },
                        error : function(resultat, statut, erreur){},
                        complete : function(resultat, statut, erreur){
                            $(btn).html('<i class="fas fa-plus"></i>');
                            $(btn).removeAttr('disabled');
                            inputAutocomplete.removeAttr('disabled');
                            inputAutocomplete.val('');
                            inputAutocomplete.removeAttr('data-description');
                        }
                    });
                }
            }
        });
    }

    /**
     * Suppression d'un job
     */
    removeJob = () => {

        let _this = this;

        $('body').on('click', '.list-job .item.rmv', function(e) {
            e.preventDefault();
            $('[data-toggle="tooltip"]').tooltip('hide');

            let occupation = {};
            let inputJobs = $('body').find('#jobs[type="hidden"]');
            let id = $(this).attr('data-id');
            let type = $(this).attr('data-type');
            let li = $(this).closest('li');
            
            if (inputJobs && inputJobs.val()) {
                let jobsList = JSON.parse(inputJobs.val());
                if (id && type) {
                    if (type in jobsList) {
                        jobsList[type] = jobsList[type].filter(function (el) {
                            return el != id;
                        });
                        inputJobs.val(JSON.stringify(jobsList));
                    }
                }

                if (li) {
                    li.remove();
                }
            }
        });
    }

    /**
     * Ajout d'une formation
     */
    addTraining = () => {

        let _this = this;

        $('body').on('click', '#content-training .add-training button', function(e) {
            e.preventDefault();

            let user_id = $('#input_user_id').val();
            let training = {};
            let inputTrainings = $('body').find('#trainings[type="hidden"]');
            let ul = $('body').find('#content-training .list-trainings .list-group');
            let inputTrainingToAdd = $('body').find('#hidden_training[type="hidden"]');
            let inputAutocomplete = $('body').find('#training-input');
            let name = inputAutocomplete.val();
            let trainingsList = [];

            if (inputTrainingToAdd && inputTrainingToAdd.val()) {
                let trainingIdToAdd = inputTrainingToAdd.val();
                inputAutocomplete.val('');
                inputAutocomplete.removeAttr('data-description');
                inputTrainingToAdd.val();
                
                if (!trainingIdToAdd) return false;

                training.id = trainingIdToAdd;
                training.name = name;
                training.totalMark = parseInt(inputAutocomplete.attr('data-totalmark')) || 0;
                training.avgMark = parseInt(inputAutocomplete.attr('data-avgmark')) || 0;

                if (inputTrainings && inputTrainings.val()) {
                    trainingsList = JSON.parse(inputTrainings.val());
                }

                if (trainingsList.length > 0) {
                    for (let k in trainingsList) {
                        if (trainingsList[k] == trainingIdToAdd) return false;
                        if (k == trainingsList.length - 1) trainingsList = [trainingIdToAdd, ...trainingsList];
                    }
                } else {
                    trainingsList = [trainingIdToAdd, ...trainingsList];
                }
                
                inputTrainings.val(JSON.stringify(trainingsList))
                
                if (ul) {
                    let li = _this.tplTraining(training, user_id);
                    $(ul).append(li);

                    _this.runRater();

                    if (ul.find('li.list-group-item').length == 0)
                        $('.no_training_result').show();
                    else
                        $('.no_training_result').hide();

                    $('[data-toggle="tooltip"]').tooltip();
                }
            }
        });
    }

    /**
     * Suppression d'une formation
     */
    removeTraining = () => {

        let _this = this;

        $('body').on('click', '#content-training .list-trainings .item.rmv', function(e) {
            e.preventDefault();
            $('[data-toggle="tooltip"]').tooltip('hide');

            let inputTrainings = $('body').find('#trainings[type="hidden"]');
            let id = $(this).attr('data-id');
            let li = $(this).closest('li');
            let ul = $('body').find('#content-training .list-trainings .list-group');
            
            if (inputTrainings && inputTrainings.val()) {
                let trainingsList = JSON.parse(inputTrainings.val());
                
                if (id) {
                    trainingsList = trainingsList.filter(function (el) {
                        return el != id;
                    });
                    inputTrainings.val(JSON.stringify(trainingsList));
                }

                if (li) {
                    li.remove();
                    if (ul.find('li.list-group-item').length == 0)
                        $('.no_training_result').show();
                    else
                        $('.no_training_result').hide();
                }
            }
        });
    }

    /**
     * Sauvegarde des occupations
     */
     saveOccupations = () => {

        let _this = this;

        $('body').on('click', '#content-work button[type="submit"]', function(e) {
            e.preventDefault();

            let inputProfessionalExperience = $('body').find('input[name="professional_experience"]');
            let inputOccupation = $('body').find('#jobs[type="hidden"]');
            let token = $('body').attr('data-token');

            let loader = `<div class="spinner-border text-light spinner-button mr-1" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>`;
            $(loader).prependTo('#content-work button[type="submit"]');
            
            let occupations = JSON.parse(inputOccupation.val());
            let url = `/api/user_occupation`;

            $.each(occupations, function (k, occupation) {
                if (!occupation || Object.size(occupation) == 0) {
                    occupations[k] = [null];
                }
            });

            occupations['userProfessionalExperience'] = inputProfessionalExperience.val();

            $.ajax({
                url: url,
                type: "POST",
                dataType: 'json',
                data: occupations,
                headers: {"X-auth-token": token},
                success: function (data, textStatus, jqXHR) {
                    let html = _this.tplMessageFlash();
                    
                    $(html).prependTo('#account');
                    $('#content-work button[type="submit"]').find('.spinner-button').remove();
                    let check = `<i class="fas fa-check mr-1"></i>`;
                    $(check).prependTo('#content-work button[type="submit"]');
                    setTimeout(function() {
                        $('#content-work button[type="submit"]').find('svg').remove();
                    }, 1500);
                    
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                },
                error: function () {
                    let html = _this.tplMessageFlash(false);
                    $(html).prependTo('#account');
                    $('#content-work button[type="submit"]').find('.spinner-button').remove();
                },
                complete: function() {
                    $('html, body').animate({scrollTop:0},500);
                    _this.displayMessage();
                }
            });
        });
    }

    /**
     * Sauvegarde des formations
     */
     saveTrainings = () => {

        let _this = this;

        $('body').on('click', '#content-training button[type="submit"]', function(e) {
            e.preventDefault();

            let inputTraining = $('body').find('#trainings[type="hidden"]');
            let token = $('body').attr('data-token');
            let trainings = [null];

            if (inputTraining && inputTraining.val()) {
                trainings = JSON.parse(inputTraining.val());
                if (trainings && trainings.length == 0) {
                    trainings = [null];
                }
            }
    
            let loader = `<div class="spinner-border text-light spinner-button mr-1" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>`;
            $(loader).prependTo('#content-training button[type="submit"]');
            let url = `/api/user_training`;
                

            $.ajax({
                url: url,
                type: "POST",
                dataType: 'json',
                data: {trainings: trainings},
                headers: {"X-auth-token": token},
                success: function (data, textStatus, jqXHR) {
                    let html = _this.tplMessageFlash();
                    
                    $(html).prependTo('#account');
                    $('#content-training button[type="submit"]').find('.spinner-button').remove();
                    let check = `<i class="fas fa-check mr-1"></i>`;
                    $(check).prependTo('#content-training button[type="submit"]');
                    setTimeout(function() {
                        $('#content-training button[type="submit"]').find('svg').remove();
                    }, 1500);
                },
                error: function () {
                    let html = _this.tplMessageFlash(false);
                    $(html).prependTo('#account');
                    $('#content-training button[type="submit"]').find('.spinner-button').remove();
                },
                complete: function() {
                    $('html, body').animate({scrollTop:0},500);
                }
            });
        });
    }

    /**
     * Gestion des compétences
     */
     manageSkills = () => {

        let _this = this;

        // Add skills
        $('body').on('click', '.add-skill button', function(e) {
            e.preventDefault();

            let skill = {};
            //let inputSkills = $('body').find('#skills[type="hidden"]');
            let div = $(this).closest('.add-skill');
            let ul = $('#list-other_skills');
            let inputSkillToAdd = div.find('input[type="hidden"]');
            let inputAutocomplete = div.find('.input-autocomplete');
            let skillDescription = inputAutocomplete.attr('data-description');
            let name = inputAutocomplete.val();
            let status = true;

            if (inputSkillToAdd && inputSkillToAdd.val()) {
                let skillIdToAdd = inputSkillToAdd.val();

                inputAutocomplete.val('');
                inputAutocomplete.removeAttr('data-description');
                inputSkillToAdd.val();
                
                if (!skillIdToAdd) return false;

                let status = _this.addSkill(skillIdToAdd, 'associatedSkills');
                if (!status) return false;

                skill.description = skillDescription;
                skill.id = skillIdToAdd;
                skill.name = name;

                if (ul) {
                    skill.k = 0;
                    ul.find('.card').each(function() {
                        let k = $(this).attr('data-key');
                        if (parseInt(k) > skill.k) skill.k = parseInt(k) + 1;
                    });
                    let li = _this.tplSkill(skill);
                    $(ul).append(li);

                    $('[data-toggle="tooltip"]').tooltip();
                }
            }
        });

        // Remove skills
        $('body').on('click', '#content-skills .rmv.item', function(e) {
            e.preventDefault();
            $('[data-toggle="tooltip"]').tooltip('hide');

            let status = true;
            let skillId = $(this).attr('data-id');
            let skillName = $(this).attr('data-name');
            let card = $(this).closest('.card');
            let div = $(this).closest('.card').find('.card-header > div > div:last-child');
            let links = `<a href="#" data-toggle="tooltip" class="text-success back-skill item" title="${translationsJS && translationsJS.add_this_skill_back_into_the_list_above ? translationsJS.add_this_skill_back_into_the_list_above : 'Add this skill back into the list above'}" data-name="${skillName}" data-id="${skillId}">
                            <i class="fas fa-plus text-primary"></i>
                        </a>`;

            _this.removeSkill(skillId, 'associatedSkills');
            status = _this.addSkill(skillId, 'disassociatedSkills');

            div.children().remove();
            $(links).prependTo(div);
            let html = card.html();
            if (status)
                $('<div class="card">' + html + '</div>').prependTo($('#list-previously_unselected'));
            card.remove();

            $('[data-toggle="tooltip"]').tooltip();
        });

        // Back skills
        $('body').on('click', '#content-skills .back-skill.item', function(e) {
            e.preventDefault();
            $('[data-toggle="tooltip"]').tooltip('hide');

            let that = this;
            let skill = {};
            let $card = $(this).closest('.card');
            let ul = $('#list-other_skills');
            //let inputSkillToAdd = div.find('input[type="hidden"]');

            let id = $(this).attr('data-id');
            let name = $(this).attr('data-name');
            let description = $card.find('.card-body').text();

            skill.description = description;
            skill.id = id;
            skill.name = name;

            _this.addSkill(id, 'associatedSkills');
            _this.removeSkill(id, 'disassociatedSkills');

            if (ul) {
                skill.k = 0;
                ul.find('.card').each(function() {
                    let k = $(this).attr('data-key');
                    if (parseInt(k) > skill.k) skill.k = parseInt(k) + 1;
                });
                let li = _this.tplSkill(skill);
                $(ul).append(li);

                $(that).closest('.card').remove();

                $('[data-toggle="tooltip"]').tooltip();
            }
        });

        // Sauvegarde skills
        $('body').on('click', '#content-skills button[type="submit"]', function(e) {
            e.preventDefault();

            let inputSkills = $('body').find('#skills[type="hidden"]');
            let token = $('body').attr('data-token');
            let skills = {};
            skills.associatedSkills = [null];
            skills.disassociatedSkills = [null];

            if (inputSkills && inputSkills.val()) {
                let skillsJson = JSON.parse(inputSkills.val());
                if (skillsJson) {
                    for (let k in skillsJson) {
                        if ('associatedSkills' in skillsJson && skillsJson.associatedSkills.length > 0) {
                            skills.associatedSkills = skillsJson.associatedSkills;
                        }
                        if ('disassociatedSkills' in skillsJson && skillsJson.disassociatedSkills.length > 0) {
                            skills.disassociatedSkills = skillsJson.disassociatedSkills;
                        } 
                    }
                }
            }

            let loader = `<div class="spinner-border text-light spinner-button mr-1" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>`;
            $(loader).prependTo('#content-skills button[type="submit"]');
            
            let url = `/api/user_skill`;

            $.ajax({
                url: url,
                type: "POST",
                dataType: 'json',
                data: skills,
                headers: {"X-auth-token": token},
                success: function (data, textStatus, jqXHR) {
                    let html = _this.tplMessageFlash();
                    
                    $(html).prependTo('#account');
                    $('#content-skills button[type="submit"]').find('.spinner-button').remove();
                    let check = `<i class="fas fa-check mr-1"></i>`;
                    $(check).prependTo('#content-skills button[type="submit"]');
                    setTimeout(function() {
                        $('#content-skills button[type="submit"]').find('svg').remove();
                    }, 1500);
                },
                error: function () {
                    let html = _this.tplMessageFlash(false);
                    $(html).prependTo('#account');
                    $('#content-skills button[type="submit"]').find('.spinner-button').remove();
                },
                complete: function() {
                    $('html, body').animate({scrollTop:0},500);
                }
            });
        });
    }

    /**
     * Suppression skill ID du champs caché
     */
    removeSkill = (skillId, type) => {
        
        let inputSkills = $('body').find('#skills[type="hidden"]');
        let skillsList = '{"associatedSkills": [],"disassociatedSkills": [] }';
        
        if (!skillId || !type) return false;

        if (inputSkills && inputSkills.val())
            skillsList = JSON.parse(inputSkills.val());
            if (type in skillsList) {
                if (skillsList[type].includes(skillId)) {
                    skillsList[type] = skillsList[type].filter(function (el) {
                        return el != skillId;
                    });
                    inputSkills.val(JSON.stringify(skillsList));
                }
        }
    } 

    /**
     * Ajout skill ID dans un champs caché
     */
    addSkill = (skillId, type) => {
        
        let inputSkills = $('body').find('#skills[type="hidden"]');
        let skillsList = {};
        skillsList.associatedSkills = [];
        skillsList.disassociatedSkills = [];
        
        if (!skillId || !type) return false;
        
        if (inputSkills && inputSkills.val()) {
            skillsList = JSON.parse(inputSkills.val());
        }

        if (type in skillsList) {
            if (skillsList[type].length == 0) {
                skillsList[type] = [skillId];
            } else { 
                for (let k in skillsList[type]) {
                    if (skillsList[type][k] == skillId) return false;
                    if (k == skillsList[type].length - 1) skillsList[type] = [skillId, ...skillsList[type]];
                }                       
            }
        } else {
            skillsList[type] = [skillId];
        }

        inputSkills.val(JSON.stringify(skillsList))
        return true;
    } 

    /**
     * Voir/masquer les compétences
     */
    displaySkills = (skillId, type) => {

        // Show
        $('body').on('click', '#content-skills .see-more', function(e) {
            e.preventDefault();

            $('#list-following_skills').find('.card').show();
            $(this).html('<i class="far fa-minus-square"></i> See less');
            $(this).toggleClass('see-more see-less');
        });
        
        // Hide
        $('body').on('click', '#content-skills .see-less', function(e) {
            e.preventDefault();
            
            let cpt = 1;
            $('#list-following_skills').find('.card').each(function() {
                if (cpt > 10) {
                    $(this).hide();
                }
                cpt++;
            });

            $(this).toggleClass('see-less see-more');
            $(this).html('<i class="far fa-plus-square"></i> See more');
        });
    } 

    /**
     * Affichage occupations/trainings liées a une compétence
     */
    displayInfosSkill = () => {
        $('body').on('click', '#content-skills .card .more', function(e) {
            e.preventDefault();

            let name = $(this).attr('data-name');
            let trainings = $(this).attr('data-trainings');
            let occupations = $(this).attr('data-occupations');
            let $modal = $('#common-modal');
            let html = '';

            if (occupations) {
                let occupationsHtml = `<h2 class="title-skill">${translationsJS && translationsJS.occupations ? translationsJS.occupations : 'Occupations'}</h2>`;
                occupationsHtml += `<p>${occupations}</p>`;
                html += occupationsHtml;
            }

            if (trainings) {
                let trainingsHtml = `<h2 class="title-skill">${translationsJS && translationsJS.trainings ? translationsJS.trainings : 'Trainings'}</h2>`;
                trainingsHtml += `<p>${trainings}</p>`;
                html += trainingsHtml;
            }

            if ($modal && html) {
                $modal.find('.modal-title').html(name);
                $(html).appendTo($modal.find('.modal-body'));
                $('#common-modal').modal('show');
            }
        });

        $('#common-modal').on('hidden.bs.modal', function (e) {
            $(this).find('.modal-title').children().remove();
            $(this).find('.modal-body').children().remove();
        });
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

        let inputHidden = document.getElementById('user_address');
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

                    let leafletControlGeocoderForm = document.querySelector('#content-personal_informations .leaflet-control-geocoder-form input');
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
            
            document.getElementById('searchmap').appendChild(document.querySelector('#content-personal_informations .leaflet-control-geocoder.leaflet-bar'));

            if (coords) {
                let marker = L.marker([coords.lat, coords.lng]).addTo(map); // Markeur
                marker.bindPopup(coords.city); // Bulle d'info

                let leafletControlGeocoderForm = document.querySelector('#content-personal_informations .leaflet-control-geocoder-form input');
                leafletControlGeocoderForm.value = coords.city;
            }
        }
    }

    /**
     * Affichage carte modal
     */
     runMapModal = () => { 

        let inputHidden = document.getElementById('training_address_hidden');
        let locationModal = document.querySelector('#common-modal #location-modal');
        let mapContent = document.querySelector("#map-modal");

        if (!mapContent || mapContent.innerHTML != "") return false;

        var map = null;
        let coords = inputHidden.value;

        if (!coords) return false;

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
            trainingAddress.innerHTML = coords.title;
            map.setCenter({lat: coords.lat, lng: coords.lng});
            map.setZoom(10);
        } else {
            locationModal.innerHTML = coords ? coords : 'N/A';
            $('#map-modal').hide();
        }
    }

    /**
     * Voir le detail d'une formation
     */
     seeDetailTraining = () => { 
        let _this = this;
        $('body').on('click', '#content-training .see-detail', function(e) {
            e.preventDefault();
            let $modal = $('#common-modal');
            let id = $(this).attr('data-id');
            let url = '/apip/trainings/' + id;
            $.ajax({
                type: "GET",
                url: url,
                success: function (data, textStatus, jqXHR) {
                    if (data) {
                        let requireSkillsHTML = '';
                        let acquireSkillsHTML = '';

                        // Nom institution
                        let institution_name = 'N/A';
                        if (data.user != undefined && data.user != '') {
                            if (data.user.username != undefined && data.user.username != '')
                                institution_name = data.user.username;
                            else if (
                                data.user.firstname != undefined && data.user.firstname != '' &&
                                data.user.lastname != undefined && data.user.lastname != ''
                            )
                                institution_name = data.user.firstname + ' ' + data.user.lastname;
                        }

                        if (data.trainingSkills && data.trainingSkills.length > 0 ) {
                            for (let k in data.trainingSkills) {
                                let skill = data.trainingSkills[k].skill;
                                if (data.trainingSkills[k].isRequired) {
                                    requireSkillsHTML += `<li>
                                                            <span class="link-description" tabindex="${k}" data-toggle="popover" data-trigger="focus" title="" data-content="${skill.description ? skill.description : ''}" data-original-title="${skill.preferredLabel ? skill.preferredLabel : ''}">
                                                                ${skill.preferredLabel ? skill.preferredLabel : ''}
                                                            </span>
                                                        </li>`;
                                }
                                if (data.trainingSkills[k].isToAcquire) {
                                    acquireSkillsHTML += `<li>
                                                            <span class="link-description" tabindex="${k}" data-toggle="popover" data-trigger="focus" title="" data-content="${skill.description ? skill.description : ''}" data-original-title="${skill.preferredLabel ? skill.preferredLabel : ''}">
                                                                ${skill.preferredLabel ? skill.preferredLabel : ''}
                                                            </span>
                                                        </li>`;
                                }
                            }
                        }

                        let location = (data.location != undefined && data.location != null && data.location != '') ?
                            data.location.replace(/&/g, "&amp;")
                                .replace(/</g, "&lt;")
                                .replace(/>/g, "&gt;")
                                .replace(/"/g, "&quot;")
                                .replace(/'/g, "&#039;") :
                            'N/A';

                        let language = '-';
                        let localeTraining = data.language ? data.language : language;
                        if (localeTraining != '-') {
                            language = translationsJS && translationsJS['language_' + localeTraining] ? translationsJS['language_' + localeTraining] : '-'
                        }

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
											<span>${institution_name}</span>
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
											<span class="title">${translationsJS && translationsJS.training_info_url ? translationsJS.training_info_url : 'Training info URL'}</span>
										</div>
										<div class="col-lg-8">
                                        <span>${data.url ? data.url : 'N/A'}</span>
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
											<span class="title">${translationsJS && translationsJS.language ? translationsJS.language : 'Language'}</span>
										</div>
										<div class="col-lg-8">
											<p class="text-justify m-0">
                                                ${language ? language : 'N/A'}
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
											<span class="title">${translationsJS && translationsJS.start_at ? translationsJS.start_at : 'Start date'}</span>
										</div>
										<div class="col-lg-8">
											<span>${data.startAt ? renderDate(data.startAt, 'YYYY/MM/DD - HH:mm') : 'N/A'}</span>
										</div>
									</div>

									<div class="row mb-3">
										<div class="col-lg-4">
											<span class="title">${translationsJS && translationsJS.end_at ? translationsJS.end_at : 'End date'}</span>
										</div>
										<div class="col-lg-8">
											<span>${data.endAt ? renderDate(data.endAt, 'YYYY/MM/DD - HH:mm') : 'N/A'}</span>
										</div>
									</div>

									<div class="row mb-3">
										<div class="col-lg-4">
											<span class="title">${translationsJS && translationsJS.duration ? translationsJS.duration : 'Duration'}</span>
										</div>
										<div class="col-lg-8">
											<span>
                                                ${data.durationValue ? data.durationValue : 'N/A'}
                                                ${(data.durationUnity && translationsJS && translationsJS[data.durationUnity]) ? translationsJS[data.durationUnity].toUpperCase() : ''}
                                            </span>
										</div>
									</div>

									<div class="row mb-3">
										<div class="col-lg-4">
											<span class="title">${translationsJS && translationsJS.duration_details ? translationsJS.duration_details : 'Duration details'}</span>
										</div>
										<div class="col-lg-8">
											<span>${data.durationDetails ? data.durationDetails : 'N/A'}</span>
										</div>
									</div>

									<div class="row mb-3">
										<div class="col-lg-4">
											<span class="title">${translationsJS && translationsJS.online ? translationsJS.online : 'Online'}</span>
										</div>
										<div class="col-lg-8">
											<span>${data.isOnline ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times text-danger"></i>'}</span>
										</div>
									</div>

									<div class="row mb-3">
										<div class="col-lg-4">
											<span class="title">${translationsJS && translationsJS.is_online_monitored ? translationsJS.is_online_monitored : 'Tutored online'}</span>
										</div>
										<div class="col-lg-8">
											<span>${data.isOnlineMonitored ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times text-danger"></i>'}</span>
										</div>
									</div>

									<div class="row mb-3">
										<div class="col-lg-4">
											<span class="title">${translationsJS && translationsJS.is_presential ? translationsJS.is_presential : 'Face to face'}</span>
										</div>
										<div class="col-lg-8">
											<span>${data.isPresential ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times text-danger"></i>'}</span>
										</div>
									</div>

									<div class="row mb-3">
										<div class="col-lg-4">
											<span class="title">${translationsJS && translationsJS.occupation ? translationsJS.occupation : 'Occupation'}</span>
										</div>
										<div class="col-lg-8">
											<span>
                                                ${(data.occupation != undefined && data.occupation != null && data.occupation.preferredLabel) ? 
                                                    data.occupation.preferredLabel : 
                                                    'N/A'
                                                }
                                            </span>
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


                        $modal.find('.modal-title').html(data.name ? data.name : '');
                        $(modalBodyHTML).appendTo($modal.find('.modal-body'));
                        $modal.find('.modal-dialog').addClass('modal-lg');

                        $modal.find('[data-toggle="popover"]').popover();

                        $modal.modal('show');
                    }
                }
            });
        });

    }

    /**
     * Affichage feedback
     */
     displayFeedback = () => { 

        $('body').on('click', '#content-training .feedback', function(e) {
            e.preventDefault();

            let _this = this;
            $(_this).attr('disabled', true);
            $(_this).tooltip('hide');

            let $modal = $('#common-modal');
            let id = $(this).attr('data-id');
            let user_id = $(this).attr('data-user-id');
            let name = $(this).attr('data-name');
            let baseUrl = '/apip/training_feedbacks';
            let params = $.param({'training': id, 'formats': 'json'});
            let url = `${baseUrl}?${params}`;

            let formFeedback = `<div class="form-feedback">
                                    <input class="rating-training rating " data-max="5" data-min="0" name="rating" type="number" />
                                    <textarea class="form-control comment"></textarea>
                                </div>`;

            let buttonSubmit = `
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary btn-submit-feedback" data-training-id="${id}" data-user-id="${user_id}">${translationsJS && translationsJS.save ? translationsJS.save : 'Save'}</button>
            `;

            $.ajax({
                type: "GET",
                url: url,
                success: function (data, textStatus, jqXHR) {

                    let statusFeedback = false;

                    $modal.find('.modal-dialog').addClass('modal-lg');
                    $modal.find('.modal-title').html(name ? name : '');
                    let feedbacksHTML = ``;
                    if (data && data.length > 0) {

                        feedbacksHTML += `<ul class="ul-trainings-feedback">`;
                        for (let k = 0; k < data.length; k++)  {
                            if (data[k].user.id == user_id) {
                                statusFeedback = true;
                            }
                            let date = renderDate(data[k].createdAt, 'YYYY/MM/DD - HH:mm');
                            feedbacksHTML += `<li>ffff
                                                <p class="author"><span class="date">${date}</span> - ${data[k].user.username ? data[k].user.username : data[k].user.firstname ? data[k].user.firstname : ''}</p>
                                                <input class="rating-readonly-training rating " data-max="5" data-min="0" name="rating" type="number" value="${data[k].mark}"/>
                                                <p class="comment">${data[k].comment}</p>
                                            </li>`;

                            if (k == data.length - 1) {
                                feedbacksHTML += `</ul>`;
                                $(feedbacksHTML).appendTo($modal.find('.modal-body'));
                                if(!statusFeedback) {
                                    $(formFeedback).appendTo($modal.find('.modal-body'));
                                    $(buttonSubmit).appendTo($modal.find('.modal-footer'));
                                }

                                $('#common-modal').modal('show');
    
                                $('input.rating-readonly-training').rating({
                                    filledStar: '<i class="fas fa-star"></i>',
                                    emptyStar: '<i class="far fa-star"></i>',
                                    showCaption: false,
                                    size: 'md',
                                    step: 1,
                                    readonly: true
                                });
    
                                $('input.rating-training').rating({
                                    filledStar: '<i class="fas fa-star"></i>',
                                    emptyStar: '<i class="far fa-star"></i>',
                                    showCaption: false,
                                    step: 1,
                                    size: 'md'
                                });

                                $(_this).removeAttr('disabled');
                            }
                        }
                    } else {
                        $('<p class="not-ratings">No feedback on this training</p>').appendTo($modal.find('.modal-body'));
                        $modal.find('.modal-body').html($(formFeedback));
                        $modal.find('.modal-footer').html($(buttonSubmit));
                        $('#common-modal').modal('show');
                        $(_this).removeAttr('disabled');
                            
                        $('input.rating-training').rating({
                            filledStar: '<i class="fas fa-star"></i>',
                            emptyStar: '<i class="far fa-star"></i>',
                            showCaption: false,
                            step: 1,
                            size: 'md'
                        });
                    }
                },
                error : function(resultat, statut, erreur){
                    $(_this).removeAttr('disabled');
                },
                complete : function(resultat, statut, erreur){
                    $(_this).removeAttr('disabled');
                }
            });
        });

        $('body').on('click', '.btn-submit-feedback', function(){

            let _this = this;
            let loader = `<div class="spinner-border text-light spinner-button mr-1" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>`;
            $(loader).prependTo(this);

            let trainingId = $(this).attr('data-training-id');
            let userId = $(this).attr('data-user-id');
            let mark = $('.form-feedback').find('input.rating-training').val();
            let comment = $('.form-feedback').find('textarea').val();
            let token = $('body').attr('data-token');


            if (!userId || !trainingId) {
                $(_this).find('.spinner-button').remove();
                return false;
            }

            let url = '/apip/training_feedbacks';
            let feedback = {};
            feedback.user = '/apip/users/' + userId;
            feedback.training = '/apip/trainings/' + trainingId;
            feedback.mark = parseInt(mark ? mark : 0);
            feedback.comment = comment && comment.length > 0 ? comment : '-';

            $.ajax({
                url: url,
                type: "POST",
                data: JSON.stringify(feedback),
                dataType: "json",
                contentType: "application/json",
                headers: {"X-auth-token": token},
                success: function (data, textStatus, jqXHR) {
                    $(_this).find('.spinner-button').remove();
                    $('.form-feedback').find('input.rating-training').rating('clear');
                    $('.form-feedback').find('textarea').val('');

                    let feedbacksHTML = ``;

                    let date = renderDate(data.createdAt, 'YYYY/MM/DD - HH:mm');

                    if($('.ul-trainings-feedback').length == 0) {
                        $('#common-modal .not-ratings').remove();
                        feedbacksHTML += `<ul class="ul-trainings-feedback">
                                            <li>
                                                <p class="author"><span class="date">${date}</span> - ${data.user.username ? data.user.username : data.user.firstname ? data.user.firstname : ''}</p>
                                                <input class="rating-readonly-training rating " data-max="5" data-min="0" name="rating" type="number" value="${feedback.mark}"/>
                                                <p class="comment">${feedback.comment}</p>
                                            </li>
                                        </ul>`;

                        $(feedbacksHTML).prependTo($('#common-modal .modal-body'));

                    } else {
                        feedbacksHTML += `<li>
                                              <p class="author"><span class="date">${date}</span> - ${data.user.username ? data.user.username : data.user.firstname ? data.user.firstname : ''}</p>
                                              <input class="rating-readonly-training rating " data-max="5" data-min="0" name="rating" type="number" value="${feedback.mark}"/>
                                              <p class="comment">${feedback.comment}</p>
                                          </li>`;

                        $(feedbacksHTML).appendTo($('.ul-trainings-feedback'));
                    }

                    $('.form-feedback').remove();
                    $(_this).remove();
                        
                    $('input.rating-readonly-training').rating({
                        filledStar: '<i class="fas fa-star"></i>',
                        emptyStar: '<i class="far fa-star"></i>',
                        showCaption: false,
                        size: 'xs',
                        step: 1,
                        readonly: true
                    });

                    $('#common-modal').animate({ scrollTop: $('#common-modal .modal-dialog').height() }, "slow");

                    location.reload();
                },
                error : function(resultat, statut, erreur){
                    $(_this).find('.spinner-button').remove();
                },
                complete : function(resultat, statut, erreur){
                    $(_this).find('.spinner-button').remove();
                }
            });
        });
    }

    runRater = () => {
        $('[data-toggle="tooltip"]').tooltip();

        $('span.rating').each(function(index) {
            let $elem = $(this);
            let value = $elem.data('value');
            $elem.rating({
                filledStar: '<i class="fas fa-star"></i>',
                emptyStar: '<i class="far fa-star"></i>',
                showCaption: false,
                size: 'xs',
                step: 1,
                readonly: true,
                showClear: false,
            }).rating('update', value);
        });
    }

    init = function() {

        let _this = this;

        this.runDetail();
        this.runAutocompletion();
        this.addJob();
        this.removeJob();
        this.removeTraining();
        this.addTraining();
        this.saveOccupations();
        this.saveTrainings();
        this.manageSkills();
        this.displaySkills();
        this.displayInfosSkill();
        this.displayMessage();
        this.displayFeedback();
        //this.runMap();
        this.seeDetailTraining();
        this.runRater();


        $('#common-modal').on('shown.bs.modal', function (e) {
            if ($(this).find('#training_address_hidden').length > 0) {
                _this.runMapModal();
            }
        });

        $('#common-modal').on('hidden.bs.modal', function (e) {
            $(this).find('.modal-title').children().remove();
            $(this).find('.modal-body').children().remove();
            $(this).find('.modal-dialog').removeClass('modal-lg');
        });
        
        $('#common-modal-2').on('hidden.bs.modal', function (e) {
            $(this).find('.modal-title').children().remove();
            $(this).find('.modal-body').children().remove();
            $(this).find('.modal-dialog').removeClass('modal-lg');
        });

        $('[data-toggle="tooltip"]').tooltip();

        // TABS
        let hash = location.hash.replace(/^#/, ''); 
        if (hash) {
            $('#account [data-toggle="tab"][href="#' + hash + '"]').tab('show');
        }
        $('#account [data-toggle="tab"]').on('shown.bs.tab', function (e) {
            window.location.hash = e.target.hash;
        });
    }
}

$(document).ready(function() {
    let account = new Account();
    account.init();
});
