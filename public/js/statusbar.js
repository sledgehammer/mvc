(function() {
	var counter = 0;
	window.addEventListener('message', function(e) {
		if (e.data.debugR && e.data.label === 'sledgehammer-statusbar') {
			counter++;
			var statusbar = document.getElementById('statusbar-debugr');
			if (counter === 1) {
				statusbar.innerHTML = '<span>DebugR <b id="statusbar-debugr-count"></b> requests<div id="statusbar-debugr-popout" class="statusbar-popout"></div></span>';
			}
			document.getElementById('statusbar-debugr-count').innerHTML = counter;
			var popout = document.getElementById('statusbar-debugr-popout');
			var div = document.createElement('div');
			div.innerHTML = '<b>' + e.data.url.replace(/^.*[\/\\]/g, '') + '</b> ' + e.data.message;
			popout.appendChild(div);
		}
	}, false);
	document.documentElement.setAttribute('data-debugR', 'active'); // Signal the extension that the eventlistener is active.
}());
