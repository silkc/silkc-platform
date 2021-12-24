import $ from "jquery";
var Slider = require("bootstrap-slider");
require("bootstrap-slider/dist/css/bootstrap-slider.min.css");

//import '../../css/bootstrap-extended.css';

import "datatables.net";
import "datatables.net-select-dt";
import "datatables.net-dt/css/jquery.dataTables.min.css";
import "datatables.net-select-dt/css/select.dataTables.min.css";
import "datatables.net-fixedcolumns";

// any CSS you import will output into a single css file (app.css in this case)
import "../../scss/elements/header.scss";
import "../../scss/search_results.scss";

require("bootstrap");
const bootbox = require("bootbox/bootbox");
require("bootstrap-star-rating");
require("bootstrap-star-rating/css/star-rating.css");
require("bootstrap-star-rating/themes/krajee-svg/theme.css");
//require('popper');
var moment = require("moment");
require("chart.js");
require("@fortawesome/fontawesome-free/js/all.min");

let tradsDatatable = {
    search: translationsJS && translationsJS.datatable_search ? translationsJS.datatable_search : 'Search:',
    loadingRecords:  "&nbsp;",
    processing: '<div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div>',
    zeroRecords: "&nbsp;",
    paginate: {
        first: translationsJS && translationsJS.datatable_first ? translationsJS.datatable_first : 'First:',
        previous: translationsJS && translationsJS.datatable_previous ? translationsJS.datatable_previous : 'Previous:',
        next: translationsJS && translationsJS.datatable_next ? translationsJS.datatable_next : 'Next:',
        last: translationsJS && translationsJS.datatable_last ? translationsJS.datatable_last : 'Last:'
    }
};

class SearchResults {
    instanceProperty = "SearchResults";
    boundFunction = () => {
        return this.instanceProperty;
    };

    constructor() {
        this.sliderDistance;
        this.sliderPrice;
    }

    runRater = () => {
        $('[data-toggle="tooltip"]').tooltip();

        $("span.rating").each(function (index) {
            let $elem = $(this);
            let value = $elem.data("value");
            $elem
                .rating({
                    filledStar: '<i class="fas fa-star"></i>',
                    emptyStar: '<i class="far fa-star"></i>',
                    showCaption: false,
                    size: "xs",
                    step: 1,
                    readonly: true,
                    showClear: false,
                })
                .rating("update", value);
        });
    };

    /**
     * Selection du type de recherche (occupation/skill)
     */
    runTypeSearch = () => {
        let $formOccupation = $("#search-results .form-search-occupation");
        let $formSkill = $("#search-results .form-search-skill");

        $("body").on("change", 'select[name="type_search"]', function () {
            $formOccupation.find(".input-autocomplete").val("");
            $formOccupation.find('input[type="hidden"]').val("");
            $formSkill.find(".input-autocomplete").val("");
            $formSkill.find('input[type="hidden"]').val("");

            if (this.value == "occupation") {
                $formSkill.hide();
                $formOccupation.show();
            }
            if (this.value == "skill") {
                $formOccupation.hide();
                $formSkill.show();
            }

            $(this)
                .closest("form")
                .find(".disabled-search")
                .prop("disabled", true);
        });
    };

    /**
     * Actions utilisateur lors de la recherche d'une formation (score)
     */
    setScore = () => {
        // Collapse show
        $("#search-results #accordion").on("show.bs.collapse", function (e) {
            let card = $(e.target).closest(".card");
            let id = card.attr("data-id");

            let params = $.param({ id: id, score: 50 });
            let url = `/set_score?${params}`;

            $.ajax({
                type: "GET",
                url: url,
                success: function (data, textStatus, jqXHR) {},
            });
        });

        // More info
        $("body").on(
            "click",
            "#search-results #accordion .btn-more",
            function (e) {
                let card = $(this).closest(".card");
                let id = card.attr("data-id");

                let params = $.param({ id: id, score: 100 });
                let url = `/set_score?${params}`;

                $.ajax({
                    type: "GET",
                    url: url,
                    success: function (data, textStatus, jqXHR) {},
                });
            }
        );
    };

