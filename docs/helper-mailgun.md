# Seed-PHP Microframework Documentation

## Mailgun (Beta)

Package: `Helper\Mailgun` <br >
Namespace: `SeedPHP\Helper\Mailgun`

```php
  $config = [
    'apiKey' => 'YOUR-MAILGUN-API-KEY', 
    'domain' => 'YOUR-MAILGUN-REGISTERED-DOMAIN', 
    'emailDefaultReplacement' => null, // string
    'whitelist' => null // array<string>
  ];

  $app->load('mailgun', $config);
  $app->mailgun->setTo('name@domain.tld', 'John Doe')->fire();
```

A wrapper class to make easier sending emails via [MailGun API](https://mailgun.com).

It was originally written as a stand-alone library but incorporated to SeedPHP since its version 1.1.0.

### Dependencies

- Parsedown (for parsing markdown files)
- Twig (for rendering templates)

### Methods

#### constructor( string<apiKey> : required, string<domain> : optional, string<emailDefaultReplacement> : optional, array<whitelist> : optional ) : \SeedPHP\Helper\Mailgun


Initialize the helper.

In order to use this helper, you need to sign up for a (at least) free account with [Mailgun](https://mailgun.com). Once signed up, you'll 
be able to retrieve your account API key from Mailgun's domain dashboard.

#### getRecipients() : array<string>

List all recipients previously set to a message.

#### setWhiteList( array<string> : required ) : \SeedPHP\Helper\Mailgun

Sets the addresses whitelisted to dispatch messages for real. When in use 
this helper will only send messages to white-listed email addresses or domains.

Supported formats are:

`name@domain.tld` or <br >
`*@domain.tld` (a domain wildcard, similar to a catch-all)

#### setEmailDefaultReplacement( string<email> : required ) : \SeedPHP\Helper\Mailgun

Set the default email for replacement. 

Always it is provided, all emails from $this->_recipients are gonna be replaced by this address unless they are also whitelisted at `$this->_whitelist`.

#### getInfo() : object

Retrives the meta-information from the cURL call.

#### setDomain( string<domain> : required ) : \SeedPHP\Helper\Mailgun

Sets the domain (based on your account at Mailgun)

#### setFrom( string<email> : required, string<name> : required ) : \SeedPHP\Helper\Mailgun

Sets the sender

#### setTo( string<email> : required, string<name> : optional, boolean<reset> : optional ) : \SeedPHP\Helper\Mailgun

Sets the recipient. 

Multiple recipients are supported, just call this multiple time.

The third argument, when true, reset the recipient list before set the new address.

#### setSubject( string<subject> : required ) : \SeedPHP\Helper\Mailgun

Sets the message subject.

#### setMessage( string<subject> : required ) : \SeedPHP\Helper\Mailgun

Sets the message body to be sent.

#### setTimeout( integer<seconds> : required ) : \SeedPHP\Helper\Mailgun

Sets the request timeout.

#### parse( string<filepath_or_string> : required, array<vars> : optional, string<parse_type> : optional, string<template_type> : optional ) : string

Parses the message content from templates, replacing whatever has been definied by `setMessage`. 
The templates can be written in either `plain text`, `HTML`, `Markdown` or `Twig` syntaxes and can be loaded either from `files` or `strings`.

Supported parser types:
- markdown (default)
- file
- string
- twig (since version `1.2.0`)

Supported template types: (since version `1.3.0`)
- file (default)
- string

Returns the rendered template.

#### send() : JSON

Sends the message.

#### fire() : JSON

Sends the message. This is an alias for $this->send().

#### getAttachments() : array

Get the attachments list.

#### addAttachment( string<filePath> : required, string<fileName> : required, string<fileContentType> : optional ) : integer|boolean

Adds an attachments.

#### deleteAttachment(integer<id> : required ) : \SeedPHP\Helper\Mailgun

Removes an attachments.

#### clearAttachments() : \SeedPHP\Helper\Mailgun

Clear all the attachments.

#### clearRecipients() : \SeedPHP\Helper\Mailgun

Clear all the recipients.

#### setReplyTo( string<email> : required, string<name> : optional ) : \SeedPHP\Helper\Mailgun

Sets the reply-to address.
