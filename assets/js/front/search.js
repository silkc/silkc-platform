//import $ from 'jquery';
const $ = require("jquery");

//import '../../css/bootstrap-extended.css';

// any CSS you import will output into a single css file (app.css in this case)
import "../../scss/elements/header.scss";
import "../../scss/search.scss";

require("bootstrap");
//require('popper');
var moment = require("moment");
require("chart.js");
require("@fortawesome/fontawesome-free/js/all.min");

class Search {
    instanceProperty = "Search";
    boundFunction = () => {
        return this.instanceProperty;
    };

    initSelectTypeSearch = () => {
        let checkbox = $('.checkbox-search input[name="search-by"]');
        let blcSearch = $(".blc-search-by");
        checkbox.on("change", function () {
            let typeSearch = this.value;
            if (typeSearch) {
                blcSearch.hide();
                $(typeSearch).show();
            }
        });

        window.addEventListener("resize", function () {
            let widthWindow = $(window).outerWidth();
            if (widthWindow > 767) {
                blcSearch.show();
                $(
                    '.checkbox-search input[name="search-by"][value="#blc-search-by-occupation"]'
                ).prop("checked", true);
            }
            if (widthWindow < 768) {
                $("#blc-search-by-occupation").show();
                $("#blc-search-by-skill").hide();
                $(
                    '.checkbox-search input[name="search-by"][value="#blc-search-by-occupation"]'
                ).prop("checked", true);
            }
        });
    };

    carouselSearch = () => {
        // Create some global vars
        var slider = $("#loop-slider"),
            imgGroup = $("#img-group"),
            img = $("#img-group li"),
            imgCount = img.length,
            firstImg = img.first(),
            lastImg = img.last();

        // If autoplay data-attr is set, create the var
        let autoplay;
        if (slider.data("autoplay")) {
            autoplay = slider.data("autoplay");
        } else {
            autoplay = false;
        }
        // Repeat for the autoplay speed
        let gallerySpeed;
        if (slider.data("speed")) {
            gallerySpeed = slider.data("speed");
        } else {
            gallerySpeed = 8000;
        }

        // Set width of slideshow
        imgGroup.css("width", (imgCount + 2) * 100 + "vw");

        // Clone the first & last images - This creates the looping/infinite effect for forward and backward controls
        (function cloneImages() {
            firstImg.clone().addClass("clone").appendTo(imgGroup);
            lastImg.clone().addClass("clone").prependTo(imgGroup);
            // update img array
            img = $("#img-group li");
        })();

        // Create background images from data-attr
        (function makeImages() {
            img.each(function () {
                var $this = $(this),
                    imgUrl = $this.data("img");
                $this.css("background-image", "url(" + imgUrl + ")");
            });
        })();

        // ============================
        // Operations for the Slideshow
        var position = -100, //Starting position
            slidew = 100, //width of slides (in vw)
            duration = 0.3, //slide transition duration
            delay = duration * 1000 + 1, // convert duration into ms, add 1ms delay
            endPosition = imgCount * -100;

        function nextSlide() {
            position = position - slidew;
            imgGroup.css({
                transform: "translateX(" + position + "vw)",
                "transition-duration": +duration + "s",
            });
        }

        function prevSlide() {
            position = position + slidew;
            imgGroup.css({
                transform: "translateX(" + position + "vw)",
                "transition-duration": +duration + "s",
            });
        }

        function rotateSlide() {
            setTimeout(function () {
                imgGroup.css({
                    "transition-duration": "0s",
                    transform: "translateX(" + position + "vw)",
                });
            }, delay);
        }

        // Arrow keys
        $(document).keydown(function (e) {
            switch (e.which) {
                case 39: // right
                    if (position > endPosition + 1) {
                        nextSlide();
                    } else {
                        nextSlide();
                        position = -100;
                        rotateSlide();
                    }
                    break;

                case 37: // left
                    if (position < -101) {
                        prevSlide();
                    } else {
                        prevSlide();
                        position = endPosition;
                        rotateSlide();
                    }
                    break;

                default:
                    return; // exit this handler for other keys
            }
            e.preventDefault(); // prevent the default action (scroll / move caret)
        });

        //========== Automate the slider ==========//

        function rotate() {
            if (position == endPosition) {
                nextSlide();
                position = -100;
                rotateSlide();
            } else {
                nextSlide();
            }
        }
        // Set timer
        // for auto - slider
        if (autoplay == "1") {
            var speed = gallerySpeed,
                timer = setInterval(rotate, speed);

            // Pause slider on mouse - over

            slider.hover(
                function () {
                    clearInterval(timer);
                },
                function () {
                    timer = setInterval(rotate, speed);
                }
            );

            // Add key functionality to slider. Left/Right arrow
            slider.keypress(
                function () {
                    clearInterval(timer);
                },
                function () {
                    timer = setInterval(rotate, speed);
                }
            );
        }
    };

    init = function () {
        this.initSelectTypeSearch();
        this.carouselSearch();
    };
}

$(document).ready(function () {
    var search = new Search();
    search.init();
});
