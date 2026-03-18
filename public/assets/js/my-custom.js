$(function () {
    /**
    @Name: String.prototype.ucwords
    @Author: Paul Visco
    @Version: 1.0 11/19/07
    @Description: Converts all first letters of words in a string to uppercase.  Great for titles.
    @Return: String The original string with all first letters of words converted to uppercase.
    @Example:
    var myString = 'hello world';

    var newString = myString.ucwords();
    //newString = 'Hello World'
    */

    String.prototype.ucwords = function () {
        var arr = this.split(" ");

        var str = "";
        arr.forEach(function (v) {
            str += v.charAt(0).toUpperCase() + v.slice(1, v.length) + " ";
        });
        return str;
    };

    Number.prototype.numberFormat = function (decimals, dec_point, thousands_sep) {
        dec_point = typeof dec_point !== 'undefined' ? dec_point : '.';
        thousands_sep = typeof thousands_sep !== 'undefined' ? thousands_sep : ',';

        var parts = this.toFixed(decimals).split('.');
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, thousands_sep);

        return parts.join(dec_point);
    }

});

// set posisi cursor
$.fn.selectRange = function(start, end) {
    if(end === undefined) {
        end = start;
    }
    return this.each(function() {
        if('selectionStart' in this) {
            this.selectionStart = start;
            this.selectionEnd = end;
        } else if(this.setSelectionRange) {
            this.setSelectionRange(start, end);
        } else if(this.createTextRange) {
            var range = this.createTextRange();
            range.collapse(true);
            range.moveEnd('character', end);
            range.moveStart('character', start);
            range.select();
        }
    });
};

var formatNumber = function (num) {
    var array = num.toString().split('');
    var index = -3;
    while (array.length + index > 0) {
        array.splice(index, 0, '.');
        // Decrement by 4 since we just added another unit to the array.
        index -= 4;
    }
    return array.join('').replace('..', ',');
};

function dispProvince(
    countryId_querystring,
    countryId_input,
    urlDispProvince,
    provinceId_input
) {
    var fd = new FormData();
    fd.append(countryId_querystring, $(countryId_input).val());
    $.ajax({
        url: urlDispProvince,
        type: "POST",
        enctype: "application/x-www-form-urlencoded",
        data: fd,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
        success: function (res) {
            let o = res[0].province;
            let totProvince = o.length;
            if (totProvince > 0) {
                for (let i = 0; i < totProvince; i++) {
                    optionText = o[i].province_name;
                    optionValue = o[i].id;
                    $(provinceId_input).append(
                        `<option value="${optionValue}">${optionText}</option>`
                    );
                }
            }
        },
    });
}

function dispCity(
    provinceId_querystring,
    provinceId_input,
    urlDispCity,
    cityId_input
) {

    var fd = new FormData();
    fd.append(provinceId_querystring, $(provinceId_input).val());
    $.ajax({
        url: urlDispCity,
        type: "POST",
        enctype: "application/x-www-form-urlencoded",
        data: fd,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
        success: function (res) {
            let o = res[0].city;
            let totCity = o.length;
            if (totCity > 0) {
                for (let i = 0; i < totCity; i++) {
                    optionText = o[i].city_type + " " + o[i].city_name;
                    optionValue = o[i].id;
                    $(cityId_input).append(
                        `<option value="${optionValue}">${optionText}</option>`
                    );
                }
            }
        },
    });
}

function dispCityByCountry(
    countryId_querystring,
    countryId_input,
    urlDispCityByCountry,
    cityId_input
) {

    var fd = new FormData();
    fd.append(countryId_querystring, $(countryId_input).val());
    $.ajax({
        url: urlDispCityByCountry,
        type: "POST",
        enctype: "application/x-www-form-urlencoded",
        data: fd,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
        success: function (res) {
            let o = res[0].city;
            let totCity = o.length;
            if (totCity > 0) {
                for (let i = 0; i < totCity; i++) {
                    optionText = o[i].city_type + " " + o[i].city_name;
                    optionValue = o[i].id;
                    $(cityId_input).append(
                        `<option value="${optionValue}">${optionText}</option>`
                    );
                }
            }
        },
    });
}

