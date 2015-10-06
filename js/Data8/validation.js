if (typeof (data8) == 'undefined') {
    data8 = function () { };
}

data8.emailValidator = {
    add: function (fields, options) {
        fields.addClass('data8-email-validation');
        fields.blur(function (e) {
            var value = e.target.value;
            e.target.data8_email_validation_result = null;

            if (value) {
                var proxy = new data8.emailvalidation();
                proxy.isvalidsimple(value, options.level, function (result) {
                    if (result.Status.Success)
                        e.target.data8_email_validation_result = result.Result != 'Invalid';
                });
            }
            else {
                e.target.data8_email_validation_result = true;
            }
        });
    }
};

data8.telephoneValidator = {
    add: function (field, countryField, options) {
        var element = document.getElementById(field);
        var countryElement = document.getElementById(countryField);

        jQuery(element)
            .addClass('data8-telephone-validation')
            .blur(function (e) {
                var value = e.target.value;
                var country = countryElement.value;
                e.target.data8_telephone_validation_result = null;

                if (value && country == 'GB') {
                    var proxy = new data8.telephonevalidation();
                    proxy.isvalidsimple(value, function (result) {
                        if (!result.Status.Success)
                            e.target.data8_telephone_validation_result = true;
                        else
                            e.target.data8_telephone_validation_result = result.Result;
                    });
                }
                else if (value && (country == 'US' || country == 'CA')) {
                    var proxy = new data8.ustelephonevalidation();
                    proxy.isvalidsimple(value, function (result) {
                        if (!result.Status.Success)
                            e.target.data8_telephone_validation_result = true;
                        else
                            e.target.data8_telephone_validation_result = result.Result;
                    });
                }
                else {
                    e.target.data8_telephone_validation_result = true;
                }
            });

        jQuery(countryElement).change(function (e) {
            jQuery(element).blur();
        });
    }
}