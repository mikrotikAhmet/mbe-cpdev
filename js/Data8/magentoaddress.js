if (typeof (data8) == 'undefined') {
    data8 = function () { };
}

// Array Remove - By John Resig (MIT Licensed)
Array.prototype.remove = function(from, to) {
  var rest = this.slice((to || from) + 1 || this.length);
  this.length = from < 0 ? this.length + from : from;
  return this.push.apply(this, rest);
};

data8.magentoPostcodeLookupButton = function (fields, options) {
	// Remove any missing fields.
	for (var i = fields.length - 1; i >= 0; i--) {
		if (document.getElementById(fields[i].element) == null) {
			fields.remove(i);
		}
	}
	
    data8.postcodeLookupButton.apply(this, arguments);
};

data8.magentoPostcodeLookupButton.prototype = new data8.postcodeLookupButton();

data8.magentoPostcodeLookupButton.prototype.createButton = function (label) {
    var button = document.createElement('button');
    button.setAttribute('type', 'button');
    button.className = 'button';
    button.style.marginLeft = '10px';
    var span1 = document.createElement('span');
    button.appendChild(span1);
    var span2 = document.createElement('span');
    span1.appendChild(span2);
    span2.innerHTML = label;

    return button;
};

data8.magentoPostcodeLookupButton.prototype.insertButton = function () {
    data8.postcodeLookupButton.prototype.insertButton.call(this);

    // Find the postcode lookup field and reduce it's size so the button and the textbox
    // all fit on one line.
    for (var i = 0; i < this.fields.length; i++) {
        if (this.fields[i].field == 'postcode') {
            var postcodeElement = document.getElementById(this.fields[i].element);
            var textboxDimensions = jQuery(postcodeElement).getHiddenDimensions(true);
            var buttonDimensions = jQuery(this.button).getHiddenDimensions(true);
            postcodeElement.style.width = textboxDimensions.outerWidth - buttonDimensions.outerWidth - 10 + 'px';
            break;
        }
    }
};