function dispDistrict(
    cityId_querystring,
    cityId_input,
    urlDispDistrict,
    districtId_input
) {

    var fd = new FormData();
    fd.append(cityId_querystring, $(cityId_input).val());
    $.ajax({
        url: urlDispDistrict,
        type: "POST",
        enctype: "application/x-www-form-urlencoded",
        data: fd,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
        success: function (res) {
            let o = res[0].district;
            let totDistrict = o.length;
            if (totDistrict > 0) {
                for (let i = 0; i < totDistrict; i++) {
                    optionText = o[i].district_name;
                    optionValue = o[i].id;
                    $(districtId_input).append(
                        `<option value="${optionValue}">${optionText}</option>`
                    );
                }
            }
        },
    });
}

function dispSubDistrict(
    districtId_querystring,
    districtId_input,
    urlDispSubDistrict,
    subdistrictId_input
) {
    // console.log('district id '+districtId_input);
    var fd = new FormData();
    fd.append(districtId_querystring, $(districtId_input).val());
    $.ajax({
        url: urlDispSubDistrict,
        type: "POST",
        enctype: "application/x-www-form-urlencoded",
        data: fd,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
        success: function (res) {
            let o = res[0].sub_district;
            let totSubDistrict = o.length;
            if (totSubDistrict > 0) {
                for (let i = 0; i < totSubDistrict; i++) {
                    optionText = o[i].sub_district_name.toLowerCase().ucwords();
                    optionValue = o[i].id;
                    $(subdistrictId_input).append(
                        `<option value="${optionValue}">${optionText}</option>`
                    );
                }
            }
        },
    });
}

function dispPoscode(
    subdistrictId_querystring,
    subdistrictId_input,
    urlDispSubDistrictPostcode,
    postcode_input
) {
    var fd = new FormData();
    fd.append(subdistrictId_querystring, $(subdistrictId_input).val());
    $.ajax({
        url: urlDispSubDistrictPostcode,
        type: "POST",
        enctype: "application/x-www-form-urlencoded",
        data: fd,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
        success: function (res) {
            let o = res[0].sub_district;
            $(postcode_input).val(o[0].post_code);
        },
    });
}

function dispReceiptOrderInfo(
    pNo_input,
    urlDispReceiptOrder
) {
    var fd = new FormData();
    fd.append('p_no', $(pNo_input).val());
    $.ajax({
        url: urlDispReceiptOrder,
        type: "POST",
        enctype: "application/x-www-form-urlencoded",
        data: fd,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
        success: function (res) {
            let o = res[0].receipt_order;
            let entity_type_name = o[0].entity_type_name + ' ';
            if (o[0].entity_type_name === null) { entity_type_name = ''; }
            let supplierInfo = 'Supplier Info:<br/>' + entity_type_name + o[0].supplier_name + '<br/>' + o[0].supplier_type_name;
            let currencyInfo = 'Currency: ' + o[0].currency_name;
            if (o[0].currency_name === null) { currencyInfo = 'Currency: -'; }
            let shippingAddrInfo = 'Shipping Address: ' + o[0].branch_name + '<br/>' + o[0].branch_address;
            $("#currency_id").val(o[0].currency_id);
            $("#supplier_id").val(o[0].supplier_id);
            $("#supplier_data").show();
            $("#supplier_info").html(supplierInfo + '<br/><br/>' +
                currencyInfo + '<br/><br/>' +
                shippingAddrInfo);
        },
    });
}

function dispSupplierPic(
    supplierId_querystring,
    supplierId_input,
    urlDispSupplierPic,
    supplierPic
) {
    var fd = new FormData();
    fd.append(supplierId_querystring, $(supplierId_input).val());
    $.ajax({
        url: urlDispSupplierPic,
        type: "POST",
        enctype: "application/x-www-form-urlencoded",
        data: fd,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
        success: function (res) {
            let o = res[0].supplier_pic;
            if (o[0].pic1_name != '' && o[0].pic1_name != null) {
                optionText = o[0].pic1_name;
                optionValue = 1;
                $(supplierPic).append(
                    `<option value="${optionValue}">${optionText}</option>`
                );
            }

            if (o[0].pic2_name != '' && o[0].pic2_name != null) {
                optionText = o[0].pic2_name;
                optionValue = 2;
                $(supplierPic).append(
                    `<option value="${optionValue}">${optionText}</option>`
                );
            }

            let supplier_type_name = o[0].supplier_type_name + ' ';
            if (o[0].supplier_type_name === null) {
                supplier_type_name = '';
            }
            let entity_type_name = o[0].entity_type_name+' ';
            if (o[0].entity_type_name == null) {
                entity_type_name = '';
            }
            let sub_district_name = ', ' + o[0].sub_district_name.toLowerCase().ucwords();
            if (o[0].sub_district_name === 'Other') {
                sub_district_name = '';
            }
            let post_code = o[0].post_code;
            if (o[0].post_code === '000000') {
                post_code = '';
            }
            let district_name = ', ' + o[0].district_name;
            if (o[0].district_name === 'Other') {
                district_name = '';
            }
            let province_name = '<br/>' + o[0].province_name;
            if (o[0].province_name === 'Other') {
                province_name = '';
            }
            let cityType = o[0].city_type;
            if (o[0].city_type === 'Luar Negeri') {
                cityType = '';
            }
            let supplierInfo = entity_type_name + o[0].supplier_name +
                '<br/>Address: ' + o[0].office_address + sub_district_name + district_name +
                '<br/>' + cityType + ' ' + o[0].city_name + province_name +
                '<br/>' + o[0].country_name + ' ' + post_code;
            $("#supplier_data").show();
            $("#supplier_info").html(supplierInfo);
        },
    });
}

