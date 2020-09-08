/**
 * Deal with show/hide in jQuery vs Bootstrap
 */
!function($) {
	"use strict";

	var oldShowHide = {'show': $.fn.show, 'hide': $.fn.hide};
	$.fn.extend({
		show: function() {
			this.each(function(index) {
				var $element = $(this);
				if ($element.hasClass('hide')) {
					$element.removeClass('hide');
				}
			});
			return oldShowHide.show.call(this);
		},
		hide: function() {
			this.each(function(index) {
				var $element = $(this);
				if ($element.hasClass('show')) {
					$element.removeClass('show');
				}
			});
			return oldShowHide.hide.call(this);
		}
	});
}(window.jQuery);