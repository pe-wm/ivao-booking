This project was first created by Donat Marko (IVAO VID 540147). But later abandonded.
We've decided to continue its development and improve it somehow.
Dependencies found may be not up to date or obsolete, as they were from 2020.
PHPMailer and all its dependencies are probably not needed.
We're using google for emailing. All it's in the database config table, mail_smtp_* entries. Using smtp.google.com works fine.

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