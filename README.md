# 🛡️ WordPress Anti-Spam Honeypot (T709 AntiSpam)

A lightweight **WordPress security plugin** that prevents spam submissions on forms using a **server-side honeypot**, **time-trap heuristic**, and **IP-based rate limiting**.  
Fully compatible with **WPForms** and **Contact Form 7** — no CAPTCHA, no friction for users.

---

## 🔐 Features

- 🕵️‍♂️ **Invisible honeypot** field to trap automated bots  
- ⏱️ **Time-trap protection** (blocks <3-second submissions)  
- 🧩 **Supports WPForms & Contact Form 7** out of the box  
- 🧠 **Server-side PHP validation** (not reliant on JavaScript)  
- 💾 **Logging system** to record IP, timestamp, reason, and user agent  
- 🪶 **Zero user disruption** — no visible fields or CAPTCHA  
- ⚙️ **Optional rate limiting** (max 3 submissions / 5-minute window)  
- 🖥️ **Admin dashboard** for reviewing, downloading, and clearing spam logs  

---

## 🧠 How It Works

1. Injects two hidden fields into supported forms:  
   - `website_url` → Honeypot (must stay empty)  
   - `t709_ts` → Timestamp (when form is rendered)  

2. On submission, the plugin validates:  
   - Honeypot filled → **Spam**  
   - Form submitted too quickly (<3 seconds) → **Spam**  
   - IP exceeded rate-limit threshold → **Spam**  
   - Keyword match (if configured) → **Spam**  

3. If spam is detected, the message is **blocked server-side** — preventing junk emails or database entries.  
   Blocked attempts are logged here:
