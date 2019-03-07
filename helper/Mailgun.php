<?php

 /* --------------------------------------------------------
 | Seed-PHP Microframework
 | @author Rogerio Taques (hello@abtz.co)
 | @version 1.1.0
 | @license MIT
 | @see http://github.com/abtzco/seed-php
 * -------------------------------------------------------- */

namespace SeedPHP\Helper;

/**
 * Original copy. Incorporated to SeedPHP since 1.1.0.
 * 
 * A wrapper class to make easier and flexible sending emails
 * via MailGun (https://mailgun.com) API.
 *
 * This library depends on Parsedown to render Markdown template files. Do
 * install it via composer (erusev/parsedown). And load the library
 * (require_once '/vendor/autoload.php') somewhere before include this file;
 *
 * @see https://documentation.mailgun.com/en/latest/quickstart-sending.html#send-via-api
 * @author Rogerio Taques <hello@abtz.co>, Tadashi Neves <tadasshi@gmail.com>
 */

class Mailgun
{
  private $_emailPattern = '/[^a-zA-Z0-9.\-@+_]/';  // Since 1.4.1. Regular Expression to Sanitize email addresses.
  private $_apiKey = '';                            // Mailgun API key
  private $_apiBase = '';                           // Mailgun API address
  private $_sender = '';                            // Who is the sender
  private $_recipients = [];                        // The recipients list
  private $_subject = '';                           // The message subject
  private $_message = '';                           // Either plain text or HTML.
  private $_domain = '';                            // The target domain
  private $_encoding = '';                          // The encoding charset
  private $_info = null;                            // Aways a call is made, its info is made available here
  private $_attachments = [];                       // The attachments list
  private $_headers = [];                           // The message headers, if any
  private $_replyTo = null;                         // The reply-to address
  private $_timeout = 20;                           // Request timeout

  // The white list for emails address (if given, no other address will get messages dispatched)
  // @since 1.3.0
  private $_whitelist = [];

  // The default email replacement (when given, all emails not in the white list will be replaced by this)
  // @since 1.3.0
  private $_emailDefaultReplacement = null;

  /**
   * Since 1.4.0 this library is supporting wildcard for white-listed emails. Eg: *@example.com.
   * @param string $addr
   * @return boolean
   */
  private function _isTLDWhiteListed($addr = '')
  {
    if (empty($this->_whitelist)) {
      return false;
    }

    $wildcard = preg_replace('/(.*)@(.*)/', '*@$2', $addr);

    if (!in_array($wildcard, $this->_whitelist)) {
      return false;
    }

    return true;
  } // _isTLDWhiteListed

  /**
   * Constructs an instance of Mailgun Class.
   * @param string $apiKey
   * @param string $domain Your domain configured in your Mailgun account.
   * @param string $emailDefaultReplacement A default email to replace any other when testing
   * @param string $whitelist A white list of emails to really dispatch messages when developing/ testing
   * @return Mailgun
   */
  function __construct($config = ['apiKey' => '', 'domain' => null, 'emailDefaultReplacement' => null, 'whitelist' => null])
  {
    if (empty($config['apiKey'])) {
      throw new \Exception('Missing the API key. You can get it from your Mailgun account dashboard.');
    }

    $this->_apiKey = $config['apiKey'];
    $this->_apiBase = 'https://api.mailgun.net/v3/';
    $this->_encoding = 'UTF-8';

    $this->setWhiteList(!empty($config['whitelist']) ? $config['whitelist'] : []);
    $this->setEmailDefaultReplacement(!empty($config['emailDefaultReplacement']) ? $config['emailDefaultReplacement'] : []);

    if (!empty($config['domain'])) {
      $this->_domain = $config['domain'];
    }

    return $this;
  } // __construct

  /**
   * Return the list of recipients
   * @since 1.3.0
   * @return array<string>
   */
  public function getRecipients()
  {
    return $this->_recipients;
  }

  /**
   * Sets the addresses whitelisted to dispatch messages for real.
   * @since 1.3.0
   * @param array<string> $whitelist
   * @return Mailgun
   */
  public function setWhiteList($whitelist = [])
  {
    if (is_array($whitelist)) {
      $this->_whitelist = $whitelist;
    }

    return $this;
  }

  /**
   * Set the default email for replacement.
   *
   * Always it is provided, all emails from $this->_recipients are gonna be replaced by this address
   * unless they are also whitelisted at $this->_whitelist.
   *
   * @since 1.3.0
   * @param string $email
   * @return Mailgun
   */
  public function setEmailDefaultReplacement($email = '')
  {
    if (empty($email)) {
      return $this;
    }

    $email = preg_replace($this->_emailPattern, '', $email);
    $this->_emailDefaultReplacement = $email;

    return $this;
  }

