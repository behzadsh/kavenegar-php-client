# Kavenegar PHP Client

A php client for connecting to kavenegar.com api for sending sms.

## Installation

You must then modify your `composer.json` file and run `composer update` to include the latest version of the package in your project.

```
"require": {
    "quince/kavenegar-client": "~1.0"
}
```

Or you can run the composer require command from your terminal.

```
composer require quince/kavenegar-client
```

## Usage

```php
<?php

$apiKey = '{API_KEY}';
$senderNumber = '10004346';

$client = Quince\kavenegar\ClientBuilder::build($apiKey, $senderNumber);

$client->send('09123456789', 'سلام چطوری؟');
```

## Methods description

### Client::send()
Send a message to specified receptor phone number.

Parameters  | type                 | description
---:        |---                   |---
receptor    | string\|string[]     | receptor or receptors phone number
message     | string               | The text to send
date        | int                  | (optional) Setting the time to send the message in timestamp
type        | int                  | (optional) Type of the sms [look here](https://kavenegar.com/rest.html#result-msgmode)
localId     | string               | (optional) Custom id for message
sender      | string               | (optional) senders number, if it is set while building client no need to feel this. the given number will overwrite the default number for this action

```php
// Send a message to `09123456789` in 4/9/2016, 1:00:43 AM
$client->send('09123456789', 'The text to send', 1460147443, 0, '12', '30004346');

// Send a message to multiple receptors
$client->send(['09123456789', '09356789124', '09213456789'], 'The text to send', 1460147443, 0, '12', '30004346');
```

[more info](https://kavenegar.com/rest.html#sms-send)

### Client::bulkSend()

Send bulk messages to multiple receptor phone numbers.

Parameters  | type                 | description
---:        |---                   |---
receptors   | string[]             | receptors phone number
message     | string\|string[]      | The text or texts to send, if array of texts passed, number of texts and receptors should be equal, and each text will send to corresponding receptor
date        | int                  | (optional) Setting the time to send the message in timestamp
type        | int\|int[]            | (optional) Type of the sms [look here](https://kavenegar.com/rest.html#result-msgmode)
localId     | string\|string[]      | (optional) Custom id for message
sender      | string\|string[]      | (optional) senders number, if it is set while building client no need to feel this. the given number will overwrite the default number for this action

```php
// Send multiple message to multiple receptor
$client->bulkSend(
    ['09123456789', '09356789124', '09213456789'],
    ['Text to send to 09123456789', 'Text to send to 09367891245', 'Text to send to 09213456789'],
    1460147443,
    [0, 1, 2],
    ['12', '13', '14'],
    ['10004346', '20004346', '30004346']
);
```

[more info](https://kavenegar.com/rest.html#sms-sendarray)

### Client::getMessageStatus()

Get status of a sent message by its message id.

Parameters  | type                 | description
---:        |---                   |---
messageId   | string\|string[]     | Message id that given to you when sent a message

```php
// Get delivery status of a message
$client->getMessageStatus('8792343');

// Get delivery status of multiple messages
$client->getMessageStatus(['8792343', '8792344', '8792345']);
```

[more info](https://kavenegar.com/rest.html#sms-status)

### Client::getMessageStatusByLocalId()

Get status of a sent message by local id set when sending.

Parameters  | type                 | description
---:        |---                   |---
localId     | string\|string[]     | Message id you set when sending a message

```php
// Get delivery status of a message
$client->getMessageStatus('13');

// Get delivery status of multiple messages
$client->getMessageStatus(['13', '14', '15']);
```

[more info](https://kavenegar.com/rest.html#sms-statuslocalmessageid)

### Client::getMessageDetail()

Get details of a message by message id.

Parameters  | type                 | description
---:        |---                   |---
messageId   | string\|string[]     | Message id you set when sending a message

```php
// Get details of a message
$client->getMessageDetail('8792343');

// Get details of multiple messages
$client->getMessageDetail(['8792343', '8792344', '8792345']);
```

[more info](https://kavenegar.com/rest.html#sms-select)

### Client::getOutbox()

Get all sent message in specified date range (max 3000 message).

Parameters  | type                 | description
---:        |---                   |---
startDate   | int                  | Start date in timestamp
endDate     | int                  | (optional) End date in timestamp
sender      | string               | (optional) senders number, if it is set while building client no need to feel this. the given number will overwrite the default number for this action. If you sent message from multiple line number set sender number as set it 0 to bring outbox of all sender numbers.

```php
// Get outbox messages sent between 4/8/2016, 10:56:40 PM and 4/9/2016, 1:00:43 AM from all sender numbers
$client->getOutbox(1460140000, 1460147443, 0);
```

[more info](https://kavenegar.com/rest.html#sms-selectoutbox)

### Client::getRecentOutbox()

Get list of recent sent messages (max 3000 messages).

Parameters  | type                 | description
---:        |---                   |---
pageSize    | int                  | (optional) number of message per request.
sender      | string               | (optional) senders number, if it is set while building client no need to feel this. the given number will overwrite the default number for this action. If you sent message from multiple line number set sender number as set it 0 to bring outbox of all sender numbers.

```php
// Get 20 latest sent messages from all sender numbers
$client->getRecentOutbox(20, 0);
```

[more info](https://kavenegar.com/rest.html#sms-selectlatest)

### Client::getOutboxCount()

Get count of sent message in specified range.

Parameters  | type                 | description
---:        |---                   |---
startDate   | int                  | Start date in timestamp
endDate     | int                  | (optional) End date in timestamp
sender      | string               | (optional) senders number, if it is set while building client no need to feel this. the given number will overwrite the default number for this action. If you sent message from multiple line number set sender number as set it 0 to bring outbox of all sender numbers.

```php
// Get outbox messages count between 4/8/2016, 10:56:40 PM and 4/9/2016, 1:00:43 AM from all sender numbers
$client->getOutboxCount(1460140000, 1460147443, 0);
```

[more info](https://kavenegar.com/rest.html#sms-countoutbox)

### Client::cancelMessage()

Canceling a pending message from sending.

Parameters  | type                 | description
---:        |---                   |---
messageId   | string               | Message id that given to you when sent a message

```php
// Cancel sending of message with id of 8792343
$client->cancelMessage('8792343');
```

[more info](https://kavenegar.com/rest.html#sms-cancel)

### Client::getInbox()

Get list of received messages (100 messages per each request).

Parameters  | type                 | description
---:        |---                   |---
onlyRead    | bool                 | (optional) If set to true only read messages would fetched
line        | string               | (optional) senders number, if it is set while building client no need to feel this. the given number will overwrite the default number for this action. If you sent message from multiple line number set sender number as set it 0 to bring outbox of all sender numbers.

```php
// Get all unread messages sent to all lines
$client->getInbox(false, 0);
```

[more info](https://kavenegar.com/rest.html#sms-unreads)

### Client::getInboxCount()

Get count of messages in inbox.

Parameters  | type                 | description
---:        |---                   |---
startDate   | int                  | Start date in timestamp
endDate     | int                  | (optional) End date in timestamp
line        | string               | (optional) senders number, if it is set while building client no need to feel this. the given number will overwrite the default number for this action. If you sent message from multiple line number set sender number as set it 0 to bring outbox of all sender numbers.
onlyRead    | bool                 | (optional) If set to true only read messages would fetched

```php
// Get inbox messages count between 4/8/2016, 10:56:40 PM and 4/9/2016, 1:00:43 AM from all sender numbers
$client->getInboxCount(1460140000, 1460147443, 0, false);
```

[more info](https://kavenegar.com/rest.html#sms-countinbox)

### Client::phoneCountByPostalCode()

Get count of phone numbers in a postal code area, categorized by oprator.

Parameters  | type                 | description
---:        |---                   |---
postalCode  | string               | Postal code for an area

```php
// Get phone count in 11252 postal code area
$client->phoneCountByPostalCode('11252');
```

[more info](https://kavenegar.com/rest.html#sms-countpostalcode)

### Client::sendByPostalCode()

Send message to phone numbers in a postal code area.

Parameters    | type                 | description
---:          |---                   |---
postalCode    | string               | Postal code for an area
message       | string               | Text message to send
mciStartIndex | int                  | Start index of MCI receptors, if set to -1 receptor selects randomely
mciCount      | int                  | Count of message to send to MCI receptors, if set to 0 (and start index set to 0 too) will send to all
mtnStartIndex | int                  | Start index of MTN receptors, if set to -1 receptor selects randomely
mtnCount      | int                  | Count of message to send to MTN receptors, if set to 0 (and start index set to 0 too) will send to all
date          | int                  | (optional) Time to send in timestamp format
sender        | string               | (optional) senders number, if it is set while building client no need to feel this. the given number will overwrite the default number for this action

```php
// Send a message to all MCI phones and 1000 random MTN phones in 11252 postal code area
// scheduled to send in 4/9/2016, 1:00:43 AM with sender number of 30004346
$client->sendByPostalCode(
    '11252',
    'Testing send by postal code',
    0,
    0,
    -1,
    1000,
    1460147443,
    '30004346'
);
```

[more info](https://kavenegar.com/rest.html#sms-sendpostalcode)

### Client::getAccountInfo()

Get information of an account.

```php
// Get info of current account
$client->getAccountInfo();
```

[more info](https://kavenegar.com/rest.html#account-info)

### Client::getAccountConfigs()

Get configuration of an account.

```php
// Get current account configuration
$client->getAccountConfigs();
```

[more info](https://kavenegar.com/rest.html#account-config)

### Client::setAccountConfigs()

Set configuration of an account.

Parameters    | type                 | description
---:          |---                   |---
configs       | array                | list of changing configurations in an array

Config          | type     | description
---:            |---       |---
apilogs         |  string  | Status of logging API requests; values: `justfaults` (default), `enabled`, `disabled`
dailyreport     |  string  | Status of daily report; values: `enabled`, `disabled`
debugmode       |  string  | Status of debiging/testing mode, when enabled message sending will be mocked; values: `enabled`, `disabled`
defaultsender   |  string  | The default sender number
mincreditalarm  |  int     | The limit to alert when the credit is about to finish
resendfailed    |  string  | Whether try to redend when sending failed or not; values: `enabled`, `disabled`

```php
// Enable apilogs and daily reports
// and set default sender to 30004346
// and disable resend when failing to send a message
$client->setAccountConfigs([
    'apilogs'       => 'enabled',
    'dailyreport'   => 'enabled',
    'defaultsender' => '30004346',
    'resendfailed'  => 'disabled'
]);
```

[more info](https://kavenegar.com/rest.html#account-config)

### Client::sendVerificationCode()

Send vrification code, password, authorization code, etc.

Parameters    | type                 | description
---:          |---                   |---
receptor      | string               | Receptor phone number
token         | string               | Verification code, password, or the token
template      | string               | Template name you set in your panel

```php
// send a code to 09123456789 with licence_template template
$client->sendVerificationCode('09123456789', 'EA-958423', 'licence_template');
```

[more info](https://kavenegar.com/rest.html#sms-Lookup)