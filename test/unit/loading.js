/* Copyright (C) 2026 iLLixM
 * SPDX-License-Identifier: GPL-3.0-or-later
 */
'use strict';
const fs = require('fs');
const vm = require('vm');
let passed = 0;
function check(value, label) { if (!value) { throw new Error(label); } passed++; }
class Element {
  constructor(value = '') { this.value = value; this.disabled = false; this.hidden = false; this.attrs = {}; this.listeners = {}; this.children = []; }
  addEventListener(name, fn) { this.listeners[name] = fn; }
  setAttribute(name, value) { this.attrs[name] = value; }
  removeAttribute(name) { delete this.attrs[name]; }
  getAttribute(name) { return this.attrs[name] || null; }
  appendChild(child) { child.parent = this; this.children.push(child); }
  querySelector() { return this.children.find((child) => child.attrs['data-msm-action']) || null; }
  remove() { this.parent.children = this.parent.children.filter((child) => child !== this); }
  focus() { this.focused = true; }
}
const form = new Element(); const button = new Element('book'); const status = new Element();
const windowListeners = {};
const context = {
  document: {
    activeElement: null,
    getElementById: (id) => ({'msm-confirm-form': form, 'msm-final-submit': button, 'msm-submit-status': status}[id]),
    createElement: () => new Element()
  },
  window: {addEventListener: (name, fn) => { windowListeners[name] = fn; }}
};
vm.runInNewContext(fs.readFileSync(__dirname + '/../../js/subscription.js', 'utf8'), context);
check(typeof form.listeners.submit === 'function' && typeof windowListeners.pageshow === 'function', 'handlers installed');
let prevented = false;
form.listeners.submit({submitter: new Element('edit'), preventDefault: () => { prevented = true; }});
check(!prevented && form.getAttribute('aria-busy') === null, 'edit submit unaffected');
form.listeners.submit({submitter: button, preventDefault: () => { prevented = true; }});
check(form.getAttribute('aria-busy') === 'true' && button.disabled && !status.hidden && status.focused, 'final submit exposes busy status and focus');
check(form.children.length === 1 && form.children[0].name === 'action' && form.children[0].value === 'book', 'disabled submitter replaced by hidden action');
prevented = false; form.listeners.submit({submitter: button, preventDefault: () => { prevented = true; }});
check(prevented, 'duplicate final submit blocked');
windowListeners.pageshow();
check(!button.disabled && status.hidden && form.getAttribute('aria-busy') === null && form.children.length === 0, 'BFCache pageshow resets loader');
console.log(passed + ' loading indicator assertions passed (synthetic DOM; no browser).');
