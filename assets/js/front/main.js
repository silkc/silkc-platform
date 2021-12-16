import $ from "jquery";
import bootbox from "bootbox";
import autocomplete from "autocompleter";
//import '../../css/bootstrap-extended.css';

// any CSS you import will output into a single css file (app.css in this case)
import "../../scss/elements/header.scss";
import "../../scss/app.scss";

require("bootstrap");
//require('popper');
var moment = require("moment");
require("chart.js");
require("@fortawesome/fontawesome-free/js/all.min");
require("bootstrap-select");
const marked = require("marked");

class Main {
    instanceProperty = "Main";
    boundFunction = () => {
        return this.instanceProperty;
    };

    /**
     * Autocompletion inputs
     * (ajouter l'attribut data-url et la class input-autocomplete à l'input de type text)
     */
    runAutocompletion = () => {
        let inputs = document.getElementsByClassName("input-autocomplete");
        let datas = {};
        let lang = $("body").attr("lang");

        let runAutocomplete = function (data, input) {
            let elemsDisabled = $(input)
                .closest("form")
                .find(".disabled-search");
            let name = input.getAttribute("name");
            let hiddenField = document.getElementById("hidden_" + name);
            let loader = document.getElementById("loader_" + name);
            let minLength = 2;

            $(input).closest("form").attr("autocomplete", "off");

            autocomplete({
                input: input,
                minLength: minLength,
                emptyMsg: "No elements found",
                render: function (item, currentValue) {
                    /*if (item.translations) {
                        item = item.translations[Object.keys(item.translations)[0]]
                    }*/
                    let div = document.createElement("div");
                    div.dataset.id = item.id;
                    div.textContent =
                        item.preferredLabel != undefined
                            ? item.preferredLabel
                            : item.name != undefined
                            ? item.name
                            : ""; // preferredLabel => table ESCO, name => table training
                    return div;
                },
                fetch: function (text, callback) {
                    text = text.toLowerCase();
                    let suggestions = data.filter((n) =>
                        n.preferredLabel != undefined
                            ? n.preferredLabel.toLowerCase().includes(text)
                            : n.name != undefined
                            ? n.name.toLowerCase().includes(text)
                            : ""
                    );
                    callback(suggestions);
                },
                onSelect: function (item) {
                    if ($(item).attr("data-associated") == true) return false;

                    input.value =
                        item.preferredLabel != undefined
                            ? item.preferredLabel
                            : item.name;
                    elemsDisabled.prop("disabled", false);
                    if (hiddenField && item.id) {
                        hiddenField.value = item.id;
                    }
                },
            });

            /* Si on vide le champs
            On desactive le bouton de recherche */
            input.addEventListener("keyup", function () {
                let search = this.value.toLowerCase();
                if (!search || search.length == 0) {
                    input.value = "";
                    if (hiddenField) {
                        hiddenField.value = "";
                        elemsDisabled.prop("disabled", true);
                    }
                }
            });

            /* Si on sort du champs de recherche sans avoir sélectionner un item, on sélectionne la première proposition
            Si il n'y a pas de propositions, on vide le champs */
            input.addEventListener("focusout", function () {
                let search = this.value.toLowerCase();
                let suggestions = data.filter((n) =>
                    n.preferredLabel != undefined
                        ? n.preferredLabel.toLowerCase().includes(search)
                        : n.name.toLowerCase().includes(search)
                );
                if (
                    suggestions &&
                    suggestions.length > 0 &&
                    search.length > 0
                ) {
                    let suggestion = suggestions[0];
                    input.value =
                        suggestion.preferredLabel != undefined
                            ? suggestion.preferredLabel
                            : suggestion.name != undefined
                            ? suggestion.name
                            : "";
                    if (hiddenField)
                        hiddenField.value =
                            suggestion.id != undefined ? suggestion.id : "";
                    elemsDisabled.prop("disabled", false);
                } else {
                    input.value = "";
                    if (hiddenField) {
                        hiddenField.value = "";
                        elemsDisabled.prop("disabled", true);
                    }
                }
            });

            if (loader) {
                loader.style.display = "none";
                input.disabled = false;
            }
        };

        if (inputs) {
            for (var i = 0; i < inputs.length; i++) {
                let input = inputs[i];
                let baseUrl = input.getAttribute("data-url");
                if (!baseUrl.includes("trainings"))
                    baseUrl = baseUrl + "/main/locale/" + lang;
                let formats = input.getAttribute("data-formats") || "json";
                let pagination = input.getAttribute("data-pagination") || false;
                let params = $.param({
                    formats: formats,
                    pagination: pagination,
                });

                let url = `${baseUrl}?${params}`;

                if (url && input) {
                    if (
                        datas &&
                        ((url.includes("skills") && "skills" in datas) ||
                            (url.includes("occupations") &&
                                "occupations" in datas) ||
                            (url.includes("trainings") && "trainings" in datas))
                    ) {
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
                            success: function (data, textStatus, jqXHR) {
                                if (url.includes("skills"))
                                    datas.skills = JSON.stringify(data);
                                if (url.includes("occupations"))
                                    datas.occupations = JSON.stringify(data);
                                if (url.includes("trainings"))
                                    datas.trainings = JSON.stringify(data);

                                runAutocomplete(data, input);
                            },
                        });
                    }
                }
            }
        }
    };

    tabsSignup = () => {
        /*$('body').on('click', '.login-form .tabs-signup a', function(e) {
            e.preventDefault();

            var type = $(this).attr('href');
            $('.login-form .tabs-signup a').removeClass('active');
            $(this).addClass('active');

            $('.login-form .input-' + type).show();
            
            if (type == 'user') {
                $('.form-group.placeholder input[type="text"]').attr('placeholder', 'Username');
                $('.login-form .input-institution').hide();
                $('.login-form .input-' + type).show();
            }
            if (type == 'institution') {
                $('.form-group.placeholder input[type="text"]').attr('placeholder', 'Institution name');
                $(this).closest('fieldset').removeClass('user');
                $('.login-form .input-user').hide();
            }
        });*/
    };

    runMarkdown = () => {
        const markdownElems = document.querySelectorAll('[markdown="1"]');

        if (
            markdownElems !== undefined &&
            markdownElems.length !== undefined &&
            markdownElems.length > 0
        ) {
            markdownElems.forEach((elem) => {
                const elemHTML = elem.innerHTML;
                elem.innerHTML = marked(elemHTML.trim());
            });
        }
    };

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


     initInputPrice = () => {
        let _this = this;
        $('body').on('click', '#training_isFree', function(e) {
            let inputPrice = $('#training_price');
            let inputCurrency = $('#training_currency');
            if($(this).is(':checked') ){
                inputCurrency.parent().addClass('disabled');
                inputPrice.prop('disabled', true);
            } else {
                inputCurrency.parent().removeClass('disabled');
                inputPrice.prop('disabled', false);
            }
        });
    };

    init = function () {
        this.runAutocompletion();
        this.tabsSignup();
        this.runMarkdown();
        this.removeCookiePageSearch();
        this.initInputPrice();

        $("select.selectpicker").selectpicker({
            liveSearch: true,
            noneSelectedText: "No choice selected",
            noneResultsText: "No result",
            showContent: true,
            actionsBox: true,
            selectAllText: "Select all",
            deselectAllText: "Unselect all",
        });

        $(".scrolltop").on("click", function (e) {
            e.preventDefault();
            window.scrollTo(0, 0);
        });
    };
}

$(document).ready(function () {
    let main = new Main();
    main.init();
});
