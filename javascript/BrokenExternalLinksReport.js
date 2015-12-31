(function($) {
	$.entwine('ss', function($) {
		$('#externalLinksReport').entwine({
			PollTimeout: null,
			onclick: function() {
				this.start();
			},
			onmatch: function() {
				// poll the current job and update the front end status
				$('#externalLinksReport').hide();
				this.poll();
			},
			start: function() {
				// initiate a new job
				$('#ReportHolder').empty();
				$('#ReportHolder').text('Running report 0%');
				$('#ReportHolder').append('<span class="ss-ui-loading-icon"></span>');
				$('#externalLinksReport').hide();
				$.ajax({url: "admin/externallinks/start", async: false, timeout: 3000 });
				this.poll();
			},
			poll: function() {
				var self = this;

				$.ajax({
					url: "admin/externallinks/getJobStatus",
					async: true,
					success: function(data) {
						// No report, so let user create one
						if (!data) {
							$('#externalLinksReport').show();
							return;
						}

						// Parse data
						var completed = data.Completed ? data.Completed : 0;
						var total = data.Total ? data.Total : 0;
						
						// If complete status
						if (data.Status === 'Completed') {
							$('#ReportHolder').text('Report Finished ' + completed + '/' + total);
							$('#externalLinksReport').show();
							return;
						}
						
						// If incomplete update status
						if (completed < total) {
							var percent = (completed / total) * 100;
							$('#ReportHolder')
								.text('Running report  ' + completed + '/' +  total + ' (' + percent.toFixed(2) + '%)')
								.append('<span class="ss-ui-loading-icon"></span>');
						}

						// Ensure the regular poll method is run
						// kill any existing timeout
						if(self.getPollTimeout() !== null) {
							clearTimeout(self.getPollTimeout());
						}

						self.setPollTimeout(setTimeout(function() { $('#externalLinksReport').poll(); }, 1000));
					},
					error: function(e) {
						if(typeof console !== 'undefined') console.log(e);
					}
				});
			}
		});
	});
}(jQuery));
