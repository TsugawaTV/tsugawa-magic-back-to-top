=== Tsugawa Magic Back to Top ===
Contributors: tsugawatv
Tags: back to top, scroll to top, smooth scroll, floating button, ui
Requires at least: 6.0
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 1.0.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A simple, lightweight, and beautiful Back to Top button. 100% Free & Commercial Use Allowed. / シンプルで軽量、美しい「トップへ戻る」ボタン。完全無料・商用利用可能。

== Description ==

**[English]**
**Tsugawa Magic Back to Top** provides a seamless and beautiful scrolling experience for your website visitors. Designed with a focus on responsive modern UI/UX and extreme performance, this plugin adds a highly customizable "Back to Top" button without bloating your site.

* **Official Plugin Page:** https://tsugawa.tv/plugins/magic-back-to-top/
* **GitHub Repository:** https://github.com/TsugawaTV/tsugawa-magic-back-to-top
* **100% Free & Open Source:** Released under the GPLv2 license. Free for both personal and commercial projects.

No heavy libraries. Built with pure Vanilla JS and modern CSS.

**Overview**
1. An intuitive settings panel featuring a real-time live preview, beautiful color customization, and 'My Colors' preset saving.
2. Pixel-perfect control over button size and position, plus a code export tool for external landing pages.

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

---

**[日本語]**
**Tsugawa Magic Back to Top** は、サイト訪問者にシームレスで美しいスクロール体験を提供します。モダンでレスポンシブなUI/UXと極限のパフォーマンスに焦点を当てて設計されており、サイトを重くすることなく、高度にカスタマイズ可能な「トップへ戻る」ボタンを追加します。

* **公式プラグインページ:** https://tsugawa.tv/plugins/magic-back-to-top/
* **GitHub リポジトリ:** https://github.com/TsugawaTV/tsugawa-magic-back-to-top
* **完全無料・商用利用可能:** GPLv2ライセンスに基づき、個人・商用を問わずすべてのサイトで安心して無料でご利用いただけます。

重いライブラリは一切使用していません。純粋なVanilla JSとモダンなCSSのみで構築されています。

**プラグインの概要**
1. リアルタイムのライブプレビュー、美しいカラーカスタマイズ、「My Colors」プリセット保存機能を備えた直感的な設定パネル。
2. ボタンのサイズと位置のピクセル単位の制御、外部ランディングページ用のコードエクスポートツールを搭載。

### 主な機能
* **完全レスポンシブUI:** 幅広いPCモニターから小さなスマホ画面まで美しく適応。すべてのデバイスで完璧なフローティング体験を提供します。
* **ピクセルパーフェクトな制御:** ボタンのサイズと位置（右下または左下）を1ピクセル単位で微調整可能。PCとスマホで独立したサイズを設定できます。
* **超軽量設計:** Vanilla JSとCSS変数で構築。サイトのパフォーマンスへの影響はゼロです。
* **CSSベースのモバイル非表示機能:** ページキャッシュと競合しないCSSメディアクエリを使用して、モバイル端末でボタンを非表示にするオプションを搭載。
* **SVGアイコン:** 美しく鮮明でスケーラブルなSVGの矢印アイコンから選択可能です。
* **カスタマイズ可能なカラーとプリセット:** ブランドに合わせて色を自由に変更し、「My Color」スロットに最大3つのカスタムカラーセットを保存できます。
* **表示タイミングと速度の調整:** ボタンが表示されるタイミング（スクロール量）と、スムーズスクロールのアニメーション速度を正確に制御します。
* **特定ページの除外:** スラッグやIDを入力するだけで、特定のページ（ランディングページやフロントページなど）でボタンを簡単に無効化できます。
* **外部LP用のエクスポート機能:** WordPress以外のランディングページでも美しくデザインされたボタンを使用できるよう、独立したHTML/CSS/JSコードを生成してコピーできます。

== Installation ==

**[English]**
1. Download the plugin .zip file from the official page or GitHub.
2. Go to your WordPress admin panel, navigate to `Plugins -> Add New Plugin`, and click `Upload Plugin` at the top.
3. Upload the downloaded .zip file and click `Install Now`.
4. Activate the plugin through the 'Plugins' menu in WordPress.
5. Go to `Settings -> Magic B2T` to customize your button's colors, position, and behavior.

**[日本語]**
1. 公式ページまたはGitHubからプラグインの.zipファイルをダウンロードします。
2. WordPress管理画面の `プラグイン -> 新規追加` へ移動し、上部の `プラグインのアップロード` をクリックします。
3. ダウンロードした.zipファイルを選択し、`今すぐインストール` をクリックします。
4. WordPressの「プラグイン」メニューからプラグインを有効化します。
5. `設定 -> Magic B2T` へ移動し、ボタンの色、位置、動作をカスタマイズしてください。

== Frequently Asked Questions ==

= Does it work with caching plugins? (キャッシュプラグインと連携動作しますか？) =
**[EN]** Yes. Core features like "Hide on mobile" use CSS media queries to prevent conflicts with page caching. However, if you change the button settings (like color or position) in the admin panel and don't see the changes on your site, please clear your caching plugin or server cache.
**[JA]** はい。「モバイルで非表示」などのコア機能は、ページキャッシュとの競合を防ぐためにCSSメディアクエリを使用しています。ただし、管理画面でボタンの設定（色や位置など）を変更してもサイトに反映されない場合は、キャッシュプラグインまたはサーバーのキャッシュをクリアしてください。

= Will it slow down my site? (サイトの表示速度は遅くなりますか？) =
**[EN]** No. Tsugawa Magic Back to Top is engineered for maximum performance. It uses native `requestAnimationFrame` for smooth scrolling and avoids heavy libraries.
**[JA]** いいえ。Tsugawa Magic Back to Topは最高のパフォーマンスを発揮するように設計されています。スムーズなスクロールにはネイティブの `requestAnimationFrame` を使用し、無駄に重いライブラリは一切排除しています。

== Support & Bug Reports ==

**[English]**
If you find any bugs or have feature requests, please open an issue on our [GitHub Issues](https://github.com/TsugawaTV/tsugawa-magic-back-to-top/issues). Your feedback is highly appreciated!

**[日本語]**
バグの報告や機能改善のご要望がございましたら、GitHubリポジトリの [Issues](https://github.com/TsugawaTV/tsugawa-magic-back-to-top/issues) よりお寄せください。皆様からのフィードバックを心よりお待ちしております。

== Changelog ==

= 1.0.3 =
* Update: README.md completely redesigned with visual assets and feature highlights. (README.mdのリニューアル)

= 1.0.2 =
* Added bilingual support (English/Japanese). (プラグイン情報の日英バイリンガル対応)
* Specified 100% free and commercial use allowance. (完全無料・商用利用可能である旨を明記)
* Added official support and bug report channels via GitHub Issues. (GitHub Issuesによる公式サポート窓口を設置)
* Removed empty Screenshots tab for cleaner UI. (表示最適化のため空のスクリーンショットタブを非表示化)

= 1.0.1 =
* Minor performance improvements and stability updates.

= 1.0.0 =
* Initial release.

== Credits ==
* Arrow icons provided by Lucide (MIT License).