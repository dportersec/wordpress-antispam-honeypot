=== T709 Anti-Spam Honeypot ===
Contributors: dportersec
Tags: spam, security, honeypot, wordpress, forms, contact form 7, wpforms
Requires at least: 5.0
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: 1.0.0
License: MIT
License URI: https://opensource.org/licenses/MIT

A lightweight anti-spam plugin that uses honeypot and time-trap validation to stop automated form submissions in WordPress.  
Compatible with WPForms and Contact Form 7 — no CAPTCHA required.

== Description ==

**T709 Anti-Spam Honeypot** adds invisible server-side protection to WordPress forms without annoying real users.  
Instead of relying on reCAPTCHA, this plugin quietly inserts hidden honeypot fields and a timestamp check to detect bots that submit forms too quickly or fill hidden fields.

Built with a focus on **cybersecurity best practices** and **performance** — no external requests, no tracking, and no heavy scripts.

### 🔐 Features
* Invisible honeypot field injected into supported forms  
* Time-trap logic that blocks submissions made in under 3 seconds  
* Works with WPForms and Contact Form 7  
* No JavaScript or CAPTCHA required  
* Optional logging for blocked attempts (IP, timestamp, reason)  
* Lightweight — <10 KB total plugin size  

### 🧠 How It Works
1. The plugin adds two hidden fields to every supported form:
   * `t709_field` → honeypot
   * `t709_ts` → timestamp
2. On submission:
   * If the honeypot has a value → request is flagged as spam
   * If submission is faster than the threshold → spam
3. Spam requests are blocked before being sent via email.

### 🧩 Integrations
* ✅ **WPForms**
* ✅ **Contact Form 7**
* 🧱 Custom hooks available for other form plugins

### 📋 Example Block Reasons
* `honeypot_triggered`
* `timed_submission`
* `rate_limited`

### 🧰 For Developers
Hooks:
```php
do_action( 't709_antispam_blocked', $reason, $ip, $form_id );
