# Seed-PHP Microframework

## Mailgun `Beta`

>Package: `Helper\Mailgun` <br >
>Namespace: `SeedPHP\Helper`

!> You must have an account on Mailgun to use this helper. <br >
If you don't have an account yet, [get started for FREE](https://signup.mailgun.com/new/signup).

A wrapper class to simplify the way your app sends emails via [MailGun API](https://mailgun.com). It was originally written as a stand-alone library but got incorporated to Seed-PHP since its version `1.1.0`.

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

## Dependencies

- [Parsedown](https://packagist.org/packages/erusev/parsedown) (for parsing markdown files)
- [Twig](https://packagist.org/packages/twig/twig) (for rendering templates)

## Methods

---

### <span style="color: #42b983;">#</span> constructor( apiKey [, domain, emailDefaultReplacement, whitelist] )


Initialize the helper.

> The API key can be retrieved from Mailgun's domain dashboard.

##### Arguments

- `{String} apiKey: required`
- `{String} domain: optional`
- `{String} emailDefaultReplacement: optional`
- `{String} whitelist: optional`

##### Return

- `\SeedPHP\Helper\Mailgun`

---

### <span style="color: #42b983;">#</span> getRecipients()

List all recipients previously set to a message.

##### Return

- `{ Array }`

---

### <span style="color: #42b983;">#</span> setWhiteList( list )

Sets the addresses whitelisted to dispatch messages for real. When in use this helper will only send messages to white-listed email addresses or domains.

> **Supported formats are:** <br >
> `name@domain.tld` or <br >
> `*@domain.tld` (a domain wildcard, similar to a catch-all)

##### Arguments

- `{Array} list: required`

##### Return

- `\SeedPHP\Helper\Mailgun`

---

### <span style="color: #42b983;">#</span> setEmailDefaultReplacement( email )

Set the default email for replacement. 

Always it is provided, all emails from `$this->_recipients` are gonna be replaced by this address unless they are also whitelisted at `$this->_whitelist`.

##### Arguments

- `{String} email: required`

##### Return

- `\SeedPHP\Helper\Mailgun`

---

### <span style="color: #42b983;">#</span> getInfo() : object

Retrives the meta-information from the cURL call.


##### Return

- `{ Object }`

---

### <span style="color: #42b983;">#</span> setDomain( domain ) 

Sets the domain (based on your account at Mailgun)

##### Arguments

- `{String} domain: required`

##### Return

- `\SeedPHP\Helper\Mailgun`

---

### <span style="color: #42b983;">#</span> setFrom( email, name )

Sets the sender

##### Arguments

- `{String} email: required`
- `{String} name: required`

##### Return

- `\SeedPHP\Helper\Mailgun`

---

### <span style="color: #42b983;">#</span> setTo( email [, name, reset] )

Sets the recipient. The third argument, when true, reset the recipient list before set the new address.

> Multiple recipients are supported, just call this multiple time.


##### Arguments

- `{String} email: required`
- `{String} name: optional`
- `{Boolean} reset: optional`

##### Return

- `\SeedPHP\Helper\Mailgun`

---

### <span style="color: #42b983;">#</span> setSubject( subject )

Sets the message subject.

##### Arguments

- `{String} subject: required`

##### Return

- `\SeedPHP\Helper\Mailgun`

---

### <span style="color: #42b983;">#</span> setMessage( body )

Sets the message body to be sent.

##### Arguments

- `{String} body: required`

##### Return

- `\SeedPHP\Helper\Mailgun`

---

### <span style="color: #42b983;">#</span> setTimeout( seconds )

Sets the request timeout.

##### Arguments

- `{Integer} seconds: required`

##### Return

- `\SeedPHP\Helper\Mailgun`

---

### <span style="color: #42b983;">#</span> parse( filepathOrString [, vars, parseType, templateType] )

Parses the message content from templates, replacing whatever has been definied by `setMessage`. The templates can be written in either `plain text`, `HTML`, `Markdown` or `Twig` syntaxes and can be loaded either from `files` or `strings`.

**Supported parser types:** <br >
- markdown (default)
- file
- string
- twig (since version `1.2.0`)


**Supported template types: (since version `1.3.0`)**
- file (default)
- string

Returns the rendered template.

##### Arguments

- `{String} filepathOrString: required`
- `{Array} vars: optional`
- `{String} parseType: optional`
- `{String} templateType: optional`

##### Return

- `{ String }`

---

### <span style="color: #42b983;">#</span> send()

Sends the message.

##### Return

- `{ JSON }`

---

### <span style="color: #42b983;">#</span> fire()

> This is an alias for `$this->send()`.

Sends the message. 


##### Return

- `{ JSON }`

---

### <span style="color: #42b983;">#</span> getAttachments() : array

Get the attachments list.

##### Return

- `{ Array }`

---

### <span style="color: #42b983;">#</span> addAttachment( filePath, fileName [, fileContentType] )

Adds an attachments.

##### Arguments

- `{String} filePath: required`
- `{String} fileName: required`
- `{String} fileContentType: optional`

##### Return

- `{ Integer | Boolean }`

---

### <span style="color: #42b983;">#</span> deleteAttachment(integer<id> : required )

Removes an attachments.

##### Arguments

- `{Integer} id: required`

##### Return

- `\SeedPHP\Helper\Mailgun`

---

### <span style="color: #42b983;">#</span> clearAttachments()

Clear all the attachments.

##### Return

- `\SeedPHP\Helper\Mailgun`

---

### <span style="color: #42b983;">#</span> clearRecipients()

Clear all the recipients.

##### Return

- `\SeedPHP\Helper\Mailgun`

---

### <span style="color: #42b983;">#</span> setReplyTo( email [, name] )

Sets the reply-to address.

##### Arguments

- `{String} email: required`
- `{String} name: optional`

##### Return

- `\SeedPHP\Helper\Mailgun`

---

### <span style="color: #42b983;">#</span> setTracking( [enable, clicks, opens] )

Instructs Mailgun to track or not track the message activities. When `enable` is set to false, the other arguments are ignored.

##### Arguments

- `{Boolean} enable: optional`
- `{Boolean} clicks: optional`
- `{Boolean} opens: optional`

##### Return

- `\SeedPHP\Helper\Mailgun`

---

### <span style="color: #42b983;">#</span> setTag( tag [, reset] )

Set a tag to be attached in the message. Multiple tags are supported. When `reset` is set to true, the list of tags is cleared.

##### Arguments

- `{String} tag: required`
- `{Boolean} reset: optional`

##### Return

- `\SeedPHP\Helper\Mailgun`

---

### <span style="color: #42b983;">#</span> setCustomVar( key, val [, reset] )

Attach a custom variable (JSON style) which will be stored on Mailgun alongside the message and returned within the payload when any webhook is called. Multiple variables are supported.

When `reset` is set to true, the list of tags is cleared.

##### Arguments

- `{String} key: required`
- `{String} value: required`
- `{Boolean} reset: optional`

##### Return

- `\SeedPHP\Helper\Mailgun`
