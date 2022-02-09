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
        this.markerCircle;
        this.mapFilter;
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
                                            translationsJS &&
                                            translationsJS.user_had_not_done_this_training_btn
                                                ? translationsJS.user_had_not_done_this_training_btn
                                                : "I did not follow this training",
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
                                            translationsJS &&
                                            translationsJS.user_had_done_this_training_btn
                                                ? translationsJS.user_had_done_this_training_btn
                                                : "I've followed this training",
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
            "#search-results #accordion .btn-interested",
            function () {
                let _this = this;
                $(_this).attr("disabled", true);

                let token = $("body").attr("data-token");
                let id = $(this).attr("data-id");
                let url = "/api/interested_training/" + id;

                bootbox.confirm({
                    message:
                        translationsJS &&
                        translationsJS.can_you_confirm_that_you_are_interested_in_this_training
                            ? translationsJS.can_you_confirm_that_you_are_interested_in_this_training
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
                                            .removeClass("btn-interested")
                                            .addClass("btn-notinterested");
                                        $(_this)
                                            .removeClass("btn-success")
                                            .addClass("btn-warning");
                                        $(_this).html(
                                            translationsJS &&
                                            translationsJS.this_training_no_longer_interests_me
                                                ? translationsJS.this_training_no_longer_interests_me
                                                : "This training no longer interests me",
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
            "#search-results #accordion .btn-notinterested",
            function () {
                let _this = this;
                $(_this).attr("disabled", true);

                let token = $("body").attr("data-token");
                let id = $(this).attr("data-id");
                let url = "/api/notinterested_training/" + id;

                bootbox.confirm({
                    message:
                        translationsJS &&
                        translationsJS.can_you_confirm_that_you_are_not_interested_in_this_training
                            ? translationsJS.can_you_confirm_that_you_are_not_interested_in_this_training
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
                                            .removeClass("btn-notinterested")
                                            .addClass("btn-interested");
                                        $(_this)
                                            .removeClass("btn-warning")
                                            .addClass("btn-success");
                                        $(_this).html(
                                            translationsJS &&
                                            translationsJS.i_am_interested_in_this_training
                                                ? translationsJS.i_am_interested_in_this_training
                                                : "I am interested in this training",
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
                let trainingAddressHidden = blcMap.querySelector(".training_address_hidden");

                if (!mapContent || mapContent.innerHTML != "") return false;

                let map = null;
                let coords = trainingAddressHidden.value;

                if (!coords) return false;

                if (/^[\],:{}\s]*$/.test(coords
                            .replace(/\\["\\\/bfnrtu]/g, "@")
                            .replace(
                                /"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g,
                                "]"
                            )
                            .replace(/(?:^|:|,)(?:\s*\[)+/g, "")
                    )
                ) {
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
                    map.setZoom(12);
                } else {
                    blcMap.innerHTML = trainingAddressHidden.value;
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
                $("#priceValMax > span:last-child").text(currencyAcronym);
                $("#minPrice").val(min);
                $("#maxPrice").val(max);
                $("#bckMinPrice").val(min);
                $("#bckMaxPrice").val(max);
            });

            // Range
            $("body").on("click", "#priceTypeRange", function () {
                if($(this).is(':checked') ){
                    _this.sliderPrice.enable();
                    $('#currency').attr('disabled', false);
                }
            });
            // free training
            $("body").on("click", "#priceTypeFree", function () {
                _this.sliderPrice.disable();
                $('#currency').attr('disabled', true);
            });
            $("body").on("click", "#priceTypeAll", function () {
                _this.sliderPrice.disable();
                $('#currency').attr('disabled', true);
            });

            if ($('input#priceTypeFree').is(':checked') || $('input#priceTypeAll').is(':checked')) {
                _this.sliderPrice.disable();
                $('#currency').attr('disabled', true);
            }
        };
        
        $("#currency").on("change", function () {
            $("#priceValMax > span:last-child").text(
                $(this).find("option:selected").attr("data-acronym")
            );

            let maxPrice = $(this).find("option:selected").attr('data-max-price');

            $("#minPrice").val(0);
            $("#maxPrice").val(maxPrice);

            $("#priceValMin > span:first-child").text(0);
            $("#priceValMax > span:first-child").text(maxPrice);

            $("#minPrice").val(0);
            $("#maxPrice").val(maxPrice);

            _this.sliderPrice.setAttribute('max', parseInt(maxPrice));
            _this.sliderPrice.setValue([0, parseInt(maxPrice)]);

        });

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

                let inputHidden = document.getElementById("city");
                let map = _this.mapFilter;

                if (inputHidden && map) {
                    let latLng = inputHidden.value;
                    latLng = latLng.length > 0 ? JSON.parse(latLng) : false;
                    if (inputHidden) {
                        if (_this.markerCircle) _this.markerCircle.setMap(null); // Suppression circle
                        _this.markerCircle = new google.maps.Circle({
                            strokeColor: "rgb(51, 136, 255)",
                            strokeOpacity: 0.5,
                            strokeWeight: 1,
                            fillColor: "rgb(51, 136, 255)",
                            fillOpacity: 0.2,
                            map,
                            center: latLng,
                            radius: obj.newValue * 1000,
                        });
                    }
                }
            });
        
            if (!$('#city').val() && !$('#input-city').val())
                _this.sliderDistance.disable();
        };

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
            $("#distanceVal").text("1km");
            $("#distance").val(1);

            // Prix
            let max = _this.sliderPrice.element.dataset.sliderMax;
            $("#priceValMin > span:first-child").text(0);
            $("#priceValMax > span:first-child").text(max);

            $("#minPrice").val(0);
            $("#maxPrice").val(max);
            $("#priceTypeAll").trigger("click");

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
            $("#distanceVal").text("1km");
            $("#distance").val(1);
            $("#advanced-search .leaflet-control-geocoder-form input[type=text]").val("");
            $(this).remove();
            setTimeout(function () {
                $(".form-results").submit();
            }, 500);
        });
        $("body").on("click", "button.tag-price", function () {
            _this.removeCookiesParamsSearch();
            let maxPrice = $("#currency").find("option:selected").attr('data-max-price');
            _this.sliderPrice.setValue([0, parseInt(maxPrice)]);
            $("#priceTypeAll").trigger("click");
            $("#priceValMin > span:first-child").text(0);
            $("#priceValMax > span:first-child").text(maxPrice);
            $("#minPrice").val(0);
            $("#maxPrice").val(maxPrice);
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

        initSliderDistance();
        initSliderPrice();
        initSliderDuration();
    };

    /**
     * Affichage carte
     */
    runMapFilter = () => {
        let _this = this;
        let $modal = $('#modal-address');
        let $modalBoby = $modal.find('.modal-body');
        let inputHidden = document.getElementById("city");
        let inputHiddenCity = document.getElementById("inputCity");
        let inputAddress = document.getElementById("address-google-map");
        let mapElem = document.getElementById('map-filter');
        let service;
        let marker;
        let tabResults = [];

        // Creation d'un marker
        let createMarker = function (result, distance = false) {
            let map = _this.mapFilter;
            marker = new google.maps.Marker({
                position: result.geometry.location,
                map,
                title: result.business_status ? `${result.name} - ${result.formatted_address}` : result.formatted_address,
                draggable: true,
            });
            map.setCenter(result.geometry.location);
            map.setZoom(8);

            _this.markerCircle = new google.maps.Circle({
                strokeColor: "rgb(51, 136, 255)",
                strokeOpacity: 0.5,
                strokeWeight: 1,
                fillColor: "rgb(51, 136, 255)",
                fillOpacity: 0.2,
                map,
                center: result.geometry.location,
                radius: distance ? distance * 1000 : 1000,
            });

            google.maps.event.addListener(marker, 'dragend', function() 
            {
                geocodePosition(marker.getPosition());
            });

            google.maps.event.addListener(marker, 'drag', function() 
            {
                _this.markerCircle.setMap(null);
                _this.markerCircle = new google.maps.Circle({
                    strokeColor: "rgb(51, 136, 255)",
                    strokeOpacity: 0.5,
                    strokeWeight: 1,
                    fillColor: "rgb(51, 136, 255)",
                    fillOpacity: 0.2,
                    map,
                    center: marker.getPosition(),
                    radius: 1000,
                });
            });

            mapElem.classList.add('active');
        };

        function geocodePosition(pos) {
            let map = _this.mapFilter;
            let geocoder = new google.maps.Geocoder();
            geocoder.geocode
            ({
                 latLng: pos
            }, 
                 function(results, status) 
                {
                    if (status == google.maps.GeocoderStatus.OK) 
                    {
                        inputAddress.value = results[0].business_status ? `${results[0].name} - ${results[0].formatted_address}` : results[0].formatted_address;
                        _this.sliderDistance.setValue(0);
                        $("#distanceVal").text("1km");
                        $("#distance").val(1);
                        _this.sliderDistance.enable();

                        let newCoords = {
                            lat: results[0].geometry.location.lat(),
                            lng: results[0].geometry.location.lng(),
                        };
                        newCoords = JSON.stringify(newCoords);

                        if (inputHidden) inputHidden.value = newCoords;
                        if (inputHiddenCity) inputHiddenCity.value = results[0].business_status ? `${results[0].name} - ${results[0].formatted_address}` : results[0].formatted_address;
                    } else {
                         let zeroResultsHTML = `<p>${translationsJS && translationsJS.no_result_found ? translationsJS.no_result_found : 'No results'}</p>`;
                         $modalBoby.html(zeroResultsHTML);
                         $modal.modal('show');
                    }
                }
            );
        }

        function initialize() {
            var latlng = new google.maps.LatLng(0, 0);
            var mapOptions = {
                zoom: 1,
                center: latlng,
                scaleControl: false,
                mapTypeControl: false,
                navigationControl: false,
                streetViewControl: false,
                fullscreenControl: false,
            }
            
            _this.mapFilter = new google.maps.Map(mapElem, mapOptions);
            if (inputHiddenCity) inputAddress.value = inputHiddenCity.value;

            // Affichage du marker en edition
            if (inputHidden && inputHidden.value != ''
                && inputHiddenCity && inputHiddenCity.value != '') {
                    let coords = JSON.parse(inputHidden.value);
                    let result = {};
                    result.geometry = {};
                    result.geometry.location = {lat: coords.lat, lng: coords.lng};
                    result.name = inputHiddenCity.value;
                    let distance = $("#distance").val();
                    createMarker(result, distance);

                    if (inputAddress) inputAddress.value = result.name;
                    $('#btn-geocode').prop('disabled', false);
            }
        }
        initialize(); 

        $("body").on("click", "#btn-geocode-filter", function (e) {
                let map = _this.mapFilter;

                $(this).prop('disabled', true);
                $(this).find('.spinner-border').toggleClass('inactive active');
                $(this).find('.fa-search').toggleClass('inactive active');

                let searchValue = $(this).val();
                if (searchValue.length == 0) {
                    var address = document.getElementById('address-google-map').value;
                    const request = {
                        query: address,
                        fields: ["name", "geometry"]
                    };
                    service = new google.maps.places.PlacesService(map);
                    service.textSearch(request, (results, status) => {                    
                        
                        if (status === google.maps.places.PlacesServiceStatus.OK && results) {                      
                            // Plusieurs resultats
                            if (results.length > 1) {
                                let listLocationHTML = '<ul>'
                                for (let i = 0; i < results.length; i++) {
                                    tabResults[i] = results[i];
                                    let titleHTML = results[i].business_status ? `<p>${results[i].name}</p><p>${results[i].formatted_address}</p>` : `<p>${results[i].formatted_address}</p>`;
                                    listLocationHTML += `<li data-id="${i}">${titleHTML}</li>`;
        
                                }
                                listLocationHTML += '</ul>'
                                $modalBoby.html(listLocationHTML);
                                $modal.modal('show');
                            }
                            // 1 seul resultat
                            if (results.length == 1) {
                                if (marker) marker.setMap(null); // Suppression marker
                                if (_this.markerCircle) _this.markerCircle.setMap(null); // Suppression circle
                                createMarker(results[0]);
                                inputAddress.value = results[0].business_status ? `${results[0].name} - ${results[0].formatted_address}` : results[0].formatted_address;
                                _this.sliderDistance.setValue(0);
                                $("#distanceVal").text("1km");
                                $("#distance").val(1);
                                _this.sliderDistance.enable();

                                let newCoords = {
                                    lat: results[0].geometry.location.lat(),
                                    lng: results[0].geometry.location.lng(),
                                };
                                newCoords = JSON.stringify(newCoords);

                                if (inputHidden) inputHidden.value = newCoords;
                                if (inputHiddenCity) inputHiddenCity.value = results[0].business_status ? `${results[0].name} - ${results[0].formatted_address}` : results[0].formatted_address;
                            }
                        } else {
                            // Pas de resultats
                            if (marker) marker.setMap(null); // Suppression marker
                            if (_this.markerCircle) _this.markerCircle.setMap(null); // Suppression circle
                            let zeroResultsHTML = `<p>${translationsJS && translationsJS.no_result_found ? translationsJS.no_result_found : 'No results'}</p>`;
                            $modalBoby.html(zeroResultsHTML);
                            $modal.modal('show');
                            _this.sliderDistance.setValue(1);
                            $("#distanceVal").text("1km");
                            $("#distance").val(1);
                            _this.sliderDistance.disable();
                        }

                        $(this).find('.spinner-border').toggleClass('inactive active');
                        $(this).find('.fa-search').toggleClass('inactive active');
                        setTimeout(function() {
                            $('#btn-geocode-filter').prop('disabled', false);
                        }, 200);
                    })
                }
            }
        );

        $('body').on('keydown', '#address-google-map', function(e) {
            if(e.key === 'Enter') {
                e.preventDefault();
                $(this).closest('.input-group').find('button').trigger('click');      
            }
        });

        $('body').on('input', '#address-google-map', function(e) {
            if($(this).val().length == 0) {
                $(this).closest('.input-group').find('button').prop('disabled', true);
                _this.sliderDistance.setValue(1);
                $("#distanceVal").text("1km");
                $("#distance").val(1);
                _this.sliderDistance.disable();
                inputHidden.value = '';
                inputHiddenCity.value = '';
                mapElem.classList.remove('active');
            } else {
                $(this).closest('.input-group').find('button').prop('disabled', false);   
            }
        });

        // Selection d'une adresse via la modal
        $('body').on('click', '#modal-address li', function() {
            if (marker) marker.setMap(null); // Suppression marker
            if (_this.markerCircle) _this.markerCircle.setMap(null); // Suppression circle
            var id = $(this).attr('data-id');
            inputAddress.value = tabResults[id].business_status ? `${tabResults[id].name} - ${tabResults[id].formatted_address}` : tabResults[id].formatted_address;
            $modal.modal('hide');
            createMarker(tabResults[id]);
            _this.sliderDistance.setValue(1);
            $("#distanceVal").text("1km");
            $("#distance").val(1);
            _this.sliderDistance.enable();

            let newCoords = {
                lat: tabResults[id].geometry.location.lat(),
                lng: tabResults[id].geometry.location.lng(),
            };
            newCoords = JSON.stringify(newCoords);

            if (inputHidden) inputHidden.value = newCoords;
            if (inputHiddenCity) inputHiddenCity.value = tabResults[id].business_status ? `${tabResults[id].name} - ${tabResults[id].formatted_address}` : tabResults[id].formatted_address;
        });
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
            order: [[3, "desc"]],
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
