import $ from "jquery";
require("bootstrap-datepicker");
require("bootstrap");

// any CSS you import will output into a single css file (app.css in this case)
import "../scss/login.scss";
import "../scss/login.scss";

//require('popper');
var moment = require("moment");
require("chart.js");
require("@fortawesome/fontawesome-free/js/all.min");

class Login {
    instanceProperty = "Login";
    boundFunction = () => {
        return this.instanceProperty;
    };

    /**
     * Carousel
     */
    runCarousel = () => {
        let idxImg = 0;
        let idxSlogans = 0;

        let images = $("body").find("#carousel img");
        let imagesLength = images.length;

        let slogans = $("body").find("#carousel .slogans > div");
        let slogansLength = slogans.length;
        setInterval(function () {
            idxImg++;
            idxSlogans++;
            if (idxImg == imagesLength) idxImg = 0;
            if (idxSlogans == slogansLength) idxSlogans = 0;
            images.hide().eq(idxImg).show();
            slogans.hide().eq(idxSlogans).show();
        }, 10000);
    };

    /**
     * Affichage carte
     */
    runMap = () => {
        let inputUserHidden = document.getElementById("user_address");
        let inputInstitutionHidden = document.getElementById(
            "institution_address"
        );
        let inputRecruiterHidden = document.getElementById("recruiter_address");

        let inputHidden = inputUserHidden
            ? inputUserHidden
            : inputInstitutionHidden
            ? inputInstitutionHidden
            : inputRecruiterHidden;
        var map = null;

        /*$('body').on('click', '#map.not-allowed', function() {
            return false;
        })*/

        if (inputHidden) {
            let coords = inputHidden.value;
            
            let optsMap = {
                dragging: false,
                doubleClickZoom: false,
                scrollWheelZoom: false,
            }

            if (coords) {


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
                    map = L.map("map", optsMap).setView([coords.lat, coords.lng], 10);
                } else {
                    map = L.map("map", optsMap).setView([0, 0], 2);
                }
            } else {
                map = L.map("map", optsMap).setView([0, 0], 2);
            }

            let geocoder = L.Control.Geocoder.nominatim();
            let control = L.Control.geocoder({
                collapsed: false,
                placeholder:
                    window.location.href.indexOf("institution") != -1
                        ? translationsJS && translationsJS.address
                            ? translationsJS.address
                            : "Address"
                        : translationsJS && translationsJS.city
                        ? translationsJS.city
                        : "City",
                position: "topleft",
                geocoder: geocoder,
            })
                .on("markgeocode", function (e) {
                    if (e.geocode && e.geocode.center) {
                        let lat = e.geocode.center.lat;
                        let lng = e.geocode.center.lng;
                        let name = e.geocode.name;

                        let newCoords = {
                            city: name,
                            lat: lat,
                            lng: lng,
                        };
                        newCoords = JSON.stringify(newCoords);

                        let leafletControlGeocoderForm = document.querySelector(
                            ".leaflet-control-geocoder-form input"
                        );
                        leafletControlGeocoderForm.value = name;
                        inputHidden.value = newCoords;

                        $("#map").removeClass("not-allowed");

                        map.dragging.enable();
                        map.doubleClickZoom.enable();
                        map.scrollWheelZoom.enable();
                    }
                })
                .addTo(map);

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

            document
                .getElementById("searchmap")
                .appendChild(
                    document.querySelector(
                        ".leaflet-control-geocoder.leaflet-bar"
                    )
                );

            let $buttonSearch = $("button.leaflet-control-geocoder-icon");
            let $inputSearch = $(".leaflet-control-geocoder.leaflet-bar input");
            let timeout = false;
            $inputSearch.on("keyup", function () {
                if (timeout) clearTimeout(timeout);
                timeout = setTimeout(function () {
                    $buttonSearch.trigger("click");
                }, 1000);
            });

            if (coords) {
                let marker = L.marker([coords.lat, coords.lng]).addTo(map); // Markeur
                marker.bindPopup(coords.city); // Bulle d'info

                let leafletControlGeocoderForm = document.querySelector(
                    ".leaflet-control-geocoder-form input"
                );
                leafletControlGeocoderForm.value = coords.city;
            }
        }
    };

    /**
     * Ajoute la class required-input sur la div parent des champs obligatoires
     */
    displayInputRequired = () => {
        $("body.signup-form form input[required]").each(function () {
            let placeholder = $(this).attr("placeholder");
            placeholder = placeholder + " *";
            $(this).attr("placeholder", placeholder);
        });
    };

    init = function () {
        this.displayInputRequired();
        this.runCarousel();
        this.runMap();
    };
}

$(document).ready(function () {
    let login = new Login();
    login.init();
});
