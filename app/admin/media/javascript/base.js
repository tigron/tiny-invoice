$(document).ready(function(){
	
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
	 * Confirm
	 */
	init_confirm();

});

function init_confirm() {

	var source = $("#data-confirm-tmpl").html();
	Handlebars.setDelimiter('(',')');
	var template = Handlebars.compile(source);

	$('a[data-confirm-message]').on('click', function(ev) {
        ev.preventDefault();

		var data = {'title': '', 'message': '', 'btn_ok': '', 'btn_href': '', 'btn_cancel': ''};

		data.title = $(this).data('confirm-title');
		data.message = $(this).data('confirm-message');
		data.btn_ok = $(this).data('confirm-btn-ok');
		data.btn_cancel = $(this).data('confirm-btn-cancel');
		data.btn_href = $(this).prop('href');

		if (!$('#dataConfirmModal').length) {
			$('body').append(template(data));
		} else {
			$('#dataConfirmModal').replaceWith(template(data));
		}

		$('#dataConfirmModal').modal('show');
		return false;
    });
}

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