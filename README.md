This project was first created by Donat Marko (IVAO VID 540147). But later abandonded.
We've decided to continue its development and improve it somehow.
Dependencies found may be not up to date or obsolete, as they were from 2020.
PHPMailer and all its dependencies are probably not needed.
Email settings are stored in the database `config` table. For Gmail SMTP, use `smtp.gmail.com` and a Google App Password; see the configuration notes below.

=========================================================================================

When first login in the website, remember to update your permissions in the users table in the database so you can have access to the admin panel.

=========================================================================================

The Following is AI Generated, sorry.
# IVAO Event Flight Booking System

A web-based flight booking and event management platform specifically designed for IVAO virtual aviation events (such as RFE, RFO, or divisional events).

## Features

- **IVAO SSO Integration**: Secure login using IVAO's official Login API.
- **Flight Booking**: Pilots can browse and book event flights.
- **Private Slots Management**: Administrators can assign private flight slots to specific users.
- **Multi-airport Events**: Support for configuring events across multiple airports.
- **Admin Dashboard**: Comprehensive back-office to manage users, flights, airports, and general settings.
- **Email Notifications**: Automated confirmation emails using SMTP.
- **Dynamic Content Management**: Ability to modify event briefs, slot instructions, and other static pages.
- **GDPR Compliance**: Pilots can choose whether to display their real name or keep it anonymous.
- **Live Weather Integration**: Briefings pull METAR/TAF data from the configured weather API.

## Requirements

- PHP 7.4+ or PHP 8.x
- MySQL / MariaDB
- Web server (Apache, Nginx, etc.)

## Installation

1. Clone this repository into your web root directory.
2. Rename `config-inc.sample.php` to `config-inc.php` and fill in your database credentials and IVAO SSO token.
3. Import the database structure. You can find the SQL dump in `_SQL/tables.sql`.
4. Ensure your server correctly processes PHP and has the necessary PHP extensions enabled (e.g., `mysqli`, `curl`, `json`).
5. Run the application and login using an IVAO staff or authorized account.

## Database Configuration

`_SQL/tables.sql` creates the `config` table and inserts example settings. Review those values after importing the SQL file. The table uses `key` and `value` columns, and the application reads the settings by key.

The event settings are `mode`, `prebook`, `division_name`, `division_web`, `division_email`, `division_facebook`, `division_twitter`, `event_name`, `event_about`, `event_banner`, `date_start`, `date_end`, and `wx_url`. These can be updated from the admin panel's general settings. `mode` is `0` (not open), `1` (open), or `2` (closed); `prebook` is `true` or `false`.

Email settings use `mail_driver` (`smtp` or `api`), `mail_from_name`, and `mail_from_email`. For Gmail SMTP, configure these `config` rows:

| Key | Example value |
| --- | --- |
| `mail_driver` | `smtp` |
| `mail_from_name` | Your event sender name |
| `mail_from_email` | Your sender email address |
| `mail_smtp_server` | `smtp.gmail.com` |
| `mail_smtp_port` | `587` |
| `mail_smtp_auth` | `true` |
| `mail_smtp_secure` | `tls` |
| `mail_smtp_username` | The Google account email address |
| `mail_smtp_password` | A Google App Password; never put the real value in this README or in source control |

Create the App Password in the Google Account's app-password settings with 2-Step Verification enabled. Google may revoke or expire an App Password according to the account's security policies; there is no universal renewal interval. Follow Google's expiry or security notices, generate a replacement when needed, and update `mail_smtp_password` in the database. Treat the database and its backups as secrets because these credentials are stored as plain values in `config`.

For existing databases created from an older SQL dump, the sample key `mail_smtp_encryption` does not match the key read by the application (`mail_smtp_secure`). Rename it and add the SMTP authentication setting if it is missing:

```sql
UPDATE config
SET `key` = 'mail_smtp_secure'
WHERE `key` = 'mail_smtp_encryption';

INSERT INTO config (`key`, `value`)
SELECT 'mail_smtp_auth', 'true'
WHERE NOT EXISTS (
	SELECT 1 FROM config WHERE `key` = 'mail_smtp_auth'
);
```

## Built With

- **PHP** (Backend)
- **Bootstrap 4** (Frontend UI)
- **jQuery** (DOM manipulation and AJAX)
- **Leaflet** (Map integration)
- **DataTables** (Table grids and pagination)
- **PHPMailer** (Email delivery)

## License

This project is intended for the IVAO community. Please refer to the IVAO Intellectual Property Policy and the bundled `LICENSE` file for more details.

## Author

Created by Donat Marko (IVAO VID 540147).