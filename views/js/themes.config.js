/* =============================================================
   POS Theme Configuration
   -------------------------------------------------------------
   THIS is the only file you need to edit to change colours.

   Each theme has five values:
     label        — display name shown in the picker
     swatch       — circle colour shown in the picker
     primary      — top nav-bar + active-item accent
     primaryDark  — logo bar (≈ 10 % darker than primary)
     sidebar      — sidebar panel background
     sidebarText  — sidebar link / icon text
     sidebarHover — sidebar item hover / active tint

   To add a new theme: copy any block, give it a unique key,
   change the hex codes, then refresh — done.
   ============================================================= */

var POS_THEMES = {

  /* ── Dark-sidebar themes ──────────────────────────────────── */

  'blue': {
    label: 'Blue',
    swatch: '#3c8dbc',
    primary:      '#3c8dbc',
    primaryDark:  '#367fa9',
    sidebar:      '#222d32',
    sidebarText:  '#b8c7ce',
    sidebarHover: 'rgba(0,0,0,0.20)',
  },

  'red': {
    label: 'Red',
    swatch: '#dd4b39',
    primary:      '#dd4b39',
    primaryDark:  '#d73925',
    sidebar:      '#222d32',
    sidebarText:  '#b8c7ce',
    sidebarHover: 'rgba(0,0,0,0.20)',
  },

  'green': {
    label: 'Green',
    swatch: '#00a65a',
    primary:      '#00a65a',
    primaryDark:  '#008d4c',
    sidebar:      '#222d32',
    sidebarText:  '#b8c7ce',
    sidebarHover: 'rgba(0,0,0,0.20)',
  },

  'purple': {
    label: 'Purple',
    swatch: '#605ca8',
    primary:      '#605ca8',
    primaryDark:  '#555299',
    sidebar:      '#222d32',
    sidebarText:  '#b8c7ce',
    sidebarHover: 'rgba(0,0,0,0.20)',
  },

  'yellow': {
    label: 'Yellow',
    swatch: '#f39c12',
    primary:      '#f39c12',
    primaryDark:  '#db8b0b',
    sidebar:      '#222d32',
    sidebarText:  '#b8c7ce',
    sidebarHover: 'rgba(0,0,0,0.20)',
  },

  'black': {
    label: 'Black',
    swatch: '#444444',
    primary:      '#2b3539',
    primaryDark:  '#1e282c',
    sidebar:      '#222d32',
    sidebarText:  '#b8c7ce',
    sidebarHover: 'rgba(0,0,0,0.20)',
  },

  /* ── Light-sidebar themes (white sidebar) ─────────────────── */

  'blue-light': {
    label: 'Blue Light',
    swatch: '#3c8dbc', swatchLight: true,
    primary:      '#3c8dbc',
    primaryDark:  '#367fa9',
    sidebar:      '#f9fafc',
    sidebarText:  '#555555',
    sidebarHover: 'rgba(0,0,0,0.07)',
  },

  'red-light': {
    label: 'Red Light',
    swatch: '#dd4b39', swatchLight: true,
    primary:      '#dd4b39',
    primaryDark:  '#d73925',
    sidebar:      '#f9fafc',
    sidebarText:  '#555555',
    sidebarHover: 'rgba(0,0,0,0.07)',
  },

  'green-light': {
    label: 'Green Light',
    swatch: '#00a65a', swatchLight: true,
    primary:      '#00a65a',
    primaryDark:  '#008d4c',
    sidebar:      '#f9fafc',
    sidebarText:  '#555555',
    sidebarHover: 'rgba(0,0,0,0.07)',
  },

  'purple-light': {
    label: 'Purple Light',
    swatch: '#605ca8', swatchLight: true,
    primary:      '#605ca8',
    primaryDark:  '#555299',
    sidebar:      '#f9fafc',
    sidebarText:  '#555555',
    sidebarHover: 'rgba(0,0,0,0.07)',
  },

  'yellow-light': {
    label: 'Yellow Light',
    swatch: '#f39c12', swatchLight: true,
    primary:      '#f39c12',
    primaryDark:  '#db8b0b',
    sidebar:      '#f9fafc',
    sidebarText:  '#555555',
    sidebarHover: 'rgba(0,0,0,0.07)',
  },

  'black-light': {
    label: 'Black Light',
    swatch: '#444444', swatchLight: true,
    primary:      '#2b3539',
    primaryDark:  '#1e282c',
    sidebar:      '#f9fafc',
    sidebarText:  '#555555',
    sidebarHover: 'rgba(0,0,0,0.07)',
  },

};

/* Default key used when no preference is stored yet */
var POS_DEFAULT_THEME = 'red-light';

/* -----------------------------------------------------------
   Internal helpers — no need to edit below this line
   ----------------------------------------------------------- */

function posApplyTheme(key) {
  var t = POS_THEMES[key] || POS_THEMES[POS_DEFAULT_THEME];
  var r = document.documentElement;
  r.style.setProperty('--pos-primary',       t.primary);
  r.style.setProperty('--pos-primary-dark',  t.primaryDark);
  r.style.setProperty('--pos-sidebar-bg',    t.sidebar);
  r.style.setProperty('--pos-sidebar-text',  t.sidebarText);
  r.style.setProperty('--pos-sidebar-hover', t.sidebarHover);
}

/* Run synchronously in <head> so colours are set before first paint */
(function () {
  var meta = document.querySelector('meta[name="pos-theme"]');
  posApplyTheme(meta ? meta.getAttribute('content') : POS_DEFAULT_THEME);
}());
