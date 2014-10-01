$(function(){
	// submit ２度押し防止
	$(document).on('submit', 'form', function(e) {
		form = $(this);
		// slight timeout so that the submit button gets properly serialized
    setTimeout(function(){ disableFormElements(form); }, 13);
	});

	function disableFormElements(form) {
		var disableSelector = 'input[data-disable-with]:enabled, button[data-disable-with]:enabled, textarea[data-disable-with]:enabled, input[data-disable]:enabled, button[data-disable]:enabled, textarea[data-disable]:enabled';

		formElements(form, disableSelector).each(function() {
			console.log(this);
			disableFormElement($(this));
		});
	};

	function disableFormElement(element) {
		var method, replacement;

		method = element.is('button') ? 'html' : 'val';
		replacement = element.data('disable-with');

		if (replacement !== undefined) {
			element[method](replacement);
		}

		element.prop('disabled', true);
	};

	// Helper function that returns form elements that match the specified CSS selector
	// If form is actually a "form" element this will return associated elements outside the from that have
	// the html form attribute set
	function formElements(form, selector) {
		return form.is('form') ? $(form[0].elements).filter(selector) : form.find(selector);
	};
});
