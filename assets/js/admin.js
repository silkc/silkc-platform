/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import '../scss/admin.scss';

import $ from 'jquery';

require('bootstrap'); 
require('datatables.net');
require('@fortawesome/fontawesome-free/js/all.min')
require('tinymce/tinymce.min.js');
var tinymce = require('tinymce/tinymce');
require('tinymce/themes/silver');
require('tinymce/plugins/paste');
require('tinymce/plugins/code');
require('tinymce/plugins/link');
require('bootstrap-select');

$(document).ready(function() {

});