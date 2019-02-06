# tiny-invoice

## What is it?

This tool is the missing link between the company management and the 
bookkeeping department. It allows you to do most administrative tasks that can 
be found in an avarage company via a web-based user-interface.

## So, web-based bookkeeping software?

No! Tinyinvoice does not pretend to be bookkeeping software. In fact, we advice
you to use bookkeeping sofware from a third-party vendor in combination with
TinyInvoice. Tinyinvoice has a pluggable export architecture. That means that
an integration with your bookkeeping software is perhaps already possible.

## Why using TinyInvoice instead of my bookkeeping software?

Well, it seems that for some reason, most bookkeeping software tend to be 
'offline' software. This means that the data is not always accessible at any
time and also not by the correct person. Do you remember last time calling
your bookkeeper asking for:
- Customer who have Unpaid invoices which are overdue?
- A list of unpaid supplier invoices?
- A revenue overview of your big customer?

If this sounds familiar, than TinyInvoice can probably help you! Since
TinyInvoice is completely web-based, it can be used by many users at the same 
time from any location. The only thing that is required is a web-browser and
an internet connection.

## But my bookkeeping software is 'online'!

Bookkeeping software venders try to fill up this gap by offering their 
software via Windows RDP / Citrix of similar techniques. While by doing this,
the application gets accessible over internet, it doesn't make the software
'web-based'. 
If you do have web-based bookkeeping software, please get in contact with us!

## What can TinyInvoice help me with?

### Relations

First of all, Tinyinvoice takes care of your most important relations:
- customers
- suppliers
Both can be managed via the web interface. Exports allows you to import this
data into your bookkeeping sofware.
For both customers and suppliers, automatic VAT validation against vies 
(http://ec.europa.eu/taxation_customs/vies/?locale=nl) is in place.

### E-invoiceing

Tinyinvoice offers invoice creation via a web-based wizard. After the invoice
is created, an email is sent to your customer.
If the invoice is overdue, TinyInvoice can automatically send a reminder-email.
Also credit notes are supported.
With extra's like 'Invoice Queue' (waiting queue for items that will be on the
next invoice) and 'Recurring Invoice Queue' (items that you want to invoice 
on recurring intervals), it will be hard to ever forget an item to invoice.

### Document management

TinyInvoice is your central place to store all your incoming invoices, incoming
credit notes, contracts or plain documents. 
Documents can be picked up via a mailscanner. That way you can mail any invoice
to your chosed mailbox and it will appear in TinyInvoice.
Extra automation can be added via SetaSign PDF Extractor 
(https://www.setasign.com/products/setapdf-extractor/details/). By using this,
you can extract all metadata of your incoming documents for known documents.

The Document management system has a 'pay' module. This can generate a 'SEPA 
PAIN 001.001.03' file. This file can be uploaded to your bank account and 
your invoices will be paid automatically.

### CODA integration

CODA files can be uploaded into TinyInvoice. TinyInvoice will automatically
detect 'known transactions'. These can be:
- payments by customers for outgoing invoices
- payments to suppliers for incoming invoices, triggered via a 
'SEPA PAIN 001.00.03' file.
If a transaction is detected, the corresponding invoice is marked as paid.
Unknown transactions can manually be linked to the correct invoice or booked
on a bookkeeping account.
Rules can be created in order to handle recurring transactions automatically.
By doing all this linking, a lot of your bookkeeping is already done, exports
allows you to export the data into your bookkeeping software.

If you want CODA files to be provided automatically, have a look at 
https://github.com/tigron/codabox-gateway

### API

TinyInvoice has a complete API. This allowes you to integrate your own software
with TinyInvoice. 


## Installation

Installation is fairly straightforward.

  * Put the code somewhere on a webserver that supports .htaccess files
  * Make sure that tmp/ and store/ are writeable by your webserver user
  * Create config/Config.php from config/Config.sample.php
  * install packages via 'composer install' from the root of the project
  * Run all migrations to setup the database: '/util/bin/skeleton migrate:up'

This should allow you to log in with the default credentials (user/user).

A non-exhaustive list of requirements:

  * PHP >= 5.4
  * The PHP MySQLi extension
  * The PHP GD extension
  * A MySQL server >= 5.1
