(function($) {
	$('#externalLinksReport').entwine({
		onclick: function() {
			$(this).start();
		},
		onmatch: function() {
			$(this).poll();
		},
		start: function() {
			// initiate a new job
			$('#ReportHolder').empty();
			$('#ReportHolder').text('Running report 0%');
			$('#ReportHolder').append('<span class="ss-ui-loading-icon"></span>');
			$('#externalLinksReport').hide();
			$.ajax({url: "admin/externallinks/start", async: true, timeout: 3000 });
			$(this).poll();
		},
		poll: function() {
			// poll the current job and update the front end status
			$('#externalLinksReport').hide();
			$.ajax({
				url: "admin/externallinks/getJobStatus",
				async: true,
				success: function(data) {
					var obj = $.parseJSON(data);
					if (!obj) {
						setTimeout(function() { $('#externalLinksReport').poll(); }, 1000);
					}
					var completed = obj.Completed ? obj.Completed : 0;
					var total = obj.Total ? obj.Total : 0;
					var jobStatus = obj.Status ? obj.Status : 'Running';
					if (jobStatus == 'Completed') {
						$('#ReportHolder').text('Report Finished ' + completed + '/' + total);
						$('#externalLinksReport').show();
					} else {
						setTimeout(function() { $('#externalLinksReport').poll(); }, 1000);
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
