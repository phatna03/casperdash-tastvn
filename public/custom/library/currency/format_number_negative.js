(function ($) {
    $.fn.sys_format_number_negative = function () { //with negative (-)
        this.each(function (index, el) {
            var elType = null; // input or other
            var value = null;
            // get value
            if ($(el).is('input') || $(el).is('textarea')) {
                value = $(el).val().replace(/\,/g, '');
                elType = 'input';
            } else {
                value = $(el).text().replace(/\,/g, '');
                elType = 'other';
            }
            // if value changes
            $(el).on('paste keyup', function (event) {
                value = $(el).val().replace(/\,/g, '');
                formatElement(el, elType, value); // format element
            });
            formatElement(el, elType, value); // format element
        });

        function formatElement(el, elType, value) {
            //custom
            value = value.replace(/\s/g, '');
            value = value.replace(/\n/g, '');

            //negative
            var minus = false;
            if (value.indexOf('-') === 0) {
                minus = true;
                value = value.substring(1);

                value = value.replace('-', '');
            }

            var result = '';
            var valueArray = value.split('');
            var resultArray = [];
            var counter = 0;
            var temp = '';
            for (var i = valueArray.length - 1; i >= 0; i--) {
                temp += valueArray[i];
                counter++
                if (counter == 3) {
                    resultArray.push(temp);
                    counter = 0;
                    temp = '';
                }
            }
            ;
            if (counter > 0) {
                resultArray.push(temp);
            }
            for (var i = resultArray.length - 1; i >= 0; i--) {
                var resTemp = resultArray[i].split('');
                for (var j = resTemp.length - 1; j >= 0; j--) {
                    result += resTemp[j];
                }
                ;
                if (i > 0) {
                    result += ',';
                }
            }
            ;

            //negative
            if (minus) {
                result = '-' + result;
            }

            if (elType == 'input') {
                $(el).val(result);
            } else {
                $(el).empty().text(result);
            }

        }
    };
}(jQuery));
