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

var allSkins = [
  'skin-blue','skin-blue-light','skin-black','skin-black-light',
  'skin-purple','skin-purple-light','skin-red','skin-red-light',
  'skin-green','skin-green-light','skin-yellow','skin-yellow-light'
];

// Style the swatches and mark the active one
(function initSwatches() {
  var activeSkin = $.grep(document.body.className.split(' '), function(c) {
    return c.indexOf('skin-') === 0;
  })[0] || 'skin-red-light';

  $('.theme-swatch').css({
    display: 'inline-block',
    width: '30px',
    height: '30px',
    borderRadius: '50%',
    cursor: 'pointer',
    transition: 'transform .15s, box-shadow .15s',
    boxSizing: 'border-box'
  });

  $('.theme-swatch[data-skin="' + activeSkin + '"]').css({
    boxShadow: '0 0 0 3px rgba(0,0,0,.5)',
    transform: 'scale(1.2)'
  });
})();

$(document).on('click', '.theme-swatch', function (e) {
  e.stopPropagation();
  var skin = $(this).data('skin');
  $('body').removeClass(allSkins.join(' ')).addClass(skin);
  $('.theme-swatch').css({ boxShadow: '', transform: '' });
  $(this).css({ boxShadow: '0 0 0 3px rgba(0,0,0,.5)', transform: 'scale(1.2)' });
  $.post('ajax/theme.ajax.php', { theme: skin });
});

/*=====  End of theme picker  ======*/


/*====================================
=            sidebar menu            =
====================================*/

$('.sidebar-menu').tree();

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