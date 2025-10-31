# WordPress Anti-Spam Honeypot (T709 AntiSpam)

A lightweight WordPress security plugin that protects forms from spam bots using a **server-side honeypot** and **time-trap heuristic**.  
Built for **WPForms** and **Contact Form 7** compatibility.

---

## 🔐 Features
- Invisible honeypot field that catches automated bots
- Time-trap validation — rejects forms submitted too quickly
- Works with WPForms and Contact Form 7
- Server-side PHP validation (not just JavaScript)
- No UX disruption — users never see a CAPTCHA
- Easy drop-in MU plugin, no settings page needed

---

## 🧠 How It Works
1. Injects two hidden fields:  
   - `website_url` (honeypot)  
   - `t709_ts` (timestamp)
2. Validates on submission:
   - If the honeypot field is filled → **spam**
   - If the form was submitted too fast (<3 seconds) → **spam**
3. Rejection occurs server-side, preventing junk emails from being sent.

---

## 📁 Installation
1. Copy `t709-antispam.php` to `/wp-content/mu-plugins/`
2. Save changes — it activates automatically.
3. Works with both WPForms and Contact Form 7 out of the box.

---

## 🧰 Future Roadmap
- Admin settings page to adjust time threshold
- Logging of blocked IPs & user agents
- Rate-limiting by IP (3 submissions per 5 minutes)
- Cloudflare Turnstile integration

---

## 🧑‍💻 Author
**Dillon Porter**  
[GitHub](https://github.com/dportersec) | [Portfolio](https://sites.google.com/view/dillonporter/home)

---

## ⚖️ License
MIT License — free for personal or commercial use.

---
### 🌐 Web Development Portfolio
Although I’m transitioning into cybersecurity, I continue to freelance part-time in web development, specializing in secure WordPress builds and website optimization.

**Portfolio:** [https://dillon-porter.github.io/portfolio/](https://dillon-porter.github.io/portfolio/)


