=== Tsugawa Magic Back to Top ===
Contributors: tsugawatv
Tags: back to top, scroll to top, smooth scroll, floating button, ui
Requires at least: 6.0
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A simple, lightweight, and beautiful Back to Top button. Features a pixel-perfect floating UI that seamlessly adapts to all screens.

== Description ==

**Tsugawa Magic Back to Top** provides a seamless and beautiful scrolling experience for your website visitors. Designed with a focus on responsive modern UI/UX and extreme performance, this plugin adds a highly customizable "Back to Top" button without bloating your site.

* **Official Plugin Page:** https://tsugawa.tv/plugins/magic-back-to-top/
* **GitHub Repository:** https://github.com/TsugawaTV/tsugawa-magic-back-to-top

No jQuery, no heavy libraries. Just pure Vanilla JS and modern CSS.

### Key Features
* **Universal Responsive UI:** Beautifully adapts from wide desktop monitors to small mobile screens. A flawless floating experience across all devices.
* **Pixel-Perfect Control:** Fine-tune the button's size and position (bottom-right or bottom-left) down to the pixel. Set independent sizes for PC and smartphone views.
* **Ultra Lightweight:** Built with Vanilla JS and CSS variables. Zero impact on your site's performance.
* **CSS-Based Mobile Hiding:** Option to hide the button on mobile devices using CSS media queries, which is friendly with page caching.
* **SVG Icons:** Choose from beautiful, crisp, and scalable SVG arrow icons.
* **Customizable Colors & Presets:** Change colors to perfectly match your brand, and save up to 3 of your custom color sets in "My Color" slots.
* **Adjustable Display Timing & Speed:** Control exactly when the button appears (scroll offset) and how fast the smooth scroll animation takes.
* **Exclude Pages:** Easily disable the button on specific pages (e.g., landing pages, front page) by entering page slugs or IDs.
* **Export for External LPs:** Generate and copy the standalone HTML/CSS/JS code to use your beautifully designed button on non-WordPress landing pages.

== Installation ==

1. Download the plugin .zip file from the official page or GitHub.
2. Go to your WordPress admin panel, navigate to `Plugins -> Add New Plugin`, and click `Upload Plugin` at the top.
3. Upload the downloaded .zip file and click `Install Now`.
4. Activate the plugin through the 'Plugins' menu in WordPress.
5. Go to `Settings -> Magic B2T` to customize your button's colors, position, and behavior.

== Frequently Asked Questions ==

= Does it work with caching plugins? =
Yes. Core features like "Hide on mobile" use CSS media queries to prevent conflicts with page caching.
However, if you change the button settings (like color or position) in the admin panel and don't see the changes on your site, please clear your caching plugin or server cache.

= Will it slow down my site? =
No. Tsugawa Magic Back to Top is engineered for maximum performance. It uses native `requestAnimationFrame` for smooth scrolling and avoids heavy libraries like jQuery.

== Screenshots ==

1. An intuitive settings panel featuring a real-time live preview, beautiful color customization, and 'My Colors' preset saving.
2. Pixel-perfect control over button size and position, plus a code export tool for external landing pages.

== Changelog ==

= 1.0.0 =
* Initial release.

== Credits ==
* Arrow icons provided by Lucide (MIT License).