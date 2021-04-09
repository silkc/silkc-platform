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

    /**
     * Autocompletion inputs 
     * (ajouter l'attribut data-url et la class input-autocomplete à l'input de type text)
     */
    runAutocompletion = () => {
        var countries = [
            { label: "Acheteur"},
            { label: "Administrateur de base de données"},
            { label: "Agent de sûreté aéroportuaire"},
            { label: "Agent de transit"},
            { label: "Agent d'entretien"},
            { label: "Agent de presse"}
        ];
        
        var inputs = document.getElementsByClassName('input-autocomplete');
        if (inputs) {
            for (var i = 0; i < inputs.length; i++) {
                let input = inputs[i];
                autocomplete({
                    input: input,
                    fetch: function(text, update) {
                        text = text.toLowerCase();
                        // you can also use AJAX requests instead of preloaded data
                        var suggestions = countries.filter(n => n.label.toLowerCase().startsWith(text))
                        update(suggestions);
                    },
                    onSelect: function(item) {
                        input.value = item.label;
                    }
                });
            }
        }
    }

    /**
     * Au changement d'onglet
     */
    onShowTab () {
        let _this = this;
        $('#account a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            _this.runAutocompletion();
        })
    }


    init = function() {
        this.onShowTab();
        this.runAutocompletion();
    }
}

$(document).ready(function() {
    let account = new Account();
    account.init();
});
