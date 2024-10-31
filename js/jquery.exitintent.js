(function (jQuery) {
	var timer;

	function trackLeave(ev) {
		if (ev.pageY > 0) {
			return;
		}

		if (timer) {
			clearTimeout(timer);
		}

		if (jQuery.exitIntent.settings.sensitivity <= 0) {
			jQuery.event.trigger('exitintent');
			return;
		}

		timer = setTimeout(
			function() {
				timer = null;
				jQuery.event.trigger('exitintent');
			}, jQuery.exitIntent.settings.sensitivity);
	}

	function trackEnter() {
		if (timer) {
			clearTimeout(timer);
			timer = null;
		}
	}

	jQuery.exitIntent = function(enable, options) {
		jQuery.exitIntent.settings = jQuery.extend(jQuery.exitIntent.settings, options);

		if (enable == 'enable') {
			jQuery(window).mouseleave(trackLeave);
			jQuery(window).mouseenter(trackEnter);
		} else if (enable == 'disable') {
			trackEnter(); // Turn off any outstanding timer
			jQuery(window).unbind('mouseleave', trackLeave);
			jQuery(window).unbind('mouseenter', trackEnter);
		} else {
			throw "Invalid parameter to jQuery.exitIntent -- should be 'enable'/'disable'";
		}
	}

	jQuery.exitIntent.settings = {
		'sensitivity': 300
	};

})(jQuery);
