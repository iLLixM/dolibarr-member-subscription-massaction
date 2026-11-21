/* Copyright (C) 2026 iLLixM
 * SPDX-License-Identifier: GPL-3.0-or-later
 */
(function () {
	'use strict';
	var form = document.getElementById('msm-confirm-form');
	var button = document.getElementById('msm-final-submit');
	var status = document.getElementById('msm-submit-status');
	if (!form || !button || !status) { return; }
	function reset() {
		form.removeAttribute('aria-busy');
		button.disabled = false;
		status.hidden = true;
		var generated = form.querySelector('input[data-msm-action]');
		if (generated) { generated.remove(); }
	}
	form.addEventListener('submit', function (event) {
		var submitter = event.submitter || document.activeElement;
		if (!submitter || submitter.value !== 'book') { return; }
		if (form.getAttribute('aria-busy') === 'true') { event.preventDefault(); return; }
		var action = document.createElement('input');
		action.type = 'hidden'; action.name = 'action'; action.value = 'book'; action.setAttribute('data-msm-action', '1');
		form.appendChild(action);
		form.setAttribute('aria-busy', 'true');
		button.disabled = true;
		status.hidden = false;
		status.setAttribute('tabindex', '-1');
		status.focus();
	});
	window.addEventListener('pageshow', reset);
}());
