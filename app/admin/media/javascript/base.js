$(document).ready(function(){
	/**
	 * Focus first input element of each form
	 */
	$('form input[type="text"]:first').focus();

	/**
	 * Feather icons
	 */
	feather.replace()

	/**
	 * Auto hide dismissable alerts
	 */
	window.setTimeout(function() {
			$(".alert-dismissible").animate({height: 0, opacity: 0}, 500, function() {
				$(this).remove();
			});
		}, 3000
	);

	/**
	 * Initialize confirm modal
	 */
	$('a[data-confirm-message],button[data-confirm-message],input[data-confirm-message]').confirmModal();

	/**
	 * Initialize autogrow
	 */
	autosize($('.autogrow'));

	/**
	 * Pager jump to
	 */
	$('.jump-to-page input').on('keydown', function(e) {
		if(e.which == 13) {
			jump_to_page($(this));
		}
	});

	// Phone number valdiation
	init_number_validator();

	// Select 2 settings for the floating labels
	$('select.select2[multiple=multiple]').select2({
		theme: "bootstrap-5",
		cache: true,
		delay: 250,
		minimumResultsForSearch: Infinity
	});

	$('select.select2[multiple!=multiple]').select2({
		theme: "bootstrap-5",
		cache: true,
		delay: 250,
		minimumResultsForSearch: Infinity
	});

	// Select2 custom js
	select2_custom(); // Custom js for select2 with floating labels
});

// function to change delimiters (to prevent twig collision)
Handlebars.setDelimiter = function(start,end){
	if(!Handlebars.original_compile) Handlebars.original_compile = Handlebars.compile;

	Handlebars.compile = function(source){
		var s = "\\"+start;
		var e = "\\"+end;
		var RE = new RegExp('('+s+'{2,3})(.*?)('+e+'{2,3})','ig');

		replacedSource = source.replace(RE,function(match, startTags, text, endTags, offset, string){
			var startRE = new RegExp(s,'ig'), endRE = new RegExp(e,'ig');

			startTags = startTags.replace(startRE,'\{');
			endTags = endTags.replace(endRE,'\}');

			return startTags+text+endTags;
		});

		return Handlebars.original_compile(replacedSource);
	};
};

Array.prototype.max = function() {
	var max = this[0];
	var len = this.length;
	for (var i = 1; i < len; i++) if (this[i] > max) max = this[i];
	return max;
}

function jump_to_page(el) {
	id = $(el).prop('id').replace('jump-to-page', 'pager');

	val = $(el).val();
	lnk = document.createElement('a');
	lnk.href = $('#' + id + ' a').first().prop('href');

	params = parseQueryString(lnk.search);
	params['p'] = val;
	str = '';

	$.each(params, function(key, value) {
		str += '&' + key + '=' + value;
	});
	lnk.search = str.substring(1);
	window.location.href = lnk.href;
}

var parseQueryString = function( queryString ) {
    var params = {}, queries, temp, i, l;

	if (queryString.indexOf('?') == 0) {
		queryString = queryString.substring(1);
	}

    // Split into key/value pairs
    queries = queryString.split("&");

    // Convert the array of strings into an object
    for ( i = 0, l = queries.length; i < l; i++ ) {
        temp = queries[i].split('=');
        params[temp[0]] = temp[1];
    }

    return params;
};

$.fn.equaliseHeights = function(options) {

	var settings = $.extend({
	    offset: 0 // add amount of pixels to the maxHeight
	}, options );

	var maxHeight = 0, $this = $(this);

	$this.each( function() {
		var height = $(this).innerHeight();
		if ( height > maxHeight ) { maxHeight = height; }
    });

	return $this.css('height', maxHeight + settings.offset);
};

/**
* Phone Number Validator
*/
function init_number_validator() {
	// Initial check of all the number validation fields
	$('.number-validator').each(function () {
		if (!!$(this).val()) {
			validate_number($(this));
		}
	});


	var timeoutId = 0;
	$('.number-validator').on('input', function() {
		var input_field = $(this);
		// We do not want to execute this on every keypress
		clearTimeout(timeoutId);
		timeoutId = setTimeout(function() {
			validate_number(input_field);
		}, 500);
	});
}

/**
 * The post to check the phone numbers
 */
function validate_number(input_field) {
	var country_code = input_field.attr('data-country-code');
	var number = input_field.val();
	var name = input_field.attr('name');

	$.post("/validator?action=phone_number", {number: number, country_code: country_code, name: name},
		function (respons) {
			if(respons.valid){
				input_field.val(respons.formatted);
				input_field.addClass('is-valid');
				input_field.removeClass('is-invalid');

				// Check if we already have the hidden input if not add.
				var hidden_input = $('[name="' + respons.name + '"]');
				if (hidden_input.length) {
					hidden_input.val(respons.formatted_db);
				} else {
					var hidden_input = $("<input>", {
						'type': 'hidden',
						'name': respons.name,
						'value': respons.formatted_db
					});
					input_field.after(hidden_input);
				}
			} else {
				input_field.addClass('is-invalid');
				input_field.removeClass('is-valid');
			}
		},
		"json"
	);
}

function clear_number_inputs(){
	$('.number-validator').each(function (index, element) {
		$(element).val("");
		$(element).removeClass("is-invalid");
		$(element).removeClass("is-valid");
		$(element).next('input').remove();
	});
}

/**
 * Js to make select2 compatible with the floating labels of BS-5
 */
function select2_custom() {
	$('.select2')
		.parent('div')
		.children('span')
		.children('span')
		.children('span')
		.css('height', ' calc(3.5rem + 2px)');
	$('.select2')
		.parent('div')
		.children('span')
		.children('span')
		.children('span')
		.children('span')
		.css('margin-top', '18px');
	$('.select2')
		.parent('div')
		.find('label')
		.css('z-index', '1');
}