function dispCustomerPic(
    customerId_querystring,
    customerId_input,
    urlDispCustomerPic,
    customerPic
) {
    var fd = new FormData();
    fd.append(customerId_querystring, $(customerId_input).val());
    $.ajax({
        url: urlDispCustomerPic,
        type: "POST",
        enctype: "application/x-www-form-urlencoded",
        data: fd,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
        success: function (res) {
            let o = res[0].customer_pic;
            if (o[0].pic1_name != '' && o[0].pic1_name != null) {
                optionText = o[0].pic1_name;
                optionValue = 1;
                $(customerPic).append(
                    `<option value="${optionValue}">${optionText}</option>`
                );
            }

            if (o[0].pic2_name != '' && o[0].pic2_name != null) {
                optionText = o[0].pic2_name;
                optionValue = 2;
                $(customerPic).append(
                    `<option value="${optionValue}">${optionText}</option>`
                );
            }

            let entity_type_name = o[0].entity_type_name;
            let sub_district_name = ', ' + o[0].sub_district_name.toLowerCase().ucwords();
            if (o[0].sub_district_name === 'Other') {
                sub_district_name = '';
            }
            let post_code = o[0].post_code;
            if (o[0].post_code === '000000') {
                post_code = '';
            }
            let district_name = ', ' + o[0].district_name;
            if (o[0].district_name === 'Other') {
                district_name = '';
            }
            let province_name = '<br/>' + o[0].province_name;
            if (o[0].province_name === 'Other') {
                province_name = '';
            }
            let cityType = o[0].city_type;
            if (o[0].city_type === 'Luar Negeri') {
                cityType = '';
            }
            let customerInfo = entity_type_name + ' ' + o[0].customer_name +
                '<br/>Address: ' + o[0].office_address + sub_district_name + district_name +
                '<br/>' + cityType + ' ' + o[0].city_name + province_name +
                '<br/>' + o[0].country_name + ' ' + post_code;
            $("#customer_data").show();
            $("#customer_info").html(customerInfo);
        },
    });
}

function dispSupplierCurrency(
    supplierId_querystring,
    supplierId_input,
    urlDispSupplierCurrency,
    supplierCurrency
) {
    var fd = new FormData();
    fd.append(supplierId_querystring, $(supplierId_input).val());
    $.ajax({
        url: urlDispSupplierCurrency,
        type: "POST",
        enctype: "application/x-www-form-urlencoded",
        data: fd,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
        success: function (res) {
            let o = res[0].supplier_currency;
            let totCurr = o.length;
            if (totCurr > 0) {
                for (let i = 0; i < totCurr; i++) {
                    optionText = o[i].currencyName;
                    optionValue = o[i].currency_id;
                    $(supplierCurrency).append(
                        `<option value="${optionValue}">${optionText}</option>`
                    );
                }
            }
        },
    });
}

function dispPartInfo(
    part_id,
    urlDispPartInfo,
    result_element
) {
    var fd = new FormData();
    fd.append('part_id', $(part_id).val());
    $.ajax({
        url: urlDispPartInfo,
        type: "POST",
        enctype: "application/x-www-form-urlencoded",
        data: fd,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
        success: function (res) {
            let o = res[0].part;
            $(result_element).val(formatNumber(o[0].price_list));
        },
    });
}
