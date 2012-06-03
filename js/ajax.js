
var numConnections = 0;

$(window).bind('beforeunload', function () {
	if (numConnections > 0) {
		return "Some actions are not yet saved.\nAre you sure you want to leave the page?";
	}
});

function callAjax (url, data, callback) {

	numConnections++;
	jQuery.ajax(url, {
		type : "POST",
		timeout: 6000,
		data : data,
		complete : function(r, status) {
			numConnections--;
			if (status != "success" || !callback(r)) {
				alert("An error occured while saving.\nThe age will be reloaded.");
				window.location.reload();
			}
		}
	});
}

