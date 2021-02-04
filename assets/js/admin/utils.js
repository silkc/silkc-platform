import $ from 'jquery';

require('bootstrap');
const bootbox = require('bootbox/bootbox');
require('@fortawesome/fontawesome-free/js/all.min');

class Utils {
    instanceProperty = "Utils";
    boundFunction = () => {
        return this.instanceProperty;
    }

    static init = function() {
        Utils.runBindDelete();
    }

    static runBindDelete = () => {
        $('body').on('click', 'button.delete-question', function(e) {
            e.preventDefault();
            const $button = $(this);
            bootbox.confirm({message : 'Êtes-vous sûr de vouloir supprimer cette élément ?', buttons : { cancel : { label : 'Annuler'}, confirm : { label : 'Oui'}}, callback : function(result) {
                if (result == true) {
                    window.location.href = $button.data('href');
                }
            }});
        });
    }
}

$(document).ready(function() {
    Utils.init();
});

