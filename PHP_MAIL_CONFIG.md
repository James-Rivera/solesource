# PHP Mail Configuration - XAMPP Setup

## ✓ Configuration Applied

Your php.ini has been updated with the following settings:

```
SMTP = 127.0.0.1
smtp_port = 25
sendmail_from = "solesource@localhost"
```

**Location**: `C:\xampp\php\php.ini` (lines 1101-1107)

---

## To Enable Real SMS Delivery: 3 Options

### Option 1: Use Papercut (Recommended - Easiest)
Local SMTP server simulator that intercepts all emails

**Steps:**
1. Download: https://github.com/ChangemakerStudios/Papercut-SMTP/releases
2. Extract and run `Papercut.exe`
3. It will listen on `localhost:25` for SMTP emails
4. Go to `http://localhost:8080` to see all sent emails
5. Your SMS to DITO gateway will be visible there

**Status**: ✓ PHP configured for this  
**Test**: Visit `http://localhost/solesource/test-mail.php` - should show SUCCESS

---

### Option 2: Keep Mock Mode (Currently Working)
The system works perfectly in mock mode - OTP codes display on screen

**In sms-config.php:**
```php
define('SMS_MODE', 'mock');  // Simulates sending
```

**Advantage**: Zero external dependencies, perfect for development  
**What it does**: Shows OTP codes in browser instead of sending SMS

---

### Option 3: Configure Gmail SMTP (Production Alternative)
Use Gmail's SMTP for real sending (requires app password)

**php.ini settings:**
```
SMTP = smtp.gmail.com
smtp_port = 587
sendmail_from = "your-email@gmail.com"
```

**Note**: Requires Gmail App Password (not your regular password)  
**Setup**: https://myaccount.google.com/apppasswords

---

## Current SMS Configuration

**File**: `includes/sms-config.php`  
**Current Mode**: `dito`  
**Status**: Waiting for mail() to work

Available modes:
- `'mock'` - Simulates SMS (development, no mail needed)
- `'dito'` - DITO carrier email-to-SMS (requires mail() working)
- `'smsgate'` - Android SMSGate app (requires mobile device)
- `'file'` - Writes to logs/sms.txt (debugging)

---

## Next Step

**Choose one of these:**

```
A) Install Papercut → No changes needed → Test
B) Switch to mock mode → Change line 15 → Already working
C) Setup Gmail SMTP → Update php.ini → Configure app password
```

**Backup created:** `C:\xampp\php\php.ini.backup`

---

## Test Your Mail Configuration

Visit: `http://localhost/solesource/test-mail.php`

Should show:
- ✓ mail() function returned TRUE
- ✓ XAMPP IS configured to send emails
- SMTP: 127.0.0.1
- smtp_port: 25
