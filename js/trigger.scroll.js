jQuery(window).scroll(function() {
	var scrollValue = ((jQuery(document).height()-jQuery(window).height())/100)*optinTriggerValue;
	var scrollBottom = jQuery(window).scrollTop();

	if(scrollBottom > scrollValue.toFixed(0))
		jQuery("#plyOptinWrapper").show();
});