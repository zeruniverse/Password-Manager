# Password Manager
[![Build Status](https://travis-ci.org/zeruniverse/Password-Manager.svg)](https://travis-ci.org/zeruniverse/Password-Manager)
[![Codacy Badge](https://api.codacy.com/project/badge/grade/b5d954be72144355aa258748cfd05bca)](https://www.codacy.com/app/zzy8200/Password-Manager)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](https://github.com/zeruniverse/Password-Manager/blob/master/LICENSE)
![Environment](https://img.shields.io/badge/PHP-7.1+-blue.svg)
![Environment](https://img.shields.io/badge/MySQL-required-ff69b4.svg)
![Environment](https://img.shields.io/badge/HTTPS-required-66ccff.svg)

**'master' branch is a dev-branch, please download stable version from [Release](https://github.com/zeruniverse/Password-Manager/releases) if you just want to use it.**

## Version

v11.00

Since v11.00, this password manager is mature and stable. That said, there will be no more major changes (e.g. database structure change) in the future. But just bug fixes and crypto algorithm updates. I know in the past, upgrading is painful due to the client-side encryption nature. But going forward, it will not be an issue anymore. All users should upgrade to at least v11.00!

Supports are available [here](https://github.com/zeruniverse/Password-Manager/issues) for versions v11.00 or later

## DEMO
[phppasswordmanager.sourceforge.io](https://phppasswordmanager.sourceforge.io)

This demo is for test **ONLY**! Do **NOT** put your real password there.

You can access the database for this demo [here](https://mysql-p.sourceforge.net), with login username **p2663268ro** and password **12345678**

## Features

1. Client side encryption. Server only keeps the encrypted strings. **Strong encryption**: Server side uses PBKDF2+SHA3-512, client side uses AES256 / PBKDF2+[SHA512|SHA3-512]  (SHA512 is used at client side as Javascript SHA3 is too slow)  [See more about security analysis](https://github.com/zeruniverse/Password-Manager/wiki/Mechanism#safety). Due to client-side encryption nature, if you forget your login password, there's **NO WAY** to recover your data.

2. Customized fields support. You can add and delete fields for the password manager. You might want a URL field to keep login URL for all your accounts.

3. PIN login. You don't need to input your long login password everytime. Instead, you can use a short PIN, in your trusted devices.

4. Files support. You can attach files to accounts. Of course, files are encrypted in your browser before they are uploaded.

5. Tags support and searching support. This makes it easier to manage lots of accounts.

6. Import/Export as CSV file. (Export CSV has been moved to recovery - you need to: generate backup -> recovery -> export to CSV. It is to discourage user from exporting CSV as the raw format is very unsafe)

7. Easy to backup and recover. For recovery, you **only** need backup file and login password when generating this backup file. All other information needed for decryption is stored in the backup file. Even if you mistakenly doomed your server, you can download source from github and do recovery (no configuration is needed). After recovery successfully decrypt all data, you can export CSV (no file or password history information). You can also export RAW format that has all data (a full clone) and can be imported into another Password-Manager instance.

8. Authentication control. Account/IP will be blocked for too many failed attempts. After a short time of no action, you'll sign out automatically.

9. Email based two-step verification support on new device login (You need a free account from [SendGrid](https://sendgrid.com/) or implement your own send_email logic in src/function/send_email.php)

10. Up to 15 password histories per account.

11. [Client-side source file integrity check](https://github.com/zeruniverse/Password-Manager/wiki/Installation#enable-client-side-source-file-check) so you will notice if your server gets hacked and someone changed the front-end source code.

12. Friendly UI.

## Installation
See [wiki](https://github.com/zeruniverse/Password-Manager/wiki/Installation)

If you deploy password manager on server that you are not 100% sure about its safety (e.g. VPS), [enable client-side source file check](https://github.com/zeruniverse/Password-Manager/wiki/Installation#enable-client-side-source-file-check). Suppose you install your password manager at `pw.A.com` and you put client-side check code at GitHub pages with CNAME `entry.A.com`. You should always visit `entry.A.com` and let it redirect you to `pw.A.com`.

Client-side source file check uses Fetch API. [It's not supported in all browsers](https://developer.mozilla.org/en-US/docs/Web/API/Body/text) but most likely your browser will support it. If your browser does not support Fetch API, you can re-implement the logic (get_content JS function in check_website.html) using AJAX GET.

## How to use
See [wiki](https://github.com/zeruniverse/Password-Manager/wiki)

## Web Browser Plugin (Does NOT work with v11.00 yet)

Chrome: [Chrome Web Store](https://chrome.google.com/webstore/detail/password-manager/mbfjokpccbakbnnpklkcginkalkijkan)

Firefox: [Add-on](https://addons.mozilla.org/en-US/firefox/addon/self-hosted-password-addon/)

GitHub Project: [PwChromeExtension](https://github.com/BenjaminHae/PwChromeExtension) by Benjamin.

## Mechanism

<img width="1098" alt="mechanism" src="https://user-images.githubusercontent.com/4648756/89339157-8c6f7b00-d652-11ea-8407-2457c442d32b.jpg">

You can read more information about implementation in [wiki](https://github.com/zeruniverse/Password-Manager/wiki/Mechanism).

## Contribution

Please read the [guide](https://github.com/zeruniverse/Password-Manager/wiki/Contribution) first.

All contributors to this project must agree their work to be published under MIT license ONLY (see LICENSE file) before submitting a pull request.
