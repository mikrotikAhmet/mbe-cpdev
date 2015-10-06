function rwAutoSelectOptions(key) {
    var Params = document.URL.toQueryParams();
    var select = $('bundle-option-'+key);
    if (select != null && typeof select != 'undefined') {
        for (var i=0; i<select.options.length; i++) {
            if (select.options[i].value == Params[key]) {
                select.selectedIndex = i;
                if ("createEvent" in document) {
                    var evt = document.createEvent("HTMLEvents");
                    evt.initEvent("change", false, true);
                    select.dispatchEvent(evt);
                }
                else {
                    select.fireEvent("onchange");
                }
            }
        }
    }
};

function rwAutoUpdate(){
//    console.log(this.id);
    var attributeId = this.id.replace(/[a-z-]*/, '');
    rwAutoSelectOptions(attributeId);
};

Event.observe(window, 'load', function() {
    // TODO: implement same for checkbox and radio controls
//    $$('.product-options').each(function(container){
//        var n = 1;
//        $(container).find('select').each(function(element){
//            window.setTimeout(rwAutoUpdate.bind(element), 300*n++);
//        });
//    });
    var n = 1;
    $$('.bundle-option-select').each(function(element){
        window.setTimeout(rwAutoUpdate.bind(element), 300*n++);
    });
//    n = 1;
//    $$('.radio').each(function(element){
//        window.setTimeout(rwAutoUpdate.bind(element), 300*n++);
//    });
//    n = 1;
//    $$('.checkbox').each(function(element){
//        window.setTimeout(rwAutoUpdate.bind(element), 300*n++);
//    });
});