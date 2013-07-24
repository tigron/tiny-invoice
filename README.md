# tiny-invoice

This tool is currently a quick and dirty invoicing tool, written in
PHP. Expect a bumpy ride and some features which don't work at all.

## Installation

Installation is fairly straightforward.

  * Put the code somewhere on a webserver that supports .htaccess files
  * Use the database.sql file in the config/ directory to bootstrap the database
  * Make sure that tmp/ and store/ are writeable by your webserver user
  * Create config/Config.php from config/Config.sample.php
  * Execute composer.phar residing in lib/external/packages/

This should allow you to log in with the default credentials (user/user).

A non-exhaustive list of requirements:

  * PHP >= 5.4
  * The PHP MySQLi extension
  * The PHP GD extension
  * A MySQL server >= 5.1

## Features

  * Create customers
  * Customers can have multiple invoice contacts
  * Create invoices linked to an invoice contact
  * Configurable VAT rate per invoice item
  * Automatically creates a PDF for your invoices
  * Send your invoices via e-mail
  * Automatic reminder mails for unpaid invoices
