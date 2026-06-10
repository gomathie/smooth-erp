/* =============================================================================
   Legacy data-API -> Bootstrap 5 bridge
   -----------------------------------------------------------------------------
   Bootstrap 5 reacts only to data-bs-toggle / data-bs-target / data-bs-dismiss
   and removed the jQuery plugin API. The app's existing markup still uses the
   Bootstrap 3/4 attributes (data-toggle / data-target / data-dismiss) across
   ~255 modal & dropdown triggers. This bridge keeps them working under BS5:

     - delegates clicks on [data-toggle="modal"|"dropdown"] and
       [data-dismiss="modal"] to the Bootstrap 5 component API, and
     - restores $.fn.modal / $.fn.dropdown / $.fn.tab for programmatic calls
       (accounting.js, payments.js, organizations.php).

   Remove once all markup is converted to native data-bs-*. Load AFTER
   bootstrap.bundle.min.js (BS5) and jQuery.
   ============================================================================= */
(function () {
  'use strict';
  function bs() { return window.bootstrap; }

  document.addEventListener('click', function (e) {
    if (!bs()) { return; }

    var modalTrigger = e.target.closest('[data-toggle="modal"]');
    if (modalTrigger) {
      var sel = modalTrigger.getAttribute('data-target') || modalTrigger.getAttribute('href');
      if (sel && sel.charAt(0) === '#') {
        var modalEl = document.querySelector(sel);
        if (modalEl) { e.preventDefault(); bs().Modal.getOrCreateInstance(modalEl).show(); }
      }
      return;
    }

    var dismiss = e.target.closest('[data-dismiss="modal"]');
    if (dismiss) {
      var parent = dismiss.closest('.modal');
      if (parent) { e.preventDefault(); bs().Modal.getOrCreateInstance(parent).hide(); }
      return;
    }

    var dd = e.target.closest('[data-toggle="dropdown"]');
    if (dd) { e.preventDefault(); bs().Dropdown.getOrCreateInstance(dd).toggle(); }
  });

  if (window.jQuery) {
    var $ = window.jQuery;
    $.fn.modal = function (a) {
      return this.each(function () { if (!bs()) return; var i = bs().Modal.getOrCreateInstance(this);
        if (a === 'hide') i.hide(); else if (a === 'toggle') i.toggle(); else i.show(); });
    };
    $.fn.dropdown = function (a) {
      return this.each(function () { if (!bs()) return; var i = bs().Dropdown.getOrCreateInstance(this);
        if (a === 'show') i.show(); else if (a === 'hide') i.hide(); else i.toggle(); });
    };
    $.fn.tab = function () {
      return this.each(function () { if (!bs()) return; bs().Tab.getOrCreateInstance(this).show(); });
    };
  }
})();
