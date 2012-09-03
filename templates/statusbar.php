<div class="statusbar">
	<a href="javascript:document.getElementById('statusbar').style.display='none';" class="statusbar-close">&times;</a>
	<?php echo $GLOBALS['website']->statusbar(); ?><span class="statusbar-divider">, <span id="statusbar-debugr" class="statusbar-tab"><a href="http://debugr.net/" target="_top">debugR</a></span>
	<script type="text/javascript">
	(function () {
		var counter = 0;
		window.addEventListener('message', function (e) {
			if (e.data.debugR && e.data.label === 'sledgehammer-statusbar') {
				counter++;
				var statusbar = document.getElementById('statusbar-debugr');
				if (counter === 1) {
					statusbar.innerHTML= '<span>Ajax: <b id="statusbar-debugr-count"></b> requests<div id="statusbar-debugr-popout" class="statusbar-popout"></span></span>';
				}
				document.getElementById('statusbar-debugr-count').innerHTML = counter;
				var popout = document.getElementById('statusbar-debugr-popout');
				var div = document.createElement('div');
				div.innerHTML = '<b>' + e.data.url.replace(/^.*[\/\\]/g, '') + '</b> ' + e.data.message;
				popout.appendChild(div);
			}
	}, false);
	document.documentElement.setAttribute('data-debugR'); // Signal the extension that the eventlistener is active.
	})();
	</script>
</div>
