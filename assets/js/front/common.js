import $ from 'jquery';

class Common {
    instanceProperty = "Main";
    boundFunction = () => {
        return this.instanceProperty;
    }

    runConstraintValidation = () => {
        let inputsRequired = document.querySelectorAll('input[required]');
        let validateEmail = function(email) {
            const re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
            return re.test(email);
        }

        if (inputsRequired) {
            inputsRequired.forEach(function(inputRequired) {
        
                let typeInput = inputRequired.getAttribute('type');
        
                inputRequired.addEventListener('input', () => {
                    inputRequired.setCustomValidity('');
                    inputRequired.checkValidity();
                });
        
                inputRequired.addEventListener('invalid', () => {
                    if (typeInput == 'email') {
                        if (!validateEmail(inputRequired.value)) {
                            inputRequired.setCustomValidity(translationsJS && translationsJS.are_you_sure_you_want_to_delete_this_item ? translationsJS.are_you_sure_you_want_to_delete_this_item : 'The email format is incorrect!');
                        }
                    }

                    if (inputRequired.value === '') {
                        inputRequired.setCustomValidity(translationsJS && translationsJS.this_field_is_required ? translationsJS.this_field_is_required : 'This field is required!');
                    }

                });
            });
        }
    }

    
    /**
     * Supprime les cookies de la page de recherche
     * (utilisé pour sauvegardé la recherche lors du changement de langue)
     */
     removeCookiePageSearch = () => {
        let _this = this;
        let url = window.location.href;
        if (url.indexOf("search_results") == -1) {
            document.cookie = "type_search_silkc_search=null; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
            document.cookie = "occupation_id_silkc_search=null; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
            document.cookie = "skill_id_silkc_search=null; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
            document.cookie = "filters_silkc_search=null; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
            document.cookie = "params_request_all=null; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
        }
    };

    init = function() {
        this.runConstraintValidation();
        this.removeCookiePageSearch();
    }
}

$(document).ready(function() {
    let common = new Common();
    common.init();
});