  /**
   * Retrives the meta-information from the cURL call.
   * @return object
   */
  public function getInfo()
  {
    return $this->_info;
  } // getInfo

  /**
   * Sets the domain (based on your account at Mailgun)
   * @param string $domain
   * @return Mailgun
   */
  public function setDomain($domain = '')
  {
    if (empty($domain)) {
      throw new \Exception('Missing account domain.');
    }

    $this->_domain = $domain;

    return $this;
  } // setDomain

  /**
   * Sets the sender
   * @param string $email
   * @param string $name
   * @return Mailgun
   */
  public function setFrom($email = '', $name = '')
  {
    if (empty($email) || empty($name)) {
      throw new \Exception('Missing sender address or name.');
    }

    $email = preg_replace($this->_emailPattern, '', $email);

    if (!empty($name)) {
      $email = "{$name} <$email>";
    }

    $this->_sender = $email;

    return $this;
  } // setFrom

  /**
   * Sets the recipient. Multiple recipients are supported, just call this multiple time.
   * @param string $email
   * @param string $name
   * @param boolean $reset
   * @return Mailgun
   */
  public function setTo($email = '', $name = null, $reset = false)
  {
    if (empty($email)) {
      return $this;
    }

    $email = preg_replace($this->_emailPattern, '', $email);
    $rawEmail = $email;

    if (!empty($name)) {
      $email = "{$name} <$email>";
    }

    // Sometimes it's necessary to reset the recipients list.
    if ($reset !== false) {
      $this->_recipients = [];
    }

    if (!empty($this->_whitelist)) {
      if (!empty($this->_emailDefaultReplacement)) {
        if (!in_array($rawEmail, $this->_whitelist) && !$this->_isTLDWhiteListed($rawEmail)) {
          $email = $this->_emailDefaultReplacement;
        } // if (!in_array($rawEmail, $this->_whitelist))
      } elseif (!in_array($rawEmail, $this->_whitelist) && !$this->_isTLDWhiteListed($rawEmail)) {
        return $this;
      } // if (empty($this->_emailDefaultReplacement))
    } elseif (!empty($this->_emailDefaultReplacement)) {
      $email = $this->_emailDefaultReplacement;
    } // if (empty($this->_whitelist))

    if (!in_array($email, $this->_recipients)) {
      $this->_recipients[] = $email;
    }

    return $this;
  } // setTo

  /**
   * Sets the message subject.
   * @param string $string
   * @return Mailgun
   */
  public function setSubject($string = '')
  {
    if (empty($string)) {
      $string = '';
    }

    $this->_subject = $string;

    return $this;
  } // setSubject

  /**
   * Sets the message body to be sent.
   * @param string $string
   * @return Mailgun
   */
  public function setMessage($string = '')
  {
    if (empty($string)) {
      throw new \Exception('Message should not be empty.');
    }

    $this->_message = $string;

    return $this;
  } // setMessage

  /**
   * Sets the request timeout.
   * @param string $string
   * @return Mailgun
   */
  public function setTimeout($seconds = 20)
  {
    if (empty($seconds) || !is_numeric($seconds)) {
      throw new \Exception('Request timeout must not be empty and must be an integer.');
    }

    $this->_timeout = $seconds;
    return $this;
  } // setMessage

  /**
   * Emails content can be gathered from template files or strings. The templates can be written in
   * either HTML or Markdown. This replaces any content set via "setMessage".
   *
   * @param string $filepath_or_string Either a template file path or template string.
   * @param array $vars Array<Key => Value>. Replaces strings in the template. E.g: {THIS_IS_A_VAR}
   * @param string $parse_type Either "markdown", "file" or "string" (when a string is given in the first argument).
   * @return string
   */
  public function parse($filepath_or_string = '', $vars = [], $parse_type = 'markdown')
  {
    $temp = '';
    $parsers = ['file', 'string', 'markdown'];

    if (!in_array($parse_type, $parsers)) {
      throw new \Exception('Given parse type "' . $parse_type . '" is not allowed.');
    }

    // Try to load the content from a template file
    if ($parse_type !== 'string' && file_exists($filepath_or_string)) {
      $temp = file_get_contents($filepath_or_string);
    }

    // The template is given as string
    else {
      $temp = $filepath_or_string;
    }

    // Replaces the variables in the template, if any.
    if (count($vars) > 0) {
      foreach ($vars as $key => $value) {
        $temp = str_replace('{' . $key . '}', $value, $temp);
      }
    }

    // Try to parse a markdown file into HTML
    if (!empty($temp) && $parse_type === 'markdown') {
      $pd = new \Parsedown();
      $temp = $pd->text($temp);
    }

    // Give the parsed template to the content
    $this->_message = $temp;

    return $this;
  } // parse

