$(document).ready(function(){
	
	/**
	 * Focus first input element of each form
	 */
	$('form input[type="text"]:first').focus();

	/**
	 * Auto hide dismissable alerts
	 */
	window.setTimeout(function() {
			$(".alert-dismissable").animate({height: 0, opacity: 0}, 500, function() {
				$(this).remove();
			});
		}, 3000
	);

	/**
	 * Initialize confirm modal
	 */
	$('a[data-confirm-message],button[data-confirm-message],input[data-confirm-message]').confirmModal();

	/**
	 * Initialize datetimepicker
	 */
	$('.datepicker').tigronDatetimepicker({
	    'format': 'DD/MM/YYYY',
		'extraFormats': [ 'YYYY-MM-DD' ],
	    'postFormat': 'YYYY-MM-DD'
	});

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