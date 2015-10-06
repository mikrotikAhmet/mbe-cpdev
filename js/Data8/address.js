if (typeof (data8) == 'undefined') {
    data8 = function () { };
}

data8.postcodeLookupButton = function (fields, options) {
    this.fields = fields;
    this.options = options;
    this.validate();
};

data8.postcodeLookupButton.prototype.getElement = function(id) {
	var element = document.getElementById(id);
	
	if (!element) {
		var elements = document.getElementsByName(id);
		
		if (elements && elements.length)
			element = elements[elements.length - 1];
	}
	
	return element;
};

data8.postcodeLookupButton.prototype.validate = function () {
    this.valid = true;

    if (!this.fields) {
        this.valid = false;
        return;
    }

    if (!this.options) {
        this.valid = false;
        return;
    }

    // Check a valid license type is selected.
    if (!this.options.license) {
        this.valid = false;
        return;
    }

    // Check all the fields exist.
    for (var i = 0; i < this.fields.length; i++) {
        if (!this.getElement(this.fields[i].element)) {
			this.valid = false;
			return;
        }
    }

    // Check all the fields are for a valid address field.
    for (var i = 0; i < this.fields.length; i++) {
        if (this.fields[i].field != 'organisation' &&
            this.fields[i].field != 'line1' &&
            this.fields[i].field != 'line2' &&
            this.fields[i].field != 'line3' &&
            this.fields[i].field != 'line4' &&
            this.fields[i].field != 'line5' &&
            this.fields[i].field != 'line6' &&
            this.fields[i].field != 'town' &&
            this.fields[i].field != 'county' &&
            this.fields[i].field != 'postcode' &&
            this.fields[i].field != 'country') {
            this.valid = false;
            return;
        }
    }

    // Check at least a postcode and line1 fields are specified.
    var hasPostcode = false;
    var hasLine1 = false;

    for (var i = 0; i < this.fields.length && (!hasPostcode || !hasLine1); i++) {
        if (this.fields[i].field == 'line1')
            hasLine1 = true;
        else if (this.fields[i].field == 'postcode')
            hasPostcode = true;
    }

    if (!hasPostcode || !hasLine1) {
        this.valid = false;
        return;
    }
	
	if (!this.options.findLabel)
		this.options.findLabel = 'Find';
	
	if (!this.options.okLabel)
		this.options.okLabel = 'OK';
	
	if (!this.options.cancelLabel)
		this.options.cancelLabel = 'Cancel';
};

data8.postcodeLookupButton.prototype.selectAddress = function (address) {
    for (var i = 0; i < this.fields.length; i++) {
        var target = this.getElement(this.fields[i].element);
        var value;
        if (this.fields[i].field == 'organisation')
            value = this.toProperCase(address.RawAddress.Organisation);
        else if (this.fields[i].field == 'line1')
            value = address.Address.Lines[0];
        else if (this.fields[i].field == 'line2')
            value = address.Address.Lines[1];
        else if (this.fields[i].field == 'line3')
            value = address.Address.Lines[2];
        else if (this.fields[i].field == 'line4')
            value = address.Address.Lines[3];
        else if (this.fields[i].field == 'line5')
            value = address.Address.Lines[4];
        else if (this.fields[i].field == 'line6')
            value = address.Address.Lines[5];
        else if (this.fields[i].field == 'town')
            value = address.Address.Lines[address.Address.Lines.length - 3];
        else if (this.fields[i].field == 'county')
            value = address.Address.Lines[address.Address.Lines.length - 2];
        else if (this.fields[i].field == 'postcode')
            value = address.Address.Lines[address.Address.Lines.length - 1];
        else
            value = target.value;
        target.value = value;
    }
};

data8.postcodeLookupButton.prototype.createButton = function (label) {
    var button = document.createElement('input');
    button.type = 'button';
    button.value = label;
    return button;
};

