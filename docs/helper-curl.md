# Seed-PHP Microframework

## cUrl

>Package: `Helper\Curl` <br >
>Namespace: `SeedPHP\Helper`

Gives you a simple wrapper to work with curl calls. <br>
An usefull way to make your API consumes third party APIs on background.

#### Example

```php
  $app->load('curl');
  $res = $app->curl->get( 'http://...' );
```

---

### <span style="color: #42b983;">#</span> create( url [, options, returnType] )

Resets and points the Curl to a new URL.

##### Arguments

- `{String} url: required`
- `{Array} options: optional`
- `{String} returnType: optional`

##### Return

- `\SeedPHP\Helper\Curl`

---

### <span style="color: #42b983;">#</span> data( options )

Appends a data object to the call.

##### Arguments

- `{Array} options: required`

##### Return

- `\SeedPHP\Helper\Curl`

---

### <span style="color: #42b983;">#</span> option( key, value )

Allows to add new custom options.

##### Arguments

- `{String} key: required`
- `{String} value: required`

##### Return

- `\SeedPHP\Helper\Curl`

---

### <span style="color: #42b983;">#</span> proxy( url, username, password)

Whenever your network goes thru proxy, you should provide it here.

##### Arguments

- `{String} url: required`
- `{String} username: required`
- `{String} password: required`

##### Return

- `\SeedPHP\Helper\Curl`

---

### <span style="color: #42b983;">#</span> cookies( params )

Set cookies for the call.

##### Arguments

- `{Array} params: required`

##### Return

- `\SeedPHP\Helper\Curl`

---

### <span style="color: #42b983;">#</span> credential( username, password )

Set credentials for your endpoint authentication.

##### Arguments

- `{String} username: required`
- `{String} password: required`

##### Return

- `\SeedPHP\Helper\Curl`

---

### <span style="color: #42b983;">#</span> header( header )

Allows you to add additional headers to the call.

##### Arguments

- `{String} header: required`

##### Return

- `\SeedPHP\Helper\Curl`

---

### <span style="color: #42b983;">#</span> get( [url, options] )

Performs a GET call

##### Arguments

- `{String} url: optional`
- `{Array} options: optional`

##### Return

- `\SeedPHP\Helper\Curl`

---

### <span style="color: #42b983;">#</span> post( [url, options] )

Performs a POST call.

##### Arguments

- `{String} url: optional`
- `{Array} options: optional`

##### Return

- `\SeedPHP\Helper\Curl`

---

### <span style="color: #42b983;">#</span> put( [url, options] )

Performs a PUT call.

##### Arguments

- `{String} url: optional`
- `{Array} options: optional`

##### Return

- `\SeedPHP\Helper\Curl`

---

### <span style="color: #42b983;">#</span> update( [url, options] )

Performs an UPDATE call.

##### Arguments

- `{String} url: optional`
- `{Array} options: optional`

##### Return

- `\SeedPHP\Helper\Curl`

---

### <span style="color: #42b983;">#</span> delete( [url, options] )

Performs a DELETE call.

##### Arguments

- `{String} url: optional`
- `{Array} options: optional`

##### Return

- `\SeedPHP\Helper\Curl`

---

### <span style="color: #42b983;">#</span> run( method, url [, options] )

Performs a general call.

##### Arguments

- `{String} method: optional`
- `{String} url: required`
- `{Array} options: optional`

##### Return

- `\SeedPHP\Helper\Curl`
