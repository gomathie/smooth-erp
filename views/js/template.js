/*====================================
=       CSRF — send token with every AJAX request
====================================*/

$.ajaxSetup({
  headers: { 'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content') }
});

/*=====  End of CSRF setup  ======*/


/*====================================
=            theme picker            =
====================================*/

// POS_THEMES is defined in themes.config.js (loaded before this file)

(function buildSwatches() {
  var $container = $('#theme-swatches');
  if (!$container.length) return;

  var activeKey = $('meta[name="pos-theme"]').attr('content') || 'red-light';

  $.each(POS_THEMES, function (key, t) {
    var border = t.swatchLight
      ? '2px solid #cccccc'
      : '2px solid transparent';
    var shadow = (key === activeKey)
      ? '0 0 0 3px rgba(0,0,0,0.45)'
      : '';
    var scale  = (key === activeKey) ? 'scale(1.2)' : '';

    var $s = $('<span></span>')
      .attr('data-theme-key', key)
      .attr('title', t.label)
      .css({
        display:         'inline-block',
        width:           '30px',
        height:          '30px',
        borderRadius:    '50%',
        cursor:          'pointer',
        background:      t.swatch,
        border:          border,
        boxShadow:       shadow,
        transform:       scale,
        transition:      'transform .15s, box-shadow .15s',
        boxSizing:       'border-box',
      });

    $container.append($s);
  });
}());

$(document).on('click', '[data-theme-key]', function (e) {
  e.stopPropagation();
  var key = $(this).data('theme-key');
  if (!POS_THEMES[key]) return;

  // Apply CSS variables immediately
  posApplyTheme(key);

  // Update active ring
  $('[data-theme-key]').css({ boxShadow: '', transform: '' });
  $(this).css({ boxShadow: '0 0 0 3px rgba(0,0,0,0.45)', transform: 'scale(1.2)' });

  // Persist to server
  $.post('ajax/theme.ajax.php', { theme: key });
});

/*=====  End of theme picker  ======*/


/*====================================
=            sidebar menu            =
====================================*/

// AdminLTE 4 handles the sidebar treeview itself via data-lte-toggle="treeview".
// (The old AdminLTE 2 jQuery $('.sidebar-menu').tree() call no longer exists and
//  would throw, halting the rest of this file.)

/*=====  End of sidebar menu  ======*/


/*=================================
=            datatable            =
=================================*/

$('.tables').dataTable();

/*=====  End of datatable  ======*/

/*=============================================
 //iCheck for checkbox and radio inputs
=============================================*/

$('input[type="checkbox"].minimal, input[type="radio"].minimal').iCheck({
  checkboxClass: 'icheckbox_minimal-blue',
  radioClass   : 'iradio_minimal-blue'
})



/*=================================
=            inputmask            =
=================================*/

//Datemask dd/mm/yyyy
$('#datemask').inputmask('dd/mm/yyyy', { 'placeholder': 'dd/mm/yyyy' })
//Datemask2 mm/dd/yyyy
$('#datemask2').inputmask('mm/dd/yyyy', { 'placeholder': 'mm/dd/yyyy' })
//Money Euro
$('[data-mask]').inputmask()


/*=============================================
FIXING HIDDEN BUTTONS IN THE BACKEND	
=============================================*/

if(window.matchMedia("(max-width:767px)").matches){
	
	$("body").removeClass('sidebar-collapse');

}else{

	$("body").addClass('sidebar-collapse');
}