    runDonetraining = () => {
        $("body").on(
            "click",
            "#search-results #accordion .btn-done",
            function () {
                let _this = this;
                $(_this).attr("disabled", true);

                let token = $("body").attr("data-token");
                let id = $(this).attr("data-id");
                let url = "/api/done_training/" + id;

                bootbox.confirm({
                    message:
                        translationsJS &&
                        translationsJS.can_you_confirm_that_you_have_completed_this_training
                            ? translationsJS.can_you_confirm_that_you_have_completed_this_training
                            : "Confirm",
                    buttons: {
                        cancel: { label: "Cancel" },
                        confirm: { label: "Yes" },
                    },
                    callback: function (result) {
                        if (result == true) {
                            $.ajax({
                                url: url,
                                type: "POST",
                                dataType: "json",
                                data: {},
                                headers: { "X-auth-token": token },
                                success: function (data, textStatus, jqXHR) {
                                    if (
                                        data.result != undefined &&
                                        data.result == true
                                    ) {
                                        $(_this)
                                            .removeClass("btn-done")
                                            .addClass("btn-notdone");
                                        $(_this)
                                            .removeClass("btn-success")
                                            .addClass("btn-warning");
                                        $(_this).html(
                                            "I did not do this training"
                                        );
                                    } else {
                                        bootbox.alert("An error occured");
                                    }
                                },
                                error: function (resultat, statut, erreur) {
                                    bootbox.alert("An error occured");
                                },
                                complete: function () {
                                    $(_this).attr("disabled", false);
                                },
                            });
                        } else {
                            $(_this).attr("disabled", false);
                        }
                    },
                });
            }
        );

        $("body").on(
            "click",
            "#search-results #accordion .btn-notdone",
            function () {
                let _this = this;
                $(_this).attr("disabled", true);

                let token = $("body").attr("data-token");
                let id = $(this).attr("data-id");
                let url = "/api/undone_training/" + id;

                bootbox.confirm({
                    message:
                        translationsJS &&
                        translationsJS.can_you_confirm_that_you_did_not_take_this_training
                            ? translationsJS.can_you_confirm_that_you_did_not_take_this_training
                            : "Confirm",
                    buttons: {
                        cancel: { label: "Cancel" },
                        confirm: { label: "Yes" },
                    },
                    callback: function (result) {
                        if (result == true) {
                            $.ajax({
                                url: url,
                                type: "POST",
                                dataType: "json",
                                data: {},
                                headers: { "X-auth-token": token },
                                success: function (data, textStatus, jqXHR) {
                                    if (
                                        data.result != undefined &&
                                        data.result == true
                                    ) {
                                        $(_this)
                                            .removeClass("btn-notdone")
                                            .addClass("btn-done");
                                        $(_this)
                                            .removeClass("btn-warning")
                                            .addClass("btn-success");
                                        $(_this).html(
                                            "I've done this training"
                                        );
                                    } else {
                                        bootbox.alert("An error occured");
                                    }
                                },
                                error: function (resultat, statut, erreur) {
                                    bootbox.alert("An error occured");
                                },
                                complete: function () {
                                    $(_this).attr("disabled", false);
                                },
                            });
                        } else {
                            $(_this).attr("disabled", false);
                        }
                    },
                });
            }
        );
    };

