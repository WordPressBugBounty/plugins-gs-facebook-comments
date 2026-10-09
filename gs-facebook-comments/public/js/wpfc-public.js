(function () {
	'use strict';

	var state = window.GenialSoulsSocialCommentsDisqus || { loaded: false, loading: false };
	window.GenialSoulsSocialCommentsDisqus = state;

	function readConfig(container) {
		try { return JSON.parse(container.getAttribute('data-gsc-disqus-config') || '{}'); } catch (error) { return null; }
	}

	function message(container, text, retry) {
		var target = container.querySelector('.gsc-disqus-target');
		if (!target) { return; }
		target.textContent = text;
		if (retry) {
			var button = document.createElement('button');
			button.type = 'button';
			button.className = 'gsc-disqus-retry';
			button.textContent = retry;
			button.addEventListener('click', function () { load(container); });
			target.appendChild(document.createTextNode(' '));
			target.appendChild(button);
		}
	}

	function load(container) {
		var config = readConfig(container);
		if (!config || !config.shortname || !config.identifier || !config.url) { message(container, 'Comments are not configured correctly.', ''); return; }
		if (state.loaded || state.loading) { return; }
		state.loading = true;
		var target = container.querySelector('.gsc-disqus-target');
		var button = container.querySelector('.gsc-disqus-load');
		if (button) { button.disabled = true; button.hidden = true; }
		if (!target) { message(container, 'Comments cannot be loaded.', ''); return; }

		var thread = document.createElement('div');
		thread.id = 'disqus_thread';
		target.appendChild(thread);
		window.disqus_config = function () {
			this.page = this.page || {};
			this.page.identifier = config.identifier;
			this.page.url = config.url;
			this.page.title = config.title || '';
		};

		var script = document.createElement('script');
		script.async = true;
		script.src = 'https://' + encodeURIComponent(config.shortname) + '.disqus.com/embed.js';
		script.setAttribute('data-timestamp', String(+new Date()));
		script.onload = function () { state.loaded = true; state.loading = false; };
		script.onerror = function () {
			state.loading = false;
			if (thread.parentNode) { thread.parentNode.removeChild(thread); }
			message(container, 'Comments could not be loaded. Please try again.', 'Retry');
		};
		document.body.appendChild(script);
	}

	function setup(container) {
		var config = readConfig(container);
		var button = container.querySelector('.gsc-disqus-load');
		if (!config) { message(container, 'Comments could not be loaded.', ''); return; }
		if (button) { button.addEventListener('click', function () { load(container); }); }
		if ('immediate' === config.mode) { load(container); }
		else if ('viewport' === config.mode) {
			if ('IntersectionObserver' in window) {
				var observer = new IntersectionObserver(function (entries) {
					if (entries[0].isIntersecting) { observer.disconnect(); load(container); }
				}, { rootMargin: '300px 0px' });
				observer.observe(container);
			} else { load(container); }
		}
	}

	document.addEventListener('DOMContentLoaded', function () {
		var containers = document.querySelectorAll('.genialsouls-social-comments-disqus[data-gsc-disqus-config]');
		for (var i = 0; i < containers.length; i += 1) { setup(containers[i]); }
	});
}());
