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
        if (inputs) {
            for (var i = 0; i < inputs.length; i++) {
                let input = inputs[i];
                let url = inputs[i].getAttribute('data-url');

                if (url && input) {
                    $.ajax({
                        type: "GET",
                        url: url,
                        success: function (data, textStatus, jqXHR) {
                            autocomplete({
                                input: input,
                                minLength: 3,
                                emptyMsg: 'No elements found',
                                render: function(item, currentValue) {
                                    var div = document.createElement('div');
                                    div.dataset.id = item.id;
                                    div.textContent = (item.preferredLabel != undefined) ? item.preferredLabel : item.name; // preferredLabel => table ESCO, name => table training
                                    return div;
                                },
                                fetch: function(text, callback) {
                                    text = text.toLowerCase();
                                    var suggestions = data.filter(n => (n.preferredLabel != undefined) ? n.preferredLabel.toLowerCase().startsWith(text) : n.name.toLowerCase().startsWith(text))
                                    callback(suggestions);
                                },
                                onSelect: function(item) {
                                    input.value = (item.preferredLabel != undefined) ? item.preferredLabel : item.name;;
                                }
                            });
                        }
                    });
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

    init = function() {
        this.rmvItem();
        this.runAutocompletion();
        this.clearModal();
    }
}

$(document).ready(function() {
    let main = new Main();
    main.init();
});
