var ChunkedFileUpload = (function($) {
	
	var settings = {
		"maxChunkSize"						:	2 * 1024 * 1024,	// the size of the chunks
		"fail"                              : function(){ alert("an error occured during upload"); }
	}
	
	function initialize(options)
	{
		var local_settings = $.extend(settings, options);
		
		$(local_settings["input_button_selector"]).fileupload(local_settings);
	}
	
	function setBarData(data, progress_bar_selector, size_uploaded_selector)
	{
		var progress = parseInt(data.loaded / data.total * 100, 10);
		
		ChunkedFileUpload.setBarWidth(progress_bar_selector, progress);
		$(size_uploaded_selector).html(" (" + ChunkedFileUpload.formatBytes(data.loaded) + " / " + ChunkedFileUpload.formatBytes(data.total) + ")");
	}
	
	function setBarWidth(progress_bar_zone_selector, percentage)
	{
		if(percentage > 0)
		{
			ChunkedFileUpload.showProgressBar(progress_bar_zone_selector);
		}
		
		$(progress_bar_zone_selector + ' .bar').attr("aria-valuenow", percentage);
		$(progress_bar_zone_selector + ' .bar').css('width', percentage + '%');
		$(progress_bar_zone_selector + ' .percent').html(percentage + '%');
	}
	
	function showProgressBar(progress_bar_zone_selector)
	{
		$(progress_bar_zone_selector).show();
	}
	
	function hideProgressBar(progress_bar_zone_selector)
	{
		$(progress_bar_zone_selector).hide();
	}
	
	function resetProgressBar(progress_bar_zone_selector, size_uploaded_selector)
	{
		ChunkedFileUpload.hideProgressBar(progress_bar_zone_selector);	
		ChunkedFileUpload.setBarWidth(progress_bar_zone_selector, 0);
		$(size_uploaded_selector).html("");
	}
	
	function resetProgressBarIfComplete(progress_bar_zone_selector, size_uploaded_selector, data)
	{
		if(data.loaded == data.total)
		{
			ChunkedFileUpload.resetProgressBar(progress_bar_zone_selector, size_uploaded_selector);
		}
	}
	
	function hideNotification(notification_selector)
	{
		$(notification_selector).hide();
	}
	
	function showSuccessMessage(notification_selector, progress_bar_zone_selector, message)
	{
		$(notification_selector).removeClass("bg-danger");
		$(notification_selector).addClass("bg-success");
		$(notification_selector).html(message);
		$(notification_selector).show();
	}
	
	function showSuccessMessageIfComplete(notification_selector, progress_bar_zone_selector, message, data)
	{
		if(data.loaded == data.total)
		{
			ChunkedFileUpload.showSuccessMessage(notification_selector, progress_bar_zone_selector, message);
		}
	}
	
	function formatBytes (bytes) 
	{
		if (bytes >= (1024*1024*1024)) {
			return (bytes / (1024*1024*1024)).toFixed(2) + ' Gb';
		}
		if (bytes >= (1024*1024)) {
			return (bytes / (1024*1024)).toFixed(2) + ' Mb';
		}
		if (bytes >= 1024) {
			return (bytes / 1024).toFixed(2) + ' Kb';
		}
		return bytes + ' b';
	};

	function generate_upload_id() {
		
		if (!Date.now) {
			Date.now = function() { return new Date().getTime(); }
		}
		
		var S4 = function() {
			return (((1+Math.random())*0x10000)|0).toString(16).substring(1);
		};
		return Date.now() + "-" + (S4()+S4()+"-"+S4()+"-"+S4()+"-"+S4()+"-"+S4()+S4()+S4());
	}
	
	return {
		settings				:	settings,
		initialize				:	initialize,
		generate_upload_id		:	generate_upload_id,
		formatBytes				:	formatBytes,
		setBarData				:	setBarData,
		setBarWidth				:	setBarWidth,
		showSuccessMessage		:	showSuccessMessage,
		showSuccessMessageIfComplete	:	showSuccessMessageIfComplete,
		showProgressBar			:	showProgressBar,
		hideProgressBar			:	hideProgressBar,
		resetProgressBar		:	resetProgressBar,
		resetProgressBarIfComplete	:	resetProgressBarIfComplete,
		hideNotification		:	hideNotification
	}
	
})(jQuery);