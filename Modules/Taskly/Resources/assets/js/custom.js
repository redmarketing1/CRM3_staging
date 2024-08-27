$(document).ready(function () {
});



function site_money_format(formate) {
	if(formate == "en_US"){
		var moneyFormatter = new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' });
		return moneyFormatter;
	} else {
		var moneyFormatter = new Intl.NumberFormat('de-DE', { style: 'currency', currency: 'EUR' });
		return moneyFormatter;
	}
}

function convert_to_site_money_formate(formate,price=0) {
	var formated_price = price;
	if(formate == "en_US") {
		formated_price = formated_price.replace(",","");
	} else {
		formated_price = formated_price.replace(".","/");
		formated_price = formated_price.replace(",",".");
		formated_price = formated_price.replace("/","");
	}
	return parseFloat(formated_price).toFixed(5);
}

function parseLocaleNumber(formate,stringNumber) {
	var locale = "de";
	if(formate == "en_US"){
		locale = "us";
	}
    var thousandSeparator = Intl.NumberFormat(locale).format(11111).replace(/\p{Number}/gu, '');
    var decimalSeparator = Intl.NumberFormat(locale).format(1.1).replace(/\p{Number}/gu, '');

    return parseFloat(stringNumber
        .replace(new RegExp('\\' + thousandSeparator, 'g'), '')
        .replace(new RegExp('\\' + decimalSeparator), '.')
    );
}
