(function($) {
	/**
	 * This code is just the original Drupal progress.js with some naming changes
	 * and a littler different markup.
	 * @see https://drupal.org/
	 * @see https://github.com/drupal/drupal/blob/7.x/misc/progress.js
	 */

	/**
	 * A progressbar object. Initialized with the given id. It will be inserted
	 * into the DOM through ProgressBar.element i.e. appended to the element selected.
	 * Be aware that jQuery chaining will indeed be broken.
	 * 
	 *
	 * method is the function which will perform the HTTP request to get the
	 * progress bar state. Either "GET" or "POST".
	 *
	 * e.g. pb = ('my_selector').ProgressBar('myProgressBar');
	 */

	'use strict';

	// PROGRESSBAR CLASS DEFINITION
	// ============================
	var ProgressBar = function(id, updateCallback, method, errorCallback) {
		var pb = this;
		this.id = id;
		this.method = method || 'GET';
		this.updateCallback = updateCallback;
		this.errorCallback = errorCallback;

		// The WAI-ARIA setting aria-live="polite" will announce changes after
		// users
		// have completed their current activity and not interrupt the screen
		// reader.
		this.element = $('<div class="panel panel-default"></div>').attr('id',
				id);
		this.element.html(
				  '<div class="panel-body">'
				+ '<div class="progress progress-striped active">'
				+ '<div class="progress-bar" role="progressbar">'
				+ '<span class="progress-bar-text"></span>' 
				+ '</div>'
				+ '</div>' 
				+ '</div>' 
				+ '<div class="panel-footer"></div>');
	};

	/**
	 * Set the percentage and status message for the progressbar.
	 */
	ProgressBar.prototype.setProgress = function(percentage, message) {
		if (percentage >= 0 && percentage <= 100) {
			$('div.progress-bar', this.element).css('width', percentage + '%');
			$('span.progress-bar-text', this.element).html(percentage + '%');
		}
		$('div.panel-footer', this.element).html(message);
		if (this.updateCallback) {
			this.updateCallback(percentage, message, this);
		}
	};

	/**
	 * Start monitoring progress via Ajax.
	 */
	ProgressBar.prototype.startMonitoring = function(uri, delay) {
		this.delay = delay;
		this.uri = uri;
		this.sendPing();
	};

	/**
	 * Stop monitoring progress via Ajax.
	 */
	ProgressBar.prototype.stopMonitoring = function() {
		clearTimeout(this.timer);
		// This allows monitoring to be stopped from within the callback.
		this.uri = null;
	};

	/**
	 * Request progress data from server.
	 */
	ProgressBar.prototype.sendPing = function() {
		if (this.timer) {
			clearTimeout(this.timer);
		}
		if (this.uri) {
			var pb = this;
			// When doing a post request, you need non-null data. Otherwise a
			// HTTP 411 or HTTP 406 (with Apache mod_security) error may result.
			$.ajax({
				type : this.method,
				url : this.uri,
				data : '',
				dataType : 'json',
				success : function(progress) {
					// Display errors.
					if (progress.status == 0) {
						pb.displayError(progress.data);
						return;
					}
					// Update display.
					pb.setProgress(progress.percentage, progress.message);
					// Schedule next timer.
					pb.timer = setTimeout(function() {
						pb.sendPing();
					}, pb.delay);
				},
				error : function(xmlhttp) {
					pb.displayError("An AJAX HTTP error occurred.");
				}
			});
		}
	};

	/**
	 * Display errors on the page.
	 */
	ProgressBar.prototype.displayError = function(string) {
		var error = $('<div class="alert alert-danger"></div>').html(string);
		$(this.element).before(error).hide();

		if (this.errorCallback) {
			this.errorCallback(this);
		}
	};
	
	// PROGRESSBAR PLUGIN DEFINITION
    // =============================
	var old = $.fn.ProgressBar;

	$.fn.ProgressBar = function(id, updateCallback, method, errorCallback) {
		var instance = new ProgressBar(id, updateCallback, method, errorCallback);
		this.append(instance.element);
		return instance;
	};

	
	// PROGRESSBAR NO CONFLICT
	// =======================

	$.fn.ProgressBar.noConflict = function() {
		$.fn.progressbar = old;
		return this;
	};

})(jQuery);