  /**
   * Sends the message.
   * @return JSON
   */
  public function send()
  {

    $data = [
      'from' => $this->_sender,
      'to' => implode(',', $this->_recipients),
      'subject' => $this->_subject,
      'html' => $this->_message,
      'text' => strip_tags($this->_message),

      // 'o:tracking'=>'yes',
      // 'o:tracking-clicks'=>'yes',
      // 'o:tracking-opens'=>'yes',
      // 'o:tag'=>$tag,
    ];

    $curl = curl_init($this->_apiBase . $this->_domain . '/messages');

    // Add the reply-to address (@since 1.2.0)
    if ($this->_replyTo) {
      $data['h:Reply-To'] = $this->_replyTo;
    }

    // Add headers to the call, if any (@since 1.2.0)
    if (sizeof($this->_headers) > 0) {
      curl_setopt($curl, CURLOPT_HTTPHEADER, $this->_headers);
    } else {
      curl_setopt($curl, CURLOPT_HEADER, false);
    }

    if (sizeof($this->_attachments) > 0) {
      foreach ($this->_attachments as $key => $att) {
        $data['attachment[' . ($k + 1) . ']'] = curl_file_create(
          $att['path'],
          $att['type'],
          $att['name']
        );
      }
    }

    curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($curl, CURLOPT_USERPWD, 'api:' . $this->_apiKey);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    curl_setopt($curl, CURLOPT_ENCODING, $this->_encoding);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->_timeout);

    $response = curl_exec($curl);

    $this->_info = curl_getinfo($curl);

    curl_close($curl);

    $results = json_decode($response, true);

    return $results;
  } // send

  /**
   * Alias for Mailgun::send()
   * @return JSON
   */
  public function fire()
  {
    return $this->send();
  } // fire

  /**
   * Get the attachments list.
   * @since 1.2.0
   * @return array
   */
  public function getAttachments()
  {
    return $this->_attachments;
  } // getAttachments

  /**
   * Adds an attachments.
   * @since 1.2.0
   * @param string $filePath
   * @param string $fileName
   * @return integer|false
   */
  public function addAttachment($filePath = '', $fileName = '', $fileContentType = 'application/pdf')
  {
    if (!empty($fileName) && !empty($filePath)) {
      $id = sizeof($this->_attachments);

      if ($id === 0) {
        $this->_headers['multipart'] = 'Content-Type: multipart/form-data;';
      }

      // $this->_attachments[$id] = "@{$filePath};filename={$fileName};";
      $this->_attachments[$id] = [
        'name' => $fileName,
        'path' => $filePath,
        'type' => $fileContentType
      ];

      return $id;
    }

    return false;
  } // addAttachment

  /**
   * Removes an attachments.
   * @since 1.2.0
   * @param integer $id
   * @return Mailgun
   */
  public function deleteAttachment($id = null)
  {
    if (!$id || !isset($this->_attachments[$id])) {
      return $this;
    }

    unset($this->_attachments[$id]);

    if (sizeof($this->_attachments) === 0) {
      unset($this->_headers['multipart']);
    }

    return $this;
  } // deleteAttachment

  /**
   * Clear all attachments.
   * @since 1.2.0
   * @return Mailgun
   */
  public function clearAttachments()
  {
    $this->_attachments = [];

    if (isset($this->_headers['multipart'])) {
      unset($this->_headers['multipart']);
    }

    return $this;
  } // clearAttachments

  /**
   * Clear all recipients.
   * @since 1.2.0
   * @return Mailgun
   */
  public function clearRecipients()
  {
    $this->_recipients = [];
    return $this;
  } // clearRecipients

  /**
   * Sets the reply-to address.
   * @param string $email
   * @param string $name
   * @return Mailgun
   */
  public function setReplyTo($email = '', $name = '')
  {
    if (!empty($email)) {
      $email = preg_replace($this->_emailPattern, '', $email);

      if (!empty($name)) {
        $email = "{$name} <$email>";
      }

      $this->_replyTo = $email;
    }

    return $this;
  }
} // Mailgun
