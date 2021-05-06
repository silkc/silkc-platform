import $ from 'jquery';
import bootbox from 'bootbox';
import autocomplete from 'autocompleter';
//import '../../css/bootstrap-extended.css';

// any CSS you import will output into a single css file (app.css in this case)
import '../../scss/elements/header.scss';
import '../../scss/app.scss';

require('bootstrap');
//require('popper');
var moment = require('moment');
require('chart.js');
require('@fortawesome/fontawesome-free/js/all.min');

class Main {
    instanceProperty = "Main";
    boundFunction = () => {
        return this.instanceProperty;
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

    /**
     * Suppression contenu de la modal
     */
    clearModal () {
        $('#common-modal').on('hidden.bs.modal', function (e) {
            $(this).find('.modal-title').children().remove();
            $(this).find('.modal-body').children().remove();
          })
    }

    /**
     * Suppression d'un item
     */
     rmvItem = () => {
        $('body').on('click', '.item.rmv', function(e) {
            e.preventDefault();

            let $this = $(this);
            let id = $this.attr('data-id');
            let name = $this.attr('data-name');
            let url = $this.attr('data-url');
            let data = {};

            if (!id || !url) return false;
            data.id = id

            let dialog = bootbox.confirm({
                message: `Do you want to delete ${name ? `"${name}"` : 'this item'}?`,
                buttons: {
                    cancel: {
                        label: 'Cancel',
                        className: 'btn-primary'
                    },
                    confirm: {
                        label: '<span class="spinner-border spinner-border-sm hide" role="status" aria-hidden="true"></span> Yes',
                        className: 'btn-danger'
                    }
                },
                callback: function (result) {
                    dialog.find('.bootbox-cancel').attr('disabled', 'disabled');
                    dialog.find('.bootbox-accept .spinner-border').removeClass('hide');

                    /*$.ajax({
                        type: "POST",
                        url: url,
                        data: data,
                        dataType: dataType,
                        success: function (data, textStatus, jqXHR) {*/

                            dialog.modal('hide');
                            
                        /*}
                    });*/
                    return false;
                }
            })
        });
    }

    /**
     * Google map
     */
     initGoogleMap = () => {
        let maps = document.getElementsByClassName('google-map');

        if (maps) {
            for (var i = 0; i < maps.length; i++) {
                let dataLocation = maps[i].getAttribute('data-location');

                //let url = `${baseUrl}?${params}`;



                if (dataLocation) {
                    const myLatLng = { lat: -25.363, lng: 131.044 };
                    const map = new google.maps.Map(maps[i], {
                        mapTypeControl: false,
                        zoom: 4,
                        center: myLatLng,
                    });
                    const contentString =
                    '<div class="google-address">' +
                    'Caen' +
                    "</div>";
                    const infowindow = new google.maps.InfoWindow({
                        content: contentString,
                      });
                    const marker = new google.maps.Marker({
                      position: myLatLng,
                      map,
                    });
                    marker.addListener("click", () => {
                        infowindow.open(map, marker);
                    });
                }
            }
        }
    }
        
    listenTrigger = () => {
        let _this = this;
        document.addEventListener('newTraining', function (e) {
            _this.runAutocompletion();
        }, false);
    }

    init = function() {
        this.initGoogleMap();
        this.rmvItem();
        this.runAutocompletion();
        this.clearModal();
        this.listenTrigger();
    }
}

$(document).ready(function() {
    let main = new Main();
    main.init();
});
