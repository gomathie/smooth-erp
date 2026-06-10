<?php

/**
 * Lightweight internationalization (i18n).
 *
 * Translations use the English string as the key, so untranslated text simply
 * falls back to English — you can adopt t() incrementally without breaking
 * anything. Add a new language by dropping a lang/<code>.php file (returning a
 * [English => translation] array) and listing it in AVAILABLE.
 *
 * Locale resolution order: ?setlang= (persists) -> session -> cookie -> default.
 */
class I18n {

	/** Supported locales: code => native display name. */
	const AVAILABLE = [
		'en' => 'English',
		'fr' => 'Français',
	];

	const DEFAULT_LOCALE = 'en';

	private static string $locale = self::DEFAULT_LOCALE;
	private static array  $dict   = [];
	private static bool   $ready  = false;

	/** Resolve the active locale (call once per request, after session_start). */
	public static function init(): void {
		$locale = self::DEFAULT_LOCALE;

		if (isset($_GET['setlang']) && isset(self::AVAILABLE[$_GET['setlang']])) {
			$locale = $_GET['setlang'];
			$_SESSION['lang'] = $locale;
			setcookie('lang', $locale, time() + 60 * 60 * 24 * 365, '/');
		} elseif (!empty($_SESSION['lang']) && isset(self::AVAILABLE[$_SESSION['lang']])) {
			$locale = $_SESSION['lang'];
		} elseif (!empty($_COOKIE['lang']) && isset(self::AVAILABLE[$_COOKIE['lang']])) {
			$locale = $_COOKIE['lang'];
			$_SESSION['lang'] = $locale;
		}

		self::$locale = $locale;
		self::load();
		self::$ready = true;
	}

	private static function load(): void {
		$file = __DIR__ . '/../lang/' . self::$locale . '.php';
		$dict = is_file($file) ? require $file : [];
		self::$dict = is_array($dict) ? $dict : [];
	}

	public static function current(): string {
		return self::$locale;
	}

	public static function available(): array {
		return self::AVAILABLE;
	}

	/** Translate a string; falls back to $default, then the key itself. */
	public static function t(string $key, ?string $default = null): string {
		if (!self::$ready) {
			self::init();
		}
		return self::$dict[$key] ?? ($default ?? $key);
	}
}

/** Shorthand: echo t('Home'); */
function t(string $key, ?string $default = null): string {
	return I18n::t($key, $default);
}
