import $ from 'jquery';

class Common {
    instanceProperty = "Main";
    boundFunction = () => {
        return this.instanceProperty;
    }



    init = function() {
    }
}

$(document).ready(function() {
    let common = new Common();
    common.init();
});
