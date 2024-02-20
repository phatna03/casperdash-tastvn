(function ($) {
    $.fn.sys_format_money_negative = function () { //with negative (-)
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

            //thap phan = 2
            var dot = false;
            var arr = value.split('.');
            var thapPhan = arr.length && arr.length === 2 ? arr[1].substring(0, 2) : 0;

            // console.log(arr);
            // console.log(thapPhan);

            if (arr.length && arr.length === 2) {
                if (parseInt(thapPhan) > 0) {

                    dot = true;
                    value = arr[0];

                } else if (arr[1].length) {

                    value = arr[0];

                    if (arr[1] === '' || arr[1] === '0') {

                        if (elType == 'input') {
                            if (arr[1] === '0') {
                                // value = value + '.0';
                                value = value + '.';

                                thapPhan = '0';

                                dot = true;

                            } else {
                                value = value + '.';

                                //negative
                                if (minus) {
                                    value = '-' + value;
                                }

                                $(el).val(value);
                                return;
                            }
                        }
                    }

                } else {

                    dot = true;
                }

            } else if (arr.length && arr.length > 2) {
                //prevent dot 2
                var valueDot = arr[0] + '.' + arr[1];

                arr = valueDot.split('.');
                thapPhan = arr[1];

                value = arr[0];

                if (parseInt(thapPhan) > 0) {

                    dot = true;
                    value = arr[0];

                } else if (arr[1].length) {

                    value = arr[0];

                    if (arr[1] === '' || arr[1] === '0') {

                        if (elType == 'input') {
                            if (arr[1] === '0') {
                                // value = value + '.0';
                                value = value + '.';

                                thapPhan = '0';

                                dot = true;

                            } else {
                                value = value + '.';

                                //negative
                                if (minus) {
                                    value = '-' + value;
                                }

                                $(el).val(value);
                                return;
                            }

                        }
                    }

                } else {

                    dot = true;
                }
            }

            if (dot) {
                value = value.replace(/\./g, '');
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
                    result += ','
                }
            }
            ;

            if (dot) {
                result += '.' + thapPhan;
            }

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
