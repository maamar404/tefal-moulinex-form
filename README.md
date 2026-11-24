# Tefal & Moulinex — Promotional Tombola Web Platform

**Live Website:** [https://www.tefal-moulinex.com/](https://www.tefal-moulinex.com/)

## Overview
A production promotional campaign web platform engineered for **Groupe SEB Morocco (Tefal & Moulinex)**. The platform enables customers to register their product purchases, upload proof of purchase, and enter an official promotional tombola for a chance to win gifts.

---

## Key Features & Engineering Highlights

* **Brand-Aligned Responsive UI:** Designed and built a clean, mobile-first campaign landing page and registration flow tailored to the **Tefal** and **Moulinex** brand identities.
* **Secure Backend Processing (Native PHP):** Implemented server-side form validation, input sanitization, and protection against common web vulnerabilities (SQL injection via prepared statements, XSS sanitization).
* **Proof-of-Purchase File Upload Pipeline:** Built secure file upload handling with strict MIME-type/extension verification, file size limits, and safe server storage organization for customer receipts.
* **Relational Database Architecture (MySQL):** Structured normalized database tables to store participant details, product references, submission timestamps, and uploaded document paths for tombola draw verification.
* **Campaign Analytics & Conversion Tracking:** Integrated **Google Tag Manager (GTM)** scripts to track page engagement, form submissions, and campaign conversion funnels.

---

## Tech Stack

| Layer | Technologies |
|---|---|
| **Frontend** | HTML5, CSS3, JavaScript (ES6+), Responsive Layout |
| **Backend** | Native PHP (PDO / Prepared Statements) |
| **Database** | MySQL |
| **Analytics** | Google Tag Manager (GTM) |
| **Local Development** | XAMPP (Apache, MySQL, PHP) |

---

## Project Structure

```text
tefal-moulinex-tombola/
├── index.php                  # Campaign landing page & tombola registration form
├── process.php                # Server-side validation, file upload & DB insertion logic
├── config.example.php         # Sample database configuration template
├── database/
│   └── schema.sql             # Sanitized MySQL table structure (no participant data)
├── assets/
│   ├── css/                   # Brand stylesheets & responsive rules
│   ├── js/                    # Client-side form validation & UI interactions
│   └── images/                # Brand logos & campaign visuals
└── uploads/                   # Protected directory for uploaded receipts (gitignored)