data8.postcodeLookupButton.prototype.insertButton = function () {
    // Find the postcode lookup field and add the button next to it.
    for (var i = 0; i < this.fields.length; i++) {
        if (this.fields[i].field == 'postcode') {
            var postcodeElement = this.getElement(this.fields[i].element);
            jQuery(this.button).insertAfter(postcodeElement);
            break;
        }
    }
};

data8.postcodeLookupButton.prototype.getLineCount = function () {
    if (typeof (this.lineCount) == 'undefined') {
        this.lineCount = 0;

        for (var i = 0; i < this.fields.length; i++) {
            if (this.fields[i].field.indexOf('line') == 0)
                this.lineCount++;
            else if (this.fields[i].field == 'town')
                this.lineCount++;
            else if (this.fields[i].field == 'county')
                this.lineCount++;
        }
    }

    return this.lineCount;
};

data8.postcodeLookupButton.prototype.usesFixedTownCounty = function () {
    if (typeof (this.fixedTownCounty) == 'undefined') {
        this.fixedTownCounty = false;

        for (var i = 0; i < this.fields.length; i++) {
            if (this.fields[i].field == 'town' || this.fields[i].field == 'county') {
                this.fixedTownCounty = true;
                break;
            }
        }
    }

    return this.fixedTownCounty;
};

data8.postcodeLookupButton.prototype.usesOrganisation = function () {
    if (typeof (this.organisation) == 'undefined') {
        this.organisation = false;

        for (var i = 0; i < this.fields.length; i++) {
            if (this.fields[i].field == 'organisation') {
                this.organisation = true;
                break;
            }
        }
    }

    return this.organisation;
};

data8.postcodeLookupButton.prototype.showAddressList = function (addresses) {
    // Clear any existing addresses.
    while (this.list.options.length > 0)
        this.list.options[this.list.options.length - 1] = null;

    // Add the addresses to the list.
    for (var i = 0; i < addresses.length; i++) {
        var option = document.createElement('option');
        option.text = this.getAddressText(addresses[i]);
        option.address = addresses[i];

        try {
            this.list.add(option, this.list.options[null]);
        }
        catch (e) {
            this.list.add(option, null);
        }
    }
	
	this.list.multiple = false;
	this.list.selectedIndex = 0;

    // Save the function to apply the selected address.
    var pcl = this;
    this.list.applySelectedAddress = function () {
        var address = addresses[pcl.list.selectedIndex];
        pcl.selectAddress(address);
    };

    // Position the drop down.
    var postcodeElement;

    for (var i = 0; i < this.fields.length; i++) {
        if (this.fields[i].field == 'postcode') {
            postcodeElement = this.getElement(this.fields[i].element);
            break;
        }
    }

    var position = jQuery(postcodeElement).offset();
    var height = jQuery(postcodeElement).height();
    var width = jQuery(postcodeElement).width();
    var container = jQuery(this.dropdown);
    this.list.style.minWidth = width + 'px';
    container.css('left', position.left + 'px');
    container.css('top', (position.top + height + 4) + 'px');
    container.show('fast');
    this.list.focus();
};

