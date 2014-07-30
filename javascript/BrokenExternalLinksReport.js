(function($) {
	$('#externalLinksReport').entwine({
		onclick: function() {
			$(this).start();
			$(this).poll();
		},
		start: function() {
			// initiate a new job
			$('#ReportHolder').empty();
			$('#ReportHolder').text('Running report 0%');
			$('#ReportHolder').append('<span class="ss-ui-loading-icon"></span>');
			$.ajax({url: "admin/externallinks/start", async: true, timeout: 1000 });
		},
		poll: function() {
			// poll the current job and update the front end status
			$.ajax({
				url: "admin/externallinks/getJobStatus",
				async: true,
				success: function(data) {
					var obj = $.parseJSON(data);
					if (!obj) return;
					var completed = obj.Completed ? obj.Completed : 0;
					var total = obj.Total ? obj.Total : 0;
					if (total > 0 && completed == total) {
						$('#ReportHolder').text('Report Finished ' + completed + '/' + total);
					} else {
						setTimeout(function() { $('#externalLinksReport').poll(); }, 1);
					}
					if (total && completed) {
						if (completed < total) {
							var percent = (completed / total) * 100;
							$('#ReportHolder').text('Running report  ' + completed + '/' + 
								total + ' (' + percent.toFixed(2) + '%)');
								$('#ReportHolder').
									append('<span class="ss-ui-loading-icon"></span>');
							}
					}
				},
				error: function(e) {
					console.log(e);
				}
			});
		}
	});
}(jQuery));
