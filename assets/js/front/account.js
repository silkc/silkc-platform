import $ from 'jquery';
import autocomplete from 'autocompleter';
//import '../../css/bootstrap-extended.css';

// any CSS you import will output into a single css file (app.css in this case)
import '../../scss/elements/header.scss';
import '../../scss/account.scss';

require('bootstrap');
//require('popper');
var moment = require('moment');
require('chart.js');
require('@fortawesome/fontawesome-free/js/all.min');

class Account {
    instanceProperty = "Account";
    boundFunction = () => {
        return this.instanceProperty;
    }

    tplJob = (occupation) => {

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
                <a href="" class="jobs-linked mr-2">
                    <span class="badge badge-success">10</span>
                </a>
                <a href="" class="text-danger item rmv" title="Remove this job" 
                        data-name="${occupation.name}" 
                        data-type="${occupation.type}" 
                        data-id="${occupation.id}">
                    <i class="fas fa-trash-alt text-danger"></i>
                </a>
                ${occupation.type == 'desired' ? 
                    `<a href="#" class="search ml-2" title="Search for training required for this job">
                        <i class="fas fa-search-plus text-primary"></i>
                    </a>` : '' }
            </div>
        </li>`;
    }

    tplTraining = (training) => {

        return `<li class="list-group-item d-flex justify-content-between align-items-center">
            <div>
                <span>${training.name}</span>
            </div>
            <div class="d-inline-flex align-items-center justify-content-end">
                <a href="" class="link mr-2" title="Skills linked to this training">
                    <i class="fas fa-link text-primary"></i>
                </a>
                <a href="" class="text-danger item rmv mr-2" title="Remove this training" data-name="${training.name}" data-id="${training.id}" >
                    <i class="fas fa-trash-alt text-danger"></i>
                </a>
                <a href="" class="text-success feedback" title="Provide feedback">
                    <i class="fas fa-plus text-primary"></i>
                </a>
            </div>
        </li>`;
    }

    runDetail = () => {
        $('body').on('click', '.list-job .detail', function(e) {
            e.preventDefault();

            let $this = $(this);

            let name = $this.attr('data-name');
            let description = $this.attr('data-description');
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
                let search = this.value.toLowerCase();
                let suggestions = data.filter(n => (n.preferredLabel != undefined) ? n.preferredLabel.toLowerCase().startsWith(search) : n.name.toLowerCase().startsWith(search));
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
                let formats = input.getAttribute('data-formats') || 'json';
                let pagination = input.getAttribute('data-pagination') || false;
                let params = $.param({'formats': formats, 'pagination': true});

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

    /**
     * Ajout d'un job
     */
    addJob = () => {

        let _this = this;

        $('body').on('click', '.add-job button', function(e) {
            e.preventDefault();

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

            if (inputJobToAdd && inputJobToAdd.val()) {
                let jobIdToAdd = inputJobToAdd.val();

                inputAutocomplete.val('');
                inputAutocomplete.removeAttr('data-description');
                inputJobToAdd.val();
                
                if (!jobIdToAdd) return false;

                occupation.description = jobDescription;
                occupation.id = jobIdToAdd;
                occupation.name = name;

                if (inputJobs && inputJobs.val()) {
                    jobsList = JSON.parse(inputJobs.val());
                }
                
                if (div.hasClass('add-current-job')) {
                    if ('currentOccupations' in jobsList) {
                        if (jobsList.currentOccupations.includes(jobIdToAdd)) return false;
                        jobsList.currentOccupations = [jobIdToAdd, ...jobsList.currentOccupations];
                    } else {
                        jobsList.currentOccupations = [jobIdToAdd];
                    }
                }
                
                if (div.hasClass('add-previous-job')) {
                    if ('previousOccupations' in jobsList) {
                        if (jobsList.previousOccupations.includes(jobIdToAdd)) return false;
                        jobsList.previousOccupations = [jobIdToAdd, ...jobsList.previousOccupations];
                    } else {
                        jobsList.previousOccupations = [jobIdToAdd];
                    }
                }
                
                if (div.hasClass('add-desired-job')) {
                    if ('desiredOccupations' in jobsList) {
                        if (jobsList.desiredOccupations.includes(jobIdToAdd)) return false;
                        jobsList.desiredOccupations = [jobIdToAdd, ...jobsList.desiredOccupations];
                    } else {
                        jobsList.desiredOccupations = [jobIdToAdd];
                    }
                }

                inputJobs.val(JSON.stringify(jobsList))

                if (ulJobs) {
                    let type = div.hasClass('add-desired-job') ? 'desired' : div.hasClass('add-current-job') ? 'current' : div.hasClass('add-previous-job') ? 'previous' : '';
                    occupation.type = div.hasClass('add-desired-job') ? 'desired' : div.hasClass('add-current-job') ? 'current' : div.hasClass('add-previous-job') ? 'previous' : '';
                    let li = _this.tplJob(occupation);
                    $(ulJobs).append(li);
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

            let occupation = {};
            let inputJobs = $('body').find('#jobs[type="hidden"]');
            let id = $(this).attr('data-id');
            let type = $(this).attr('data-type');
            let li = $(this).closest('li');
            
            if (inputJobs && inputJobs.val()) {
                let jobsList = JSON.parse(inputJobs.val());
                
                if (id && type) {
                    if (type in jobsList) {
                        if (jobsList[type].includes(id)) {
                            jobsList[type] = jobsList[type].filter(function (el) {
                                return el != id;
                            });
                            inputJobs.val(JSON.stringify(jobsList));
                        }
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

                if (inputTrainings && inputTrainings.val()) {
                    trainingsList = JSON.parse(inputTrainings.val());
                }
                
                
                if (trainingsList.includes(trainingIdToAdd)) return false;
                trainingsList = [trainingIdToAdd, ...trainingsList];
                
                inputTrainings.val(JSON.stringify(trainingsList))
                
                if (ul) {
                    console.log('training', training)
                    let li = _this.tplTraining(training);
                    $(ul).append(li);
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

            let inputTrainings = $('body').find('#trainings[type="hidden"]');
            let id = $(this).attr('data-id');
            let li = $(this).closest('li');
            
            if (inputTrainings && inputTrainings.val()) {
                let trainingsList = JSON.parse(inputTrainings.val());
                
                if (id) {
                    if (trainingsList.includes(id)) {
                        trainingsList = trainingsList.filter(function (el) {
                            return el != id;
                        });
                        inputTrainings.val(JSON.stringify(trainingsList));
                    }
                }

                if (li) {
                    li.remove();
                }
            }
        });
    }

    /**
     * Suppression d'une formation
     */
     saveTraining = () => {

        let _this = this;

        $('body').on('click', '#content-work button[type="submit"]', function(e) {
            e.preventDefault();

            let inputOccupation = $('body').find('#jobs[type="hidden"]');
            
            if (inputOccupation && inputOccupation.val()) {

                let occupations = JSON.stringify(inputOccupation.val());
                let url = `api/user_occupation`;

                $.ajax({
                    url: url,
                    type: "POST",
                    dataType: 'json',
                    contentType: 'application/json',
                    data: occupations,
                    success: function (data, textStatus, jqXHR) {
                        console.log('data', data)
                    }
                });
            }
        });
    }

    init = function() {
        this.runDetail();
        this.runAutocompletion();
        this.addJob();
        this.removeJob();
        this.removeTraining();
        this.addTraining();
        this.saveTraining();
    }
}

$(document).ready(function() {
    let account = new Account();
    account.init();
});
