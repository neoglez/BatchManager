var BatchManager = BatchManager || { };
(function($) {
	var batch = function() {
		var holder = $('#progress-container');

		// Success: redirect to the summary.
		var updateCallback = function(progress, status, pb) {
			if (progress == 100) {
				pb.stopMonitoring();
				window.location = BatchManager.finishedUri;
			}
		};

		var errorCallback = function(pb) {
			holder.prepend($('<p class="error"></p>').html('here an error'));
		};

		var progress = holder.ProgressBar('updateprogress',
				updateCallback, 'POST', errorCallback);
		progress.setProgress(-1, 'Initializing');
		progress.startMonitoring(BatchManager.processUri, 10);
	};
	$(function() {
		batch();
	});
})(jQuery);
