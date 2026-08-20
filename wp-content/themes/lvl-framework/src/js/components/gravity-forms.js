jQuery(document).on('gform_post_render', function() {
	
	let inputFields = document.querySelectorAll('.gfield input, .gfield textarea');

	inputFields.forEach(function(input) {
		input.addEventListener('focus', function() {
			this.closest('.gfield').classList.add('focused');
		});

		input.addEventListener('blur', function() {
			if (this.value === '') {
				this.closest('.gfield').classList.remove('focused');
			}
		});

		input.addEventListener('input', function() {
			if (this.value !== '') {
				this.closest('.gfield').classList.add('focused');
			} else {
				this.closest('.gfield').classList.remove('focused');
			}
		});	
	});

	let datepickerInput = document.querySelector('.datepicker');
	if (datepickerInput) {
		// Add the ARIA attributes
		datepickerInput.setAttribute('aria-label', 'mm/dd/yyyy');
		datepickerInput.setAttribute('aria-hidden', 'false');
	}

    let submitButton = document.querySelector('.gform_footer input[type="submit"], .gform_page_footer input[type="submit"]');
	if (submitButton !== null) {
		submitButton.classList.add('btn', 'btn-primary');
	}

	let prevButton = document.querySelectorAll('.gform_page_footer input.gform_previous_button');
	prevButton.forEach(function(button){
		button.classList.add('btn', 'btn-secondary');

	});

	let nextButton = document.querySelectorAll('.gform_page_footer input.gform_next_button');
	nextButton.forEach( function (button){
		button.classList.add('btn', 'btn-primary');
	});
})