    runMap = () => {
        $("#search-results #accordion").on("shown.bs.collapse", function (e) {
            let blcMap = e.target.querySelector(".blc-map");
            if (blcMap) {
                let mapContent = blcMap.querySelector(".map");
                let trainingAddress = blcMap.querySelector(".training_address");
                let trainingAddressHidden = blcMap.querySelector(
                    ".training_address_hidden"
                );

                if (!mapContent || mapContent.innerHTML != "") return false;

                let map = null;
                let coords = trainingAddressHidden.value;

                if (!coords) return false;

                if (
                    /^[\],:{}\s]*$/.test(
                        coords
                            .replace(/\\["\\\/bfnrtu]/g, "@")
                            .replace(
                                /"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g,
                                "]"
                            )
                            .replace(/(?:^|:|,)(?:\s*\[)+/g, "")
                    )
                ) {
                    coords = JSON.parse(coords);
                    map = L.map(mapContent).setView(
                        [coords.lat, coords.lng],
                        10
                    );
                    let geocoder = L.Control.Geocoder.nominatim();

                    let control = L.Control.geocoder({
                        collapsed: false,
                        placeholder:
                            translationsJS && translationsJS.search_here
                                ? translationsJS.search_here
                                : "Search here...",
                        position: "topleft",
                        geocoder: geocoder,
                    }).addTo(map);

                    // Créer l'objet "map" et l'insèrer dans l'élément HTML qui a l'ID "map"
                    // Leaflet ne récupère pas les cartes (tiles) sur un serveur par défaut. Nous devons lui préciser où nous souhaitons les récupérer. Ici, openstreetmap.fr
                    L.tileLayer(
                        "https://{s}.tile.openstreetmap.fr/osmfr/{z}/{x}/{y}.png",
                        {
                            attribution: "",
                            minZoom: 1,
                            maxZoom: 20,
                        }
                    ).addTo(map);

                    //document.getElementById('searchmap').appendChild(document.querySelector('.leaflet-control-geocoder.leaflet-bar'));

                    if (coords) {
                        let marker = L.marker([coords.lat, coords.lng]).addTo(
                            map
                        ); // Markeur
                        marker.bindPopup(coords.city); // Bulle d'info

                        trainingAddress.innerHTML = coords.city;
                    }
                } else {
                    blcMap.innerHTML = coords;
                }
            }
        });
    };

    runKeepSearch = () => {
        $("body").on("change", "#search-save-results", function (e) {
            let url = "/api/toggle_user_searches_param";
            let token = $("body").attr("data-token");
            let data = {};

            $.ajax({
                url: url,
                type: "POST",
                data: data,
                dataType: "json",
                headers: { "X-auth-token": token },
                success: function (data, textStatus, jqXHR) {
                    if (data.result) {
                        $("#history-search").show();
                    } else {
                        $("#history-search").hide();
                    }
                },
                error: function () {
                    bootbox.alert("An error occured");
                },
            });
        });
    };

    sliderSearch = () => {
        let _this = this;
        /*let sliderDistance;
        let sliderPrice;*/

        // PRIX
        let initSliderPrice = function () {
            _this.sliderPrice = new Slider("#formControlRangePrice", {
                formatter: function (value) {
                    let currencyAcronym = $("#currency")
                        .find("option:selected")
                        .attr("data-acronym");
                    return value + currencyAcronym;
                },
            });
            _this.sliderPrice.on("change", function (slideEvt) {
                let min = slideEvt.newValue[0];
                let max = slideEvt.newValue[1];
                let currencyAcronym = $("#currency")
                    .find("option:selected")
                    .attr("data-acronym");

                $("#priceValMin > span:first-child").text(min);
                $("#priceValMax > span:first-child").text(max);
                //$("#priceValMin > span:last-child").text(currencyAcronym);
                $("#priceValMax > span:last-child").text(currencyAcronym);
                $("#minPrice").val(min);
                $("#maxPrice").val(max);
                $("#bckMinPrice").val(min);
                $("#bckMaxPrice").val(max);
            });

            // free training
            $("body").on("click", "#isFree", function () {
                if($(this).is(':checked') ){
                    _this.sliderPrice.disable();
                    $('#currency').attr('disabled', true);
                    $("#minPrice").val(0);
                    $("#maxPrice").val(0); 
                } else {
                    _this.sliderPrice.enable();
                    $('#currency').attr('disabled', false);
                    $("#minPrice").val($('#bckMinPrice').val());  
                    $("#maxPrice").val($('#bckMaxPrice').val()); 
                    $("#priceValMin > span:first-child").text($('#bckMinPrice').val());
                    $("#priceValMax > span:first-child").text($('#bckMaxPrice').val());
                    _this.sliderPrice.setValue([parseInt($('#bckMinPrice').val()), parseInt($('#bckMaxPrice').val())]);
                }
            });

            if ($("body #isFree").is(':checked') ) {
                _this.sliderPrice.disable();
                $('#currency').attr('disabled', true);
                $("#minPrice").val($('#bckMinPrice').val());  
                $("#maxPrice").val($('#bckMaxPrice').val());  
            }
        };
        
        // DURATION
        let initSliderDuration = function () {
            _this.sliderDuration = new Slider("#formControlRangeDuration", {
                formatter: function (value) {
                    let unity = $("#unity").find("option:selected").val();
                    return value + unity;
                },
            });
            _this.sliderDuration.on("change", function (slideEvt) {
                let min = slideEvt.newValue[0];
                let max = slideEvt.newValue[1];
                let unity = $("#unity").find("option:selected").val();

                $("#durationValMin > span:first-child").text(min);
                $("#durationValMax > span:first-child").text(max);
                //$("#durationValMin > span:last-child").text(unity);
                $("#durationValMax > span:last-child").text(unity);
                $("#minDuration").val(min);
                $("#maxDuration").val(max);
            });
        };

        // DISTANCE
        let initSliderDistance = function () {
            _this.sliderDistance = new Slider("#formControlRangeDistance", {
                formatter: function (value) {
                    return value + " km";
                }
            });
            _this.sliderDistance.on("change", function (obj) {
                $("#distanceVal").text(obj.newValue + "km");
                $("#distance").val(obj.newValue);
            });
        
            if (!$('#city').val() && !$('#input-city').val())
                _this.sliderDistance.disable();
        };

        $("#currency").on("change", function () {
            $("#priceValMax > span:last-child").text(
                $(this).find("option:selected").attr("data-acronym")
            );

            let max = _this.sliderPrice.element.dataset.sliderMax;
            $("#priceValMin > span:first-child").text(0);
            $("#priceValMax > span:first-child").text(max);

            $("#minPrice").val(0);
            $("#maxPrice").val(max);

            _this.sliderPrice.setValue([0, 5000]);
        });

        $("#unity").on("change", function () {
            $("#durationValMax > span:last-child").text(
                $(this).find("option:selected").val()
            );

            let max = _this.sliderDuration.element.dataset.sliderMax;
            $("#durationValMin > span:first-child").text(0);
            $("#durationValMax > span:first-child").text(max);

            $("#minDuration").val(0);
            $("#maxDuration").val(max);

            _this.sliderDuration.setValue([0, 100]);
        });

        // Clear filter
        $("body").on("click", "button.btn-clear", function () {

            _this.removeCookiesParamsSearch();

            _this.sliderDistance.setValue(0);
            _this.sliderPrice.setValue([0, 5000]);

            // Distance - ville
            $("#city").val("");
            $("#inputCity").val("");
            $("#distanceVal").text("0km");
            $("#distance").val(0);

            // Prix
            let max = _this.sliderPrice.element.dataset.sliderMax;
            $("#priceValMin > span:first-child").text(0);
            $("#priceValMax > span:first-child").text(max);

            $("#minPrice").val(0);
            $("#maxPrice").val(max);

            // Duration
            $("#durationValMin > span:first-child").text(0);
            $("#durationValMax > span:first-child").text(100);

            $("#minDuration").val(0);
            $("#maxDuration").val(100);
            _this.sliderDuration.setValue([0, 100]);

            // Input text, date ...
            $(
                "#advanced-search input[type=date], #advanced-search input[type=text], #advanced-search input[type=datetime-local]"
            ).val("");

            // Checkbox
            $("#advanced-search input[type=checkbox]").prop("checked", false);
        });

        // Remove tags filters
        $("body").on("click", "button.tag-city", function () {
            _this.removeCookiesParamsSearch();
            _this.sliderDistance.setValue(0);
            $("#city").val("");
            $("#inputCity").val("");
            $("#distanceVal").text("0km");
            $("#distance").val(0);
            $("#advanced-search .leaflet-control-geocoder-form input[type=text]").val("");
            $(this).remove();
            setTimeout(function () {
                $(".form-results").submit();
            }, 500);
        });
        $("body").on("click", "button.tag-price", function () {
            _this.removeCookiesParamsSearch();
            _this.sliderPrice.setValue([0, 5000]);
            let max = _this.sliderPrice.element.dataset.sliderMax;
            $("#priceValMin > span:first-child").text(0);
            $("#priceValMax > span:first-child").text(max);
            $("#minPrice").val(0);
            $("#maxPrice").val(max);
            $("#isFree").prop('checked', false);
            $(this).remove();
            setTimeout(function () {
                $(".form-results").submit();
            }, 500);
        });

        $("body").on("click", "button.tag-duration", function () {
            _this.removeCookiesParamsSearch();
            _this.sliderDuration.setValue([0, 100]);
            let max = _this.sliderDuration.element.dataset.sliderMax;
            $("#durationValMin > span:first-child").text(0);
            $("#durationValMax > span:first-child").text(max);
            $("#minDuration").val(0);
            $("#maxDuration").val(max);
            $(this).remove();
            setTimeout(function () {
                $(".form-results").submit();
            }, 500);
        });
        $("body").on("click", "button.tag-startAt", function () {
            _this.removeCookiesParamsSearch();
            $("#advanced-search #startAt").val("");
            $(this).remove();
            setTimeout(function () {
                $(".form-results").submit();
            }, 500);
        });
        $("body").on("click", "button.tag-endAt", function () {
            _this.removeCookiesParamsSearch();
            $("#advanced-search #endAt").val("");
            $(this).remove();
            setTimeout(function () {
                $(".form-results").submit();
            }, 500);
        });
        $("body").on("click", "button.tag-isOnline", function () {
            _this.removeCookiesParamsSearch();
            $('#isOnline').prop("checked", false);
            $(this).remove();
            setTimeout(function () {
                $(".form-results").submit();
            }, 500);
        });
        $("body").on("click", "button.tag-isOnlineMonitored", function () {
            _this.removeCookiesParamsSearch();
            $('#isOnlineMonitored').prop(
                "checked",
                false
            );
            $(this).remove();
            setTimeout(function () {
                $(".form-results").submit();
            }, 500);
        });
        $("body").on("click", "button.tag-isPresential", function () {
            _this.removeCookiesParamsSearch();
            $('#isPresential').prop("checked", false);
            $(this).remove();
            setTimeout(function () {
                $(".form-results").submit();
            }, 500);
        });
        $("body").on("click", "button.tag-excludeTraining", function () {
            _this.removeCookiesParamsSearch();
            $('#exclude-training-without-completed-description').prop("checked", false);
            $(this).remove();
            setTimeout(function () {
                $(".form-results").submit();
            }, 500);
        });
        $("body").on("click", "button.tag-specifiedDuration", function () {
            _this.removeCookiesParamsSearch();
            $('#without-specified-duration').prop(
                "checked",
                false
            );
            $(this).remove();
            setTimeout(function () {
                $(".form-results").submit();
            }, 500);
        });
        $("body").on("click", "button.tag-isCertified", function () {
            _this.removeCookiesParamsSearch();
            $('#isCertified').prop(
                "checked",
                false
            );
            $(this).remove();
            setTimeout(function () {
                $(".form-results").submit();
            }, 500);
        });

        initSliderDistance(0);
        initSliderPrice();
        initSliderDuration();
    };

    /**
     * Affichage carte
     */
    runMapFilter = () => {
        let _this = this;
        var map = L.map("map").setView([0, 0], 2);
        let geocoder = L.Control.Geocoder.nominatim();
        let inputHidden = document.getElementById("city");
        let inputHiddenCity = document.getElementById("inputCity");

        let control = L.Control.geocoder({
            collapsed: false,
            placeholder:
                translationsJS && translationsJS.search_here
                    ? translationsJS.search_here
                    : "Search here...",
            position: "topleft",
            geocoder: geocoder,
        })
            .on("markgeocode", function (e) {
                if (e.geocode && e.geocode.center) {

                    let lat = e.geocode.center.lat;
                    let lng = e.geocode.center.lng;
                    let name = e.geocode.name;

                    let newCoords = {
                        lat: lat,
                        lng: lng,
                    };
                    newCoords = JSON.stringify(newCoords);

                    let leafletControlGeocoderForm = document.querySelector(
                        "#search-results .leaflet-control-geocoder-form input"
                    );
                    leafletControlGeocoderForm.value = name;

                    if (inputHidden) inputHidden.value = newCoords;
                    if (inputHiddenCity) inputHiddenCity.value = name;

                    _this.sliderDistance.enable();
                }
            })
            .addTo(map);

        // Créer l'objet "map" et l'insèrer dans l'élément HTML qui a l'ID "map"
        // Leaflet ne récupère pas les cartes (tiles) sur un serveur par défaut. Nous devons lui préciser où nous souhaitons les récupérer. Ici, openstreetmap.fr
        /*L.tileLayer('https://{s}.tile.openstreetmap.fr/osmfr/{z}/{x}/{y}.png', {
            attribution: '',
            minZoom: 1,
            maxZoom: 20
        }).addTo(map);*/

        document
            .getElementById("searchmap")
            .appendChild(
                document.querySelector(
                    "#search-results .leaflet-control-geocoder.leaflet-bar"
                )
            );

        if (inputHiddenCity) {
            let leafletControlGeocoderForm = document.querySelector(
                "#search-results .leaflet-control-geocoder-form input"
            );
            leafletControlGeocoderForm.value = inputHiddenCity.value;
        }

        $("body").on(
            "keyup",
            "#search-results .leaflet-control-geocoder-form input",
            function (e) {
                let searchValue = $(this).val();
                if (searchValue.length == 0) {
                    _this.sliderDistance.setValue(0);
                    $("#distanceVal").text("0km");
                    $("#distance").val(0);
                    _this.sliderDistance.disable();
                }
            }
        );
    };

    /**
     * Affiche/masque filtres
     */
    displayFilters = () => {
        $("body").on("click", ".btn-filters", function () {
            let filters = $("#advanced-search");
            filters.toggleClass("active");
            $(this).toggleClass("active");
        });
    };

    /**
     * Affiche/masque un resultat
     */
    btnShowResult = () => {
        $("body").on( "click", "#datatable-results .card-header h4 button", function () {
                $(this).find("span").toggleClass("hide show");
            }
        );
    };

    /**
     * Affiche/masque info resultats
     */
     btnShowInfo = () => {
        $("body").on("click", ".info-results .see-more span", function () {
            $(this).parent("span").hide().closest("p").find(".hide").show();
        });
    };

    /**
     * Datatable resultats de recherche
     */
    runDatatableresults = () => {
        let tableResults = $("#datatable-results").DataTable({
            searching: true,
            info: false,
            lengthChange: false,
            order: [[1, "asc"]],
            language: tradsDatatable, 
            columnDefs: [
                {
                    targets: [0],
                    searchable: false,
                },
            ],
        });

        $("#input-search-datatatable").keyup(function () {
            tableResults.search($(this).val()).draw();
        });

        // trie du Datatable via le select
        $("body").on("change", "#sort-by", function () {
            let direction = $(this).val();
            let colName = $(this).find(":selected").attr("data-col");

            if (!direction || !colName) return false;
            let idxCol = $("#datatable-results")
                .find(`thead th[data-colname=${colName}]`)
                .index();
            if (idxCol == -1) return false;
            $('#search-results .collapse[data-parent="#accordion"]').collapse('hide');
            $("#datatable-results .card-header h4 button span:first-child").removeClass('hide').addClass('show');
            $("#datatable-results .card-header h4 button span:last-child").removeClass('show').addClass('hide');
            tableResults.order([idxCol, direction ? direction : "asc"]).draw();
        });
    };

    removeCookiesParamsSearch () {
        document.cookie =
            "type_search_silkc_search=null; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
        document.cookie =
            "occupation_id_silkc_search=null; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
        document.cookie =
            "skill_id_silkc_search=null; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
        document.cookie =
            "filters_silkc_search=null; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
        document.cookie =
        "params_request_all=null; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
    }

    init = function () {
        this.runTypeSearch();
        //this.setScore();
        this.runDonetraining();
        this.runMap();
        this.runKeepSearch();
        this.sliderSearch();
        this.runMapFilter();
        this.displayFilters();
        this.runRater();
        this.btnShowResult();
        this.btnShowInfo();
        this.runDatatableresults();

        $("body").on(
            "click",
            'form.form-search button[type="submit"]',
            function (e) {
                e.preventDefault();
                document.cookie =
                    "type_search_silkc_search=null; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
                document.cookie =
                    "occupation_id_silkc_search=null; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
                document.cookie =
                    "skill_id_silkc_search=null; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
                document.cookie =
                    "filters_silkc_search=null; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
                document.cookie =
                    "params_request_all=null; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";

                $(this).closest("form").submit();
            }
        );

        $('[data-toggle="tooltip"]').tooltip;
    };
}

$(document).ready(function () {
    var searchResults = new SearchResults();
    searchResults.init();
});
