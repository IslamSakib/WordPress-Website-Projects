(function($) {
	'use strict';

	const JobBoard = {

		currentJobId: null,

		/**
		 * Initialize
		 */
		init: function() {
			this.bindEvents();
		},

		/**
		 * Bind all events
		 */
		bindEvents: function() {
			// View job details
			$(document).on('click', '.ojb-view-details-btn', this.openJobDetails);

			// Apply from card
			$(document).on('click', '.ojb-apply-btn', this.openApplicationModal);

			// Apply from details modal
			$(document).on('click', '#ojb-apply-from-detail', this.applyFromDetails);

			// Close details modal
			$(document).on('click', '.ojb-close-detail-btn', this.closeJobDetails);

			// Close modals
			$(document).on('click', '.ojb-modal-close', function(e) {
				e.preventDefault();
				if ($('#ojb-job-details-modal').is(':visible')) {
					JobBoard.closeJobDetails();
				} else if ($('#ojb-application-modal').is(':visible')) {
					JobBoard.closeApplicationModal();
				}
			});

			$(document).on('click', '.ojb-cancel-btn', this.closeApplicationModal);

			// Smooth scroll on pagination
			$(document).on('click', '.ojb-page-link', this.handlePaginationClick);

			// Close on outside click
			$(document).on('click', '.ojb-modal', function(e) {
				if ($(e.target).hasClass('ojb-modal')) {
					if ($(e.target).attr('id') === 'ojb-job-details-modal') {
						JobBoard.closeJobDetails();
					} else {
						JobBoard.closeApplicationModal();
					}
				}
			});

			// Form submission
			$(document).on('submit', '#ojb-application-form', this.submitForm);

			// ESC key
			$(document).on('keydown', function(e) {
				if (e.key === 'Escape') {
					if ($('#ojb-application-modal').is(':visible')) {
						JobBoard.closeApplicationModal();
					} else if ($('#ojb-job-details-modal').is(':visible')) {
						JobBoard.closeJobDetails();
					}
				}
			});
		},

		/**
		 * Open job details modal
		 */
		openJobDetails: function(e) {
			e.preventDefault();

			const jobId = $(this).data('job-id');
			const jobCard = $(this).closest('.ojb-job-card');

			// Get job data
			const title = jobCard.data('job-title');
			const company = jobCard.data('company');
			const location = jobCard.data('location');
			const deadline = jobCard.data('deadline');
			const closingDate = jobCard.data('closing-date');
			const expired = jobCard.data('expired');
			const fullContent = jobCard.find('.ojb-full-content').html();

			// Populate modal
			$('#ojb-detail-job-title').text(title);
			$('#ojb-detail-company').text(company);
			$('#ojb-detail-location').text(location);
			$('#ojb-detail-deadline').text(deadline ? deadline : 'Not specified');
			$('#ojb-detail-closing-date').text(closingDate ? closingDate : 'Not specified');
			$('#ojb-detail-description').html(fullContent);

			// Store current job ID
			JobBoard.currentJobId = jobId;

			// Hide/show apply button based on expiration
			if (expired == '1') {
				$('#ojb-apply-from-detail').hide();
			} else {
				$('#ojb-apply-from-detail').show();
			}

			// Show modal
			$('#ojb-job-details-modal').fadeIn(300);
			$('body').css('overflow', 'hidden');
		},

		/**
		 * Close job details modal
		 */
		closeJobDetails: function(e) {
			if (e) {
				e.preventDefault();
			}

			$('#ojb-job-details-modal').fadeOut(300);
			$('body').css('overflow', 'auto');
		},

		/**
		 * Apply from details modal
		 */
		applyFromDetails: function(e) {
			e.preventDefault();

			// Close details modal
			$('#ojb-job-details-modal').fadeOut(300);

			// Open application modal
			setTimeout(function() {
				$('#ojb-job-id').val(JobBoard.currentJobId);
				$('#ojb-application-form')[0].reset();
				$('#ojb-form-message').hide().removeClass('success error');
				$('#ojb-application-modal').fadeIn(300);
			}, 300);
		},

		/**
		 * Open application modal
		 */
		openApplicationModal: function(e) {
			e.preventDefault();

			const jobId = $(this).data('job-id');

			// Set job ID
			$('#ojb-job-id').val(jobId);

			// Reset form
			$('#ojb-application-form')[0].reset();
			$('#ojb-form-message').hide().removeClass('success error');

			// Show modal
			$('#ojb-application-modal').fadeIn(300);
			$('body').css('overflow', 'hidden');
		},

		/**
		 * Close application modal
		 */
		closeApplicationModal: function(e) {
			if (e) {
				e.preventDefault();
			}

			$('#ojb-application-modal').fadeOut(300);
			$('body').css('overflow', 'auto');
		},

		/**
		 * Submit application form
		 */
		submitForm: function(e) {
			e.preventDefault();

			const form = $(this);
			const submitBtn = form.find('.ojb-submit-btn');
			const messageBox = $('#ojb-form-message');

			// Client-side validation
			if (!JobBoard.validateForm(form)) {
				return false;
			}

			// Disable button
			submitBtn.prop('disabled', true).text(ojbData.messages.submitting);

			// Hide messages
			messageBox.hide().removeClass('success error');

			// Prepare FormData
			const formData = new FormData(form[0]);
			formData.append('action', 'submit_job_application');
			formData.append('security', ojbData.nonce);

			// Submit via AJAX
			$.ajax({
				url: ojbData.ajaxUrl,
				type: 'POST',
				data: formData,
				processData: false,
				contentType: false,
				success: function(response) {
					if (response.success) {
						// Show success message
						messageBox
							.addClass('success')
							.html(response.data.message)
							.fadeIn();

						// Reset form
						form[0].reset();

						// Close modal after 3 seconds
						setTimeout(function() {
							JobBoard.closeApplicationModal();
						}, 3000);

					} else {
						// Show error message
						messageBox
							.addClass('error')
							.html(response.data.message)
							.fadeIn();
					}
				},
				error: function(xhr, status, error) {
					messageBox
						.addClass('error')
						.html(ojbData.messages.errorOccurred)
						.fadeIn();
				},
				complete: function() {
					// Re-enable button
					submitBtn.prop('disabled', false).text(ojbData.messages.submit);
				}
			});

			return false;
		},

		/**
		 * Validate form
		 */
		validateForm: function(form) {
			const messageBox = $('#ojb-form-message');

			// Check required fields
			let isValid = true;
			form.find('[required]').each(function() {
				if (!$(this).val()) {
					isValid = false;
					messageBox
						.addClass('error')
						.html(ojbData.messages.fillAllFields)
						.fadeIn();
					return false;
				}
			});

			if (!isValid) {
				return false;
			}

			// Validate email
			const email = form.find('[name="email"]').val();
			const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
			if (!emailRegex.test(email)) {
				messageBox
					.addClass('error')
					.html(ojbData.messages.invalidEmail)
					.fadeIn();
				return false;
			}

			// Validate file
			const fileInput = form.find('[name="resume"]')[0];
			if (!fileInput.files || fileInput.files.length === 0) {
				messageBox
					.addClass('error')
					.html(ojbData.messages.selectPDF)
					.fadeIn();
				return false;
			}

			const file = fileInput.files[0];

			// Check file type (PDF only)
			if (file.type !== 'application/pdf') {
				messageBox
					.addClass('error')
					.html(ojbData.messages.pdfOnly)
					.fadeIn();
				return false;
			}

			// Check file size (5MB max)
			const maxSize = 5 * 1024 * 1024;
			if (file.size > maxSize) {
				messageBox
					.addClass('error')
					.html(ojbData.messages.fileTooLarge)
					.fadeIn();
				return false;
			}

			return true;
		},

		/**
		 * Handle pagination click with smooth scroll
		 */
		handlePaginationClick: function(e) {
			const $jobBoard = $('.ojb-job-board-container');
			if ($jobBoard.length) {
				// Allow the link to navigate, then scroll after page loads
				setTimeout(function() {
					$('html, body').animate({
						scrollTop: $jobBoard.offset().top - 100
					}, 600);
				}, 100);
			}
		}
	};

	// Initialize on document ready
	$(document).ready(function() {
		JobBoard.init();
	});

})(jQuery);