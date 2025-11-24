<?php
/**
 * Plugin Name: Cookie Consent Notice
 * Plugin URI: https://eyalamit.co.il
 * Description: הודעת הסכמה לעוגיות - מופיעה בכניסה ראשונה לאתר
 * Version: 1.0.0
 * Author: Custom
 * Author URI: https://eyalamit.co.il
 * License: GPL v2 or later
 * Text Domain: cookie-consent-notice
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Cookie Consent Notice
 * הודעת הסכמה לשימוש בעוגיות
 */
function enqueue_cookie_consent_scripts() {
	// Register and enqueue a dummy stylesheet to attach inline styles
	wp_register_style( 'cookie-consent-style', false );
	wp_enqueue_style( 'cookie-consent-style' );
	
	// Add inline CSS
	wp_add_inline_style( 'cookie-consent-style', '
		#cookie-consent-notice {
			position: fixed;
			bottom: 0;
			left: 0;
			right: 0;
			background: #fff;
			border-top: 1px solid #333;
			padding: 10px 15px;
			box-shadow: 0 -2px 8px rgba(0,0,0,0.1);
			z-index: 99999;
			display: none;
			font-family: Arial, sans-serif;
			direction: rtl;
			text-align: right;
		}
		#cookie-consent-notice.show {
			display: block;
		}
		.cookie-consent-content {
			max-width: 1200px;
			margin: 0 auto;
			display: flex;
			align-items: center;
			justify-content: space-between;
			flex-wrap: wrap;
			gap: 10px;
		}
		.cookie-consent-text {
			flex: 1;
			min-width: 300px;
			font-size: 13px;
			line-height: 1.4;
			color: #333;
		}
		.cookie-consent-text p {
			margin: 0 0 5px 0;
		}
		.cookie-consent-text p:last-child {
			margin-bottom: 0;
		}
		.cookie-consent-checkbox {
			display: flex;
			align-items: center;
			gap: 6px;
			margin: 5px 0 0 0;
		}
		.cookie-consent-checkbox input[type="checkbox"] {
			width: 16px;
			height: 16px;
			cursor: pointer;
			margin: 0;
		}
		.cookie-consent-checkbox label {
			cursor: pointer;
			font-size: 13px;
			color: #333;
			margin: 0;
		}
		.cookie-consent-actions {
			display: flex;
			gap: 10px;
			align-items: center;
		}
		.cookie-consent-button {
			background: #333;
			color: #fff;
			border: none;
			padding: 8px 16px;
			cursor: pointer;
			font-size: 13px;
			border-radius: 3px;
			transition: background 0.3s;
			white-space: nowrap;
		}
		.cookie-consent-button:hover {
			background: #555;
		}
		.cookie-consent-button:disabled {
			background: #ccc;
			cursor: not-allowed;
		}
		@media (max-width: 768px) {
			#cookie-consent-notice {
				padding: 8px 12px;
			}
			.cookie-consent-content {
				flex-direction: column;
				text-align: center;
				gap: 8px;
			}
			.cookie-consent-text {
				font-size: 12px;
			}
			.cookie-consent-checkbox label {
				font-size: 12px;
			}
			.cookie-consent-actions {
				width: 100%;
				justify-content: center;
			}
			.cookie-consent-button {
				padding: 7px 14px;
				font-size: 12px;
			}
		}
	' );
	
	// Register and enqueue JavaScript
	wp_register_script( 'cookie-consent', false );
	wp_enqueue_script( 'cookie-consent' );
	$cookie_consent_js = '
	(function() {
		function initCookieConsent() {
			var notice = document.getElementById("cookie-consent-notice");
			var checkbox = document.getElementById("cookie-consent-checkbox");
			var acceptBtn = document.getElementById("cookie-consent-accept");
			
			if (!notice || !checkbox || !acceptBtn) return;
			
			// Check if user has already consented
			if (!localStorage.getItem("cookie_consent_accepted")) {
				notice.classList.add("show");
			}
			
			// Handle checkbox change
			checkbox.addEventListener("change", function() {
				if (this.checked) {
					acceptBtn.disabled = false;
				} else {
					acceptBtn.disabled = true;
				}
			});
			
			// Handle accept button
			acceptBtn.addEventListener("click", function() {
				if (checkbox.checked) {
					localStorage.setItem("cookie_consent_accepted", "true");
					notice.classList.remove("show");
					// Set cookie as well for server-side access
					document.cookie = "cookie_consent_accepted=true; path=/; max-age=31536000"; // 1 year
				}
			});
		}
		
		if (document.readyState === "loading") {
			document.addEventListener("DOMContentLoaded", initCookieConsent);
		} else {
			initCookieConsent();
		}
	})();
	';
	wp_add_inline_script( 'cookie-consent', $cookie_consent_js );
}
add_action( 'wp_enqueue_scripts', 'enqueue_cookie_consent_scripts', 20 );

// Add cookie consent notice HTML to footer
function add_cookie_consent_notice() {
	// Only show on frontend
	if (is_admin()) {
		return;
	}
	?>
	<div id="cookie-consent-notice">
		<div class="cookie-consent-content">
			<div class="cookie-consent-text">
				<p><strong>שימוש בעוגיות באתר</strong></p>
				<p>אנחנו משתמשים בעוגיות כדי לשפר את החוויה שלך באתר. על ידי המשך השימוש באתר, אתה מסכים לשימוש בעוגיות בהתאם למדיניות הפרטיות שלנו.</p>
				<div class="cookie-consent-checkbox">
					<input type="checkbox" id="cookie-consent-checkbox" name="cookie-consent">
					<label for="cookie-consent-checkbox">אני מבין ומסכים לשימוש בעוגיות באתר</label>
				</div>
			</div>
			<div class="cookie-consent-actions">
				<button id="cookie-consent-accept" class="cookie-consent-button" disabled>אשר</button>
			</div>
		</div>
	</div>
	<?php
}
add_action( 'wp_footer', 'add_cookie_consent_notice' );