data8.postcodeLookupButton.prototype.show = function () {
    if (!this.valid)
        return;

    this.shown = true;

    if (this.button) {
        this.button.style.display = this.buttonDisplay;
        return;
    }

    this.createUI();

    for (var i = 0; i < this.fields.length; i++) {
        if (this.fields[i].field == 'postcode') {
            // Create the button element.
            var postcodeElement = this.getElement(this.fields[i].element);
            this.button = this.createButton(this.options.findLabel);

            this.insertButton();
            this.buttonDisplay = this.button.style.display;

            // Do a postcode lookup when the button is clicked.
            var pcl = this;
            jQuery(this.button).click(function (e) {
                var proxy = new data8.addresscapture();
                var formattingOptions = [
                        { name: 'MaxLines', value: pcl.getLineCount() },
                        { name: 'FixTownCounty', value: pcl.usesFixedTownCounty() },
                        { name: 'Formatter', value: pcl.usesOrganisation() ? 'NoOrganisationFormatter' : 'DefaultFormatter' }
                    ];
                proxy.getfulladdress(pcl.options.license, postcodeElement.value, '', formattingOptions, function (result) {
                    if (!result.Status.Success) {
                        alert(result.Status.ErrorMessage);
                    }
                    else if (result.Results.length == 0) {
                        alert('Postcode not recognised');
                    }
                    else {
                        if (result.Results.length == 1) {
                            pcl.selectAddress(result.Results[0]);
                        }
                        else {
                            pcl.showAddressList(result.Results);
                        }
                    }
                });
            });
        }
        else if (this.fields[i].field == 'country' && this.options.expectedCountry) {
            var countryElement = jQuery(this.getElement(this.fields[i].element));
            var country = countryElement.val();
            if (country != this.options.expectedCountry)
                this.hide(true);

            var pcl = this;
            countryElement.change(function (e) {
                if (countryElement.val() != pcl.options.expectedCountry)
                    pcl.hide(true);
                else if (pcl.shown)
                    pcl.show();
            });
        }
    }
};

data8.postcodeLookupButton.prototype.hide = function (shown) {
    if (this.button) {
        this.button.style.display = 'none';
        this.shown = shown;
    }
};

data8.postcodeLookupButton.prototype.createUI = function() {
	this.dropdown = document.createElement('div');
	this.dropdown.style.position = 'absolute';
	this.dropdown.style.display = 'none';
	this.dropdown.style.backgroundColor = '#FFFFFF';
	this.dropdown.style.padding = '1px';
	
	document.body.appendChild(this.dropdown);
	
	this.list = document.createElement('select');
	this.list.size = 10;
	this.dropdown.appendChild(this.list);
	
	var buttons = document.createElement('div');
	buttons.style.textAlign = 'right';
	buttons.style.marginTop = '1em';
	this.dropdown.appendChild(buttons);
	
	var ok = this.createButton(this.options.okLabel);
	buttons.appendChild(ok);
	
	var cancel = this.createButton(this.options.cancelLabel);
	buttons.appendChild(cancel);
  
	var ignoreBlur = false;
	var pcl = this;
	
    jQuery(this.list).blur(function(e) {
		data8PostcodeLookupListOnBlur = function () {
			if (ignoreBlur) {
				ignoreBlur = false;
				jQuery(pcl.list).focus();
			}
			else if (document.activeElement == null || document.activeElement != ok) {
				jQuery(pcl.dropdown).hide('fast');
			}
		};
		
		setTimeout('data8PostcodeLookupListOnBlur()', 100);
    })
    .dblclick(function(e) {
      jQuery(ok).click()
    })
	.click(function(e) {
	  var ua = navigator.userAgent;
	  if (/iPad/i.test(ua) || /iPhone/i.test(ua))
	    ignoreBlur = true;
	})
    .change(function (e) {
        if (pcl.mobileCheck())
            jQuery(ok).click();
    });
  
    jQuery(ok).click(function(e) {
      pcl.list.applySelectedAddress();
      jQuery(pcl.dropdown).hide('fast');
    });
};

data8.postcodeLookupButton.prototype.mobileCheck = function () {
    var check = false;
    (function (a) { if (/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(a) || /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0, 4))) check = true })(navigator.userAgent || navigator.vendor || window.opera);
    return check;
}

data8.postcodeLookupButton.prototype.getAddressText = function (address) {
    var text = '';

    if (this.usesOrganisation() && address.RawAddress.Organisation)
        text = this.toProperCase(address.RawAddress.Organisation);

    for (var i = 0; i < address.Address.Lines.length; i++) {
        if (address.Address.Lines[i]) {
            if (text)
                text = text + ', ';
            text = text + address.Address.Lines[i];
        }
    }

    return text;
};

data8.postcodeLookupButton.prototype.toProperCase = function (s) {
    return s.toLowerCase().replace(/^(.)|\s(.)/g,
          function ($1) { return $1.toUpperCase(); });
};