<?php
// Minimal stubs for Imagick classes to satisfy static analyzers (Intelephense).
// These are no-op definitions and won't be loaded at runtime when the real
// Imagick extension is present. They exist only to provide type hints.

if (!class_exists('Imagick')) {
    class Imagick {
        public function __construct() {}
        public function newImage($width, $height, $background, $format) {}
        public function setImageFormat($format) {}
        public function getImageBlob() { return '';} 
        public function drawImage($draw) {}
    }
}

if (!class_exists('ImagickPixel')) {
    class ImagickPixel {
        public function __construct($color = null) {}
    }
}

if (!class_exists('ImagickDraw')) {
    class ImagickDraw {
        public function __construct() {}
        public function setFillColor($color) {}
        public function rectangle($x1, $y1, $x2, $y2) {}
    }
}
