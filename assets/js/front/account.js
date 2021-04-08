import $ from 'jquery';
import 'autocomplete.js/dist/autocomplete.jquery';
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
    
    runAutocompletion = () => {
        a1 = $('#query').autocomplete({
            width: 448,
            delimiter: /(,|;)\s*/,
            lookup: 'Andorra,Azerbaijan,Bahamas,Bahrain,Benin,Bhutan,Bolivia,Bosnia Herzegovina,Botswana,Brazil,Brunei,Bulgaria,Burkina, Burundi,Cambodia,Cameroon,Canada,Cape Verde,Central African Rep,Chile,China,Colombia,Comoros,Congo,Congo {Democratic Rep},Costa Rica,Croatia,Cuba,Cyprus,Czech Republic,Denmark,Djibouti,East Timor,Ecuador,Egypt,El Salvador,Equatorial Guinea,Eritrea,Fiji,France,Georgia,Germany,Ghana,Greece,Grenada,Guatemala,Guinea,Guinea-Bissau,Guyana,Haiti,Honduras,Hungary,India,Iraq,Ireland {Republic},Ivory Coast,Jamaica,Japan,Kazakhstan,Kiribati,Korea North,'.split(',')
         }); 
    }

    init = function() {
        this.runAutocompletion();
    }
}

$(document).ready(function() {
    let account = new Account();
    account.init();
});
