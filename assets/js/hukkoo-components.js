/**
 * Vanilla JS behavior layer — no jQuery, no build step. Everything hooks
 * off data-hk-* attributes via event delegation on document, so markup
 * rendered after page load (AJAX, dynamic components) works without
 * re-binding anything.
 */
(function () {
	'use strict';

	var FOCUSABLE = 'a[href], button:not([disabled]), textarea:not([disabled]), input:not([disabled]), select:not([disabled]), [tabindex]:not([tabindex="-1"])';

	/* -------------------------------------------------------------------
	 * Modal
	 * Markup contract:
	 *   <button data-hk-modal-open="my-modal">
	 *   <div id="my-modal" class="hk-modal" data-hk-modal>
	 *     <div class="hk-modal-panel" role="dialog" aria-modal="true">…
	 *       <button data-hk-modal-close>
	 * ---------------------------------------------------------------- */

	var lastFocusedBeforeModal = null;

	function openModal(modal) {
		if (!modal) return;
		lastFocusedBeforeModal = document.activeElement;
		modal.classList.add('hk-modal--open');
		modal.removeAttribute('hidden');

		var panel = modal.querySelector('.hk-modal-panel');
		var focusTarget = (panel && panel.querySelector(FOCUSABLE)) || panel;
		if (focusTarget) focusTarget.focus();

		document.addEventListener('keydown', onModalKeydown, true);
	}

	function closeModal(modal) {
		if (!modal) return;
		modal.classList.remove('hk-modal--open');
		modal.setAttribute('hidden', '');
		document.removeEventListener('keydown', onModalKeydown, true);
		if (lastFocusedBeforeModal) lastFocusedBeforeModal.focus();
	}

	function onModalKeydown(e) {
		var modal = document.querySelector('.hk-modal--open');
		if (!modal) return;

		if (e.key === 'Escape') {
			closeModal(modal);
			return;
		}

		if (e.key !== 'Tab') return;

		var panel = modal.querySelector('.hk-modal-panel') || modal;
		var focusable = Array.prototype.slice.call(panel.querySelectorAll(FOCUSABLE));
		if (!focusable.length) return;

		var first = focusable[0];
		var last = focusable[focusable.length - 1];

		if (e.shiftKey && document.activeElement === first) {
			e.preventDefault();
			last.focus();
		} else if (!e.shiftKey && document.activeElement === last) {
			e.preventDefault();
			first.focus();
		}
	}

	/* -------------------------------------------------------------------
	 * Tabs
	 * Markup contract:
	 *   <div data-hk-tabs>
	 *     <button data-hk-tab="panel-1" aria-selected="true">…
	 *     <div id="panel-1" data-hk-tab-panel>…
	 * ---------------------------------------------------------------- */

	function activateTab(tabsRoot, targetId) {
		var tabs = tabsRoot.querySelectorAll('[data-hk-tab]');
		var panels = tabsRoot.querySelectorAll('[data-hk-tab-panel]');

		tabs.forEach(function (tab) {
			var active = tab.getAttribute('data-hk-tab') === targetId;
			tab.setAttribute('aria-selected', active ? 'true' : 'false');
			tab.classList.toggle('hk-tab--active', active);
		});

		panels.forEach(function (panel) {
			var active = panel.id === targetId;
			panel.toggleAttribute('hidden', !active);
		});
	}

	/* -------------------------------------------------------------------
	 * Dropdown
	 * Markup contract:
	 *   <div data-hk-dropdown>
	 *     <button data-hk-dropdown-toggle aria-expanded="false">…
	 *     <div class="hk-dropdown-menu" data-hk-dropdown-menu hidden>…
	 * ---------------------------------------------------------------- */

	function closeAllDropdowns(except) {
		document.querySelectorAll('[data-hk-dropdown-menu]:not([hidden])').forEach(function (menu) {
			if (menu === except) return;
			menu.setAttribute('hidden', '');
			var root = menu.closest('[data-hk-dropdown]');
			var toggle = root && root.querySelector('[data-hk-dropdown-toggle]');
			if (toggle) toggle.setAttribute('aria-expanded', 'false');
		});
	}

	/* -------------------------------------------------------------------
	 * Select — a button+listbox dropdown standing in for a native
	 * <select>, since native <option> popups can't be styled or
	 * positioned by CSS. A hidden <input> carries the real value.
	 * Markup contract:
	 *   <div data-hk-dropdown>
	 *     <button data-hk-dropdown-toggle aria-haspopup="listbox">
	 *       <span class="hk-select-value">…
	 *     <input type="hidden" data-hk-select-value-input>
	 *     <ul data-hk-dropdown-menu role="listbox" hidden>
	 *       <li data-hk-select-option="value" role="option" tabindex="-1">…
	 * ---------------------------------------------------------------- */

	function selectOption(root, optionEl) {
		var hidden = root.querySelector('[data-hk-select-value-input]');
		var label = root.querySelector('.hk-select-value');

		if (hidden) {
			hidden.value = optionEl.getAttribute('data-hk-select-option');
			hidden.dispatchEvent(new Event('change', { bubbles: true }));
		}

		if (label) label.textContent = optionEl.textContent;

		root.querySelectorAll('[data-hk-select-option]').forEach(function (el) {
			el.setAttribute('aria-selected', el === optionEl ? 'true' : 'false');
		});
	}

	function focusSelectOption(root, direction) {
		// .hidden-filtered so Lookup's search doesn't leave keyboard nav
		// landing on an option its own filter just hid.
		var options = Array.prototype.slice.call(root.querySelectorAll('[data-hk-select-option]')).filter(function (el) {
			return !el.hidden;
		});
		if (!options.length) return;

		var current = options.indexOf(document.activeElement);
		var next = current === -1
			? (direction > 0 ? 0 : options.length - 1)
			: (current + direction + options.length) % options.length;

		options[next].focus();
	}

	/* -------------------------------------------------------------------
	 * Lookup — Select's listbox plus a filter-as-you-type search input,
	 * since a lookup's option list is typically much longer.
	 * Markup contract:
	 *   <div data-hk-dropdown-menu data-hk-lookup-panel hidden>
	 *     <input data-hk-lookup-search>
	 *     <ul role="listbox">
	 *       <li data-hk-select-option="value" data-hk-lookup-text="lowercased label">…
	 *     <p data-hk-lookup-empty hidden>No matches</p>
	 * ---------------------------------------------------------------- */

	function filterLookupOptions(panel, query) {
		var normalized = query.trim().toLowerCase();
		var anyVisible = false;

		panel.querySelectorAll('[data-hk-lookup-text]').forEach(function (option) {
			var match = option.getAttribute('data-hk-lookup-text').indexOf(normalized) !== -1;
			option.hidden = !match;
			if (match) anyVisible = true;
		});

		var empty = panel.querySelector('[data-hk-lookup-empty]');
		if (empty) empty.hidden = anyVisible;
	}

	/* -------------------------------------------------------------------
	 * Date — a button+calendar-panel widget standing in for a native
	 * <input type="date">, since its popup can't be styled or positioned
	 * by CSS either. Mirrors Select's hidden-input-carries-the-value
	 * approach. PHP renders the initial month; prev/next re-render the
	 * header/grid here rather than round-tripping to the server.
	 * Markup contract:
	 *   <div data-hk-dropdown>
	 *     <button data-hk-dropdown-toggle aria-haspopup="dialog">
	 *       <span class="hk-select-value">…
	 *     <input type="hidden" data-hk-date-value-input>
	 *     <div data-hk-dropdown-menu data-hk-date-panel role="dialog" hidden
	 *          data-hk-date-year data-hk-date-month data-hk-date-min data-hk-date-max>
	 *       <button data-hk-date-nav="prev">…<span data-hk-date-label>…<button data-hk-date-nav="next">
	 *       <div class="hk-date-weekdays">…
	 *       <div data-hk-date-grid>
	 *         <button data-hk-date-day="YYYY-MM-DD">…
	 * ---------------------------------------------------------------- */

	var WEEKDAY_LABELS = ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'];

	function pad2(n) {
		return n < 10 ? '0' + n : '' + n;
	}

	function isoDate(year, month, day) {
		return year + '-' + pad2(month) + '-' + pad2(day);
	}

	function daysInMonth(year, month) {
		return new Date(year, month, 0).getDate();
	}

	function dateDayCellHtml(year, month, day, outside, today, value, min, max) {
		var date = isoDate(year, month, day);
		var disabled = (min && date < min) || (max && date > max);
		var classes = 'hk-date-day'
			+ (outside ? ' hk-date-day--outside' : '')
			+ (date === today ? ' hk-date-day--today' : '')
			+ (date === value ? ' hk-date-day--selected' : '');

		return '<button type="button" class="' + classes + '" data-hk-date-day="' + date + '"'
			+ (disabled ? ' disabled' : '') + (date === value ? ' aria-selected="true"' : '') + '>' + day + '</button>';
	}

	function renderCalendar(panel, year, month, value, min, max) {
		var first = new Date(year, month - 1, 1);
		var startWeekday = first.getDay();
		var count = daysInMonth(year, month);
		var prevMonth = month === 1 ? 12 : month - 1;
		var prevYear = month === 1 ? year - 1 : year;
		var prevCount = daysInMonth(prevYear, prevMonth);
		var nextMonth = month === 12 ? 1 : month + 1;
		var nextYear = month === 12 ? year + 1 : year;
		var now = new Date();
		var today = isoDate(now.getFullYear(), now.getMonth() + 1, now.getDate());

		var cells = '';
		var i;
		for (i = startWeekday - 1; i >= 0; i--) {
			cells += dateDayCellHtml(prevYear, prevMonth, prevCount - i, true, today, value, min, max);
		}
		for (i = 1; i <= count; i++) {
			cells += dateDayCellHtml(year, month, i, false, today, value, min, max);
		}
		var trailing = (7 - ((startWeekday + count) % 7)) % 7;
		for (i = 1; i <= trailing; i++) {
			cells += dateDayCellHtml(nextYear, nextMonth, i, true, today, value, min, max);
		}

		var label = panel.querySelector('[data-hk-date-label]');
		var weekdays = panel.querySelector('.hk-date-weekdays');
		var grid = panel.querySelector('[data-hk-date-grid]');

		if (label) {
			label.textContent = first.toLocaleDateString(undefined, { month: 'long', year: 'numeric' });
		}
		if (weekdays) {
			weekdays.innerHTML = WEEKDAY_LABELS.map(function (l) {
				return '<span class="hk-date-weekday">' + l + '</span>';
			}).join('');
		}
		if (grid) grid.innerHTML = cells;

		panel.setAttribute('data-hk-date-year', year);
		panel.setAttribute('data-hk-date-month', month);
	}

	function selectDate(root, dateStr) {
		var panel = root.querySelector('[data-hk-date-panel]');
		var isCombo = panel && panel.hasAttribute('data-hk-datetime-combo');

		if (panel) {
			panel.setAttribute('data-hk-date-value', dateStr);
			renderCalendar(
				panel,
				parseInt(panel.getAttribute('data-hk-date-year'), 10),
				parseInt(panel.getAttribute('data-hk-date-month'), 10),
				dateStr,
				panel.getAttribute('data-hk-date-min'),
				panel.getAttribute('data-hk-date-max')
			);
		}

		if (isCombo) {
			syncComboValue(root, panel);
			return;
		}

		var hidden = root.querySelector('[data-hk-date-value-input]');
		var label = root.querySelector('.hk-select-value');

		if (hidden) {
			hidden.value = dateStr;
			hidden.dispatchEvent(new Event('change', { bubbles: true }));
		}

		if (label) {
			var d = new Date(dateStr + 'T00:00:00');
			label.textContent = d.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
		}
	}

	function focusDateDay(panel, offset) {
		var days = Array.prototype.slice.call(panel.querySelectorAll('[data-hk-date-day]:not([disabled])'));
		if (!days.length) return;

		var current = days.indexOf(document.activeElement);
		var next = current === -1 ? 0 : current + offset;
		next = Math.max(0, Math.min(days.length - 1, next));
		days[next].focus();
	}

	function navigateCalendar(panel, direction) {
		var year = parseInt(panel.getAttribute('data-hk-date-year'), 10);
		var month = parseInt(panel.getAttribute('data-hk-date-month'), 10) + direction;

		if (month < 1) {
			month = 12;
			year -= 1;
		} else if (month > 12) {
			month = 1;
			year += 1;
		}

		renderCalendar(
			panel,
			year,
			month,
			panel.getAttribute('data-hk-date-value'),
			panel.getAttribute('data-hk-date-min'),
			panel.getAttribute('data-hk-date-max')
		);
	}

	/* -------------------------------------------------------------------
	 * Time — an hour/minute column widget standing in for a native
	 * <input type="time">'s popup, same reasoning as Date. Also used
	 * inside DateTime's combined panel (data-hk-datetime-combo), where
	 * picking a time updates only the time part of the composite value
	 * instead of the whole thing.
	 * Markup contract:
	 *   <div data-hk-dropdown>
	 *     <button data-hk-dropdown-toggle aria-haspopup="dialog">
	 *       <span class="hk-select-value">…
	 *     <input type="hidden" data-hk-time-value-input>
	 *     <div data-hk-dropdown-menu data-hk-time-panel role="dialog" hidden
	 *          data-hk-time-hour data-hk-time-minute>
	 *       <div data-hk-time-part="hour"><button data-hk-time-option="00">…
	 *       <div data-hk-time-part="minute"><button data-hk-time-option="00">…
	 *       <button data-hk-time-done>Done</button>
	 * ---------------------------------------------------------------- */

	function markSelectedTimeOption(panel, part, value) {
		var column = panel.querySelector('[data-hk-time-part="' + part + '"]');
		if (!column) return;

		column.querySelectorAll('[data-hk-time-option]').forEach(function (el) {
			var selected = el.getAttribute('data-hk-time-option') === value;
			el.classList.toggle('hk-time-option--selected', selected);
			el.setAttribute('aria-selected', selected ? 'true' : 'false');
		});
	}

	function selectTimePart(root, part, value) {
		var timePanel = root.querySelector('[data-hk-time-panel]');
		if (!timePanel) return;

		var hour = timePanel.getAttribute('data-hk-time-hour') || '00';
		var minute = timePanel.getAttribute('data-hk-time-minute') || '00';
		if (part === 'hour') hour = value; else minute = value;

		timePanel.setAttribute('data-hk-time-hour', hour);
		timePanel.setAttribute('data-hk-time-minute', minute);
		markSelectedTimeOption(timePanel, 'hour', hour);
		markSelectedTimeOption(timePanel, 'minute', minute);

		if (timePanel.hasAttribute('data-hk-datetime-combo')) {
			syncComboValue(root, timePanel);
			return;
		}

		var hidden = root.querySelector('[data-hk-time-value-input]');
		var label = root.querySelector('.hk-select-value');
		var timeStr = hour + ':' + minute;

		if (hidden) {
			hidden.value = timeStr;
			hidden.dispatchEvent(new Event('change', { bubbles: true }));
		}
		if (label) label.textContent = timeStr;
	}

	/* -------------------------------------------------------------------
	 * DateTime — Date's calendar + Time's columns in one panel. Selecting
	 * a day or a time updates only that part; the composite 'Y-m-dTH:i'
	 * value/label only commits once a date is present (defaults the time
	 * to whatever the panel already shows, 00:00 unless changed).
	 * ---------------------------------------------------------------- */

	function syncComboValue(root, panel) {
		var date = panel.getAttribute('data-hk-date-value');
		if (!date) return;

		var hour = panel.getAttribute('data-hk-time-hour') || '00';
		var minute = panel.getAttribute('data-hk-time-minute') || '00';
		var hidden = root.querySelector('[data-hk-date-value-input]');
		var label = root.querySelector('.hk-select-value');
		var combined = date + 'T' + hour + ':' + minute;

		if (hidden) {
			hidden.value = combined;
			hidden.dispatchEvent(new Event('change', { bubbles: true }));
		}
		if (label) {
			var d = new Date(date + 'T00:00:00');
			label.textContent = d.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' }) + ', ' + hour + ':' + minute;
		}
	}

	/* -------------------------------------------------------------------
	 * Toast — a single transient notification container. Call
	 * window.hkToast(message, color, id, title) from anywhere to show
	 * it; it auto-dismisses on its own (or on clicking its close button).
	 * Markup contract:
	 *   <div id="hk-toast" class="hk-toast" role="status" hidden></div>
	 * ---------------------------------------------------------------- */

	var toastDismissTimer = null;

	var TOAST_ICONS = {
		success: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7"/></svg>',
		error: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6 6 18"/></svg>',
		warning: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8.5v4.25m0 3.25h.01"/><path stroke-linecap="round" stroke-linejoin="round" d="M10.44 4.5 2.62 18a1.3 1.3 0 0 0 1.12 1.95h16.52A1.3 1.3 0 0 0 21.38 18L13.56 4.5a1.3 1.3 0 0 0-2.24 0Z"/></svg>',
		info: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15.5v-4.25m0-3.25h.01"/><circle cx="12" cy="12" r="9"/></svg>'
	};

	var TOAST_CLOSE_ICON = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M6 6l12 12M18 6 6 18"/></svg>';

	var TOAST_TITLES = {
		neutral: 'Notice',
		success: 'Success',
		error: 'Error',
		warning: 'Warning',
		info: 'Info'
	};

	function dismissToast(toast) {
		if (!toast) return;
		clearTimeout(toastDismissTimer);
		toast.classList.remove('hk-toast--visible');
		setTimeout(function () {
			toast.setAttribute('hidden', '');
		}, 200);
	}

	window.hkToast = function (message, color, id, title) {
		var toast = document.getElementById(id || 'hk-toast');
		if (!toast) return;

		clearTimeout(toastDismissTimer);
		toast.classList.remove('hk-toast--visible');

		// Icon/title come from a fixed lookup (safe to inline); the
		// caller-supplied message/title are set via textContent below so
		// arbitrary strings can never inject markup here.
		toast.className = 'hk-toast' + (color ? ' hk-toast--' + color : '');
		toast.innerHTML =
			'<span class="hk-toast-icon" aria-hidden="true">' + (TOAST_ICONS[color] || TOAST_ICONS.info) + '</span>' +
			'<span class="hk-toast-body">' +
				'<span class="hk-toast-title"></span>' +
				'<span class="hk-toast-message"></span>' +
			'</span>' +
			'<button type="button" class="hk-toast-close" aria-label="Dismiss">' + TOAST_CLOSE_ICON + '</button>';

		toast.querySelector('.hk-toast-title').textContent = title || TOAST_TITLES[color] || TOAST_TITLES.neutral;
		toast.querySelector('.hk-toast-message').textContent = message;
		toast.removeAttribute('hidden');

		// Separate frame so removing [hidden] and adding the visible class
		// don't land in the same paint — otherwise there's nothing for the
		// opacity/transform transition to animate from.
		requestAnimationFrame(function () {
			toast.classList.add('hk-toast--visible');
		});

		toastDismissTimer = setTimeout(function () {
			dismissToast(toast);
		}, 3200);
	};

	/* -------------------------------------------------------------------
	 * Disclosure — generic show/hide toggle (used by the Showcase
	 * gallery's "Show code" panels, but generic enough for any
	 * collapsible block).
	 * Markup contract:
	 *   <button data-hk-disclosure-toggle="panel-id" aria-expanded="false">…
	 *   <div id="panel-id" hidden>…
	 * ---------------------------------------------------------------- */

	function toggleDisclosure(trigger) {
		var panel = document.getElementById(trigger.getAttribute('data-hk-disclosure-toggle'));
		if (!panel) return;

		var expanded = trigger.getAttribute('aria-expanded') === 'true';
		panel.toggleAttribute('hidden', expanded);
		trigger.setAttribute('aria-expanded', expanded ? 'false' : 'true');

		var labelAttr = expanded ? 'data-hk-label-show' : 'data-hk-label-hide';
		var label = trigger.getAttribute(labelAttr);
		if (label) trigger.textContent = label;
	}

	/* -------------------------------------------------------------------
	 * Delegated listeners
	 * ---------------------------------------------------------------- */

	document.addEventListener('click', function (e) {
		var toastClose = e.target.closest('.hk-toast-close');
		if (toastClose) {
			dismissToast(toastClose.closest('.hk-toast'));
			return;
		}

		var disclosureTrigger = e.target.closest('[data-hk-disclosure-toggle]');
		if (disclosureTrigger) {
			toggleDisclosure(disclosureTrigger);
			return;
		}

		var openTrigger = e.target.closest('[data-hk-modal-open]');
		if (openTrigger) {
			var id = openTrigger.getAttribute('data-hk-modal-open');
			openModal(document.getElementById(id));
			return;
		}

		var closeTrigger = e.target.closest('[data-hk-modal-close]');
		if (closeTrigger) {
			closeModal(closeTrigger.closest('[data-hk-modal]'));
			return;
		}

		var backdrop = e.target.closest('[data-hk-modal]');
		if (backdrop && e.target === backdrop) {
			closeModal(backdrop);
			return;
		}

		var tabTrigger = e.target.closest('[data-hk-tab]');
		if (tabTrigger) {
			var tabsRoot = tabTrigger.closest('[data-hk-tabs]');
			if (tabsRoot) activateTab(tabsRoot, tabTrigger.getAttribute('data-hk-tab'));
			return;
		}

		var selectOptionTrigger = e.target.closest('[data-hk-select-option]');
		if (selectOptionTrigger) {
			var selectRoot = selectOptionTrigger.closest('[data-hk-dropdown]');
			if (selectRoot) {
				selectOption(selectRoot, selectOptionTrigger);
				closeAllDropdowns();
				var selectToggle = selectRoot.querySelector('[data-hk-dropdown-toggle]');
				if (selectToggle) selectToggle.focus();
			}
			return;
		}

		var dateNav = e.target.closest('[data-hk-date-nav]');
		if (dateNav) {
			var navPanel = dateNav.closest('[data-hk-date-panel]');
			if (navPanel) navigateCalendar(navPanel, dateNav.getAttribute('data-hk-date-nav') === 'next' ? 1 : -1);
			return;
		}

		var dateDay = e.target.closest('[data-hk-date-day]');
		if (dateDay) {
			if (dateDay.disabled) return;

			var dateRoot = dateDay.closest('[data-hk-dropdown]');
			if (dateRoot) {
				selectDate(dateRoot, dateDay.getAttribute('data-hk-date-day'));

				var datePanelEl = dateRoot.querySelector('[data-hk-date-panel]');
				if (!(datePanelEl && datePanelEl.hasAttribute('data-hk-datetime-combo'))) {
					closeAllDropdowns();
					var dateToggle = dateRoot.querySelector('[data-hk-dropdown-toggle]');
					if (dateToggle) dateToggle.focus();
				}
			}
			return;
		}

		var timeOption = e.target.closest('[data-hk-time-option]');
		if (timeOption) {
			var timeRoot = timeOption.closest('[data-hk-dropdown]');
			var timePartEl = timeOption.closest('[data-hk-time-part]');
			if (timeRoot && timePartEl) {
				selectTimePart(timeRoot, timePartEl.getAttribute('data-hk-time-part'), timeOption.getAttribute('data-hk-time-option'));
			}
			return;
		}

		var timeDone = e.target.closest('[data-hk-time-done]');
		if (timeDone) {
			closeAllDropdowns();
			var doneRoot = timeDone.closest('[data-hk-dropdown]');
			var doneToggle = doneRoot && doneRoot.querySelector('[data-hk-dropdown-toggle]');
			if (doneToggle) doneToggle.focus();
			return;
		}

		var dropdownToggle = e.target.closest('[data-hk-dropdown-toggle]');
		if (dropdownToggle) {
			if (dropdownToggle.disabled) return;

			var dropdownRoot = dropdownToggle.closest('[data-hk-dropdown]');
			var menu = dropdownRoot && dropdownRoot.querySelector('[data-hk-dropdown-menu]');
			if (!menu) return;
			var isOpen = !menu.hasAttribute('hidden');
			closeAllDropdowns();
			if (!isOpen) {
				menu.removeAttribute('hidden');
				dropdownToggle.setAttribute('aria-expanded', 'true');

				if (menu.getAttribute('role') === 'listbox') {
					var current = menu.querySelector('[aria-selected="true"]') || menu.querySelector('[data-hk-select-option]');
					if (current) current.focus();
				} else if (menu.getAttribute('role') === 'dialog' && menu.hasAttribute('data-hk-date-panel')) {
					var currentDay = menu.querySelector('[aria-selected="true"]')
						|| menu.querySelector('.hk-date-day--today')
						|| menu.querySelector('[data-hk-date-day]:not([disabled])');
					if (currentDay) currentDay.focus();
				} else if (menu.getAttribute('role') === 'dialog' && menu.hasAttribute('data-hk-time-panel')) {
					var currentTimeOption = menu.querySelector('.hk-time-option--selected') || menu.querySelector('[data-hk-time-option]');
					if (currentTimeOption) currentTimeOption.focus();
				} else if (menu.hasAttribute('data-hk-lookup-panel')) {
					var search = menu.querySelector('[data-hk-lookup-search]');
					if (search) {
						search.value = '';
						filterLookupOptions(menu, '');
						search.focus();
					}
				}
			}
			return;
		}

		if (!e.target.closest('[data-hk-dropdown]')) {
			closeAllDropdowns();
		}
	});

	document.addEventListener('keydown', function (e) {
		if (e.key === 'Escape') closeAllDropdowns();

		// Matches Select (role="listbox" on the dropdown-menu itself) and
		// Lookup (role="listbox" on a <ul> nested inside the panel, since
		// the panel itself also holds the search input).
		var openListbox = document.querySelector(
			'[data-hk-dropdown-menu][role="listbox"]:not([hidden]), [data-hk-dropdown-menu]:not([hidden]) [role="listbox"]'
		);
		if (openListbox) {
			var listboxRoot = openListbox.closest('[data-hk-dropdown]');
			if (listboxRoot && listboxRoot.contains(document.activeElement)) {
				if (e.key === 'ArrowDown') {
					e.preventDefault();
					focusSelectOption(listboxRoot, 1);
				} else if (e.key === 'ArrowUp') {
					e.preventDefault();
					focusSelectOption(listboxRoot, -1);
				} else if ((e.key === 'Enter' || e.key === ' ') && document.activeElement.hasAttribute('data-hk-select-option')) {
					e.preventDefault();
					selectOption(listboxRoot, document.activeElement);
					closeAllDropdowns();
					var toggle = listboxRoot.querySelector('[data-hk-dropdown-toggle]');
					if (toggle) toggle.focus();
				} else if (e.key === 'Enter' && document.activeElement.hasAttribute('data-hk-lookup-search')) {
					// Don't let Enter fall through to an implicit form submit
					// while the user is still filtering — arrow down + Enter
					// on an option is how a choice actually gets made.
					e.preventDefault();
				}
			}
		}

		var openDatePanel = document.querySelector('[data-hk-date-panel]:not([hidden])');
		if (openDatePanel && document.activeElement.hasAttribute('data-hk-date-day')) {
			var arrowOffsets = { ArrowRight: 1, ArrowLeft: -1, ArrowDown: 7, ArrowUp: -7 };
			if (arrowOffsets[e.key] !== undefined) {
				e.preventDefault();
				focusDateDay(openDatePanel, arrowOffsets[e.key]);
			}
		}

		var openTimePanel = document.querySelector('[data-hk-time-panel]:not([hidden])');
		if (openTimePanel && document.activeElement.hasAttribute('data-hk-time-option') && (e.key === 'ArrowDown' || e.key === 'ArrowUp')) {
			e.preventDefault();
			var timeColumn = document.activeElement.closest('[data-hk-time-part]');
			if (timeColumn) {
				var timeOptions = Array.prototype.slice.call(timeColumn.querySelectorAll('[data-hk-time-option]'));
				var currentIndex = timeOptions.indexOf(document.activeElement);
				var nextIndex = Math.max(0, Math.min(timeOptions.length - 1, currentIndex + (e.key === 'ArrowDown' ? 1 : -1)));
				timeOptions[nextIndex].focus();
			}
		}
	});

	document.addEventListener('input', function (e) {
		var search = e.target.closest('[data-hk-lookup-search]');
		if (search) {
			var panel = search.closest('[data-hk-lookup-panel]');
			if (panel) filterLookupOptions(panel, search.value);
		}
	});
})();
