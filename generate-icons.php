<?php
/**
 * PWA Icon Generator
 * Generates all required app icons from a base image
 */

// Icon sizes needed
$sizes = [72, 96, 128, 144, 152, 192, 384, 512];

// Create base icon with SVG
$svg = '<?xml version="1.0" encoding="UTF-8"?>
<svg width="512" height="512" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <linearGradient id="grad1" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" style="stop-color:#1e40af;stop-opacity:1" />
      <stop offset="100%" style="stop-color:#1e3a8a;stop-opacity:1" />
    </linearGradient>
  </defs>
  <rect width="512" height="512" rx="80" fill="url(#grad1)"/>
  <circle cx="256" cy="200" r="120" fill="white"/>
  <text x="256" y="240" font-family="Arial, sans-serif" font-size="140" font-weight="bold" fill="#1e40af" text-anchor="middle">L</text>
  <text x="256" y="380" font-family="Arial, sans-serif" font-size="48" font-weight="600" fill="white" text-anchor="middle">LACOWE</text>
  <text x="256" y="430" font-family="Arial, sans-serif" font-size="32" fill="rgba(255,255,255,0.9)" text-anchor="middle">Welfare MIS</text>
</svg>';

// Ensure directory exists
if (!file_exists('assets/images')) {
    mkdir('assets/images', 0755, true);
}

// Save base SVG
file_put_contents('assets/images/icon-base.svg', $svg);

echo "Icon base created: assets/images/icon-base.svg\n";
echo "To generate PNG icons, use ImageMagick or an online converter:\n";
foreach ($sizes as $size) {
    echo "convert icon-base.svg -resize {$size}x{$size} icon-{$size}.png\n";
}
echo "\nOr use an online service like https://realfavicongenerator.net/\n";
?>