data8.magentoPostcodeLookupButton.prototype.show = function () {
    if (!this.valid)
        return;

    data8.postcodeLookupButton.prototype.show.call(this);

    for (var i = 0; i < this.fields.length; i++) {
        if (this.fields[i].field == 'country') {
            var countryElement = document.getElementById(this.fields[i].element);
            var countryDropDown = jQuery(countryElement);
            var button = jQuery(this.button);

            countryDropDown.change(function (e) {
                if (countryDropDown.val() == 'GB')
                    button.show();
                else
                    button.hide();
            });

            if (countryDropDown.val() != 'GB')
                button.hide();

            // Check if we can move the postcode and country fields above the rest of the address.
            if (countryDropDown.get(0).parentNode &&
                countryDropDown.get(0).parentNode.parentNode &&
                countryDropDown.get(0).parentNode.parentNode.parentNode &&
                this.button.parentNode &&
                this.button.parentNode.parentNode &&
                this.button.parentNode.parentNode.parentNode &&
                countryDropDown.get(0).parentNode.parentNode.parentNode == this.button.parentNode.parentNode.parentNode && // both on same row
                this.button.parentNode.parentNode.parentNode.previousSibling &&
                this.button.parentNode.parentNode.parentNode.previousSibling.previousSibling &&
                this.button.parentNode.parentNode.parentNode.previousSibling.previousSibling.previousSibling &&
                this.button.parentNode.parentNode.parentNode.previousSibling.previousSibling.previousSibling.childNodes.length > 2 &&
                this.button.parentNode.parentNode.parentNode.previousSibling.previousSibling.previousSibling.childNodes[2].childNodes.length > 0 &&
                this.button.parentNode.parentNode.parentNode.previousSibling.previousSibling.previousSibling.childNodes[2].childNodes[0].id &&
                this.button.parentNode.parentNode.parentNode.previousSibling.previousSibling.previousSibling.childNodes[2].childNodes[0].id.indexOf('street') != -1) { // 3 rows after street 1
                // Move the row up
                var postcodeCountryRow = this.button.parentNode.parentNode.parentNode;
                var street1Row = postcodeCountryRow.previousSibling.previousSibling.previousSibling;
                postcodeCountryRow.parentNode.removeChild(postcodeCountryRow);
                jQuery(postcodeCountryRow).insertBefore(street1Row);

                // Swap the fields around in the row.
                var postcodeField = this.button.parentNode.parentNode;
                postcodeCountryRow.removeChild(postcodeField);
                postcodeCountryRow.appendChild(postcodeField);
            }
            else if (countryDropDown.get(0).parentNode &&
                countryDropDown.get(0).parentNode.parentNode &&
                countryDropDown.get(0).parentNode.parentNode.parentNode &&
                this.button.parentNode &&
                this.button.parentNode.parentNode &&
                this.button.parentNode.parentNode.parentNode &&
                countryDropDown.get(0).parentNode.parentNode.parentNode == this.button.parentNode.parentNode.parentNode && // both on same row
                this.button.parentNode.parentNode.parentNode.previousSibling &&
                this.button.parentNode.parentNode.parentNode.previousSibling.previousSibling &&
                this.button.parentNode.parentNode.parentNode.previousSibling.previousSibling.previousSibling &&
                this.button.parentNode.parentNode.parentNode.previousSibling.previousSibling.previousSibling.previousSibling &&
                this.button.parentNode.parentNode.parentNode.previousSibling.previousSibling.previousSibling.previousSibling.previousSibling &&
                this.button.parentNode.parentNode.parentNode.previousSibling.previousSibling.previousSibling.previousSibling.previousSibling.previousSibling &&
                this.button.parentNode.parentNode.parentNode.previousSibling.previousSibling.previousSibling.previousSibling.previousSibling.previousSibling.childNodes.length > 3 &&
                this.button.parentNode.parentNode.parentNode.previousSibling.previousSibling.previousSibling.previousSibling.previousSibling.previousSibling.childNodes[3].childNodes.length > 1 &&
                this.button.parentNode.parentNode.parentNode.previousSibling.previousSibling.previousSibling.previousSibling.previousSibling.previousSibling.childNodes[3].childNodes[1].id &&
                this.button.parentNode.parentNode.parentNode.previousSibling.previousSibling.previousSibling.previousSibling.previousSibling.previousSibling.childNodes[3].childNodes[1].id.indexOf('street') != -1) { // 3 rows after street 1
                // Move the row up
                var postcodeCountryRow = this.button.parentNode.parentNode.parentNode;
                var street1Row = postcodeCountryRow.previousSibling.previousSibling.previousSibling.previousSibling.previousSibling.previousSibling;
                postcodeCountryRow.parentNode.removeChild(postcodeCountryRow);
                jQuery(postcodeCountryRow).insertBefore(street1Row);

                // Swap the fields around in the row.
                var postcodeField = this.button.parentNode.parentNode;
                postcodeCountryRow.removeChild(postcodeField);
                postcodeCountryRow.appendChild(postcodeField);
            }

            break;
        }
    }
};

(function ($) {
    $.fn.getHiddenDimensions = function (includeMargin) {
        var $item = this,
        props = { position: 'absolute', visibility: 'hidden', display: 'block' },
        dim = { width: 0, height: 0, innerWidth: 0, innerHeight: 0, outerWidth: 0, outerHeight: 0 },
        $hiddenParents = $item.parents().andSelf().not(':visible'),
        includeMargin = (includeMargin == null) ? false : includeMargin;

        var oldProps = [];
        $hiddenParents.each(function () {
            var old = {};
            for (var name in props) {
                old[name] = this.style[name];
                this.style[name] = props[name];
            }

            oldProps.push(old);
        });

        dim.width = $item.width();
        dim.outerWidth = $item.outerWidth(includeMargin);
        dim.innerWidth = $item.innerWidth();
        dim.height = $item.height();
        dim.innerHeight = $item.innerHeight();
        dim.outerHeight = $item.outerHeight(includeMargin);

        $hiddenParents.each(function (i) {
            var old = oldProps[i];
            for (var name in props) {
                this.style[name] = old[name];
            }
        });

        return dim;
    }
} (jQuery));