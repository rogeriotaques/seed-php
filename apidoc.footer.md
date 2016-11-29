### Advanced usage

The object returned by the API, by default, has 2 properties.

```
{
  status: 200,
  data: []
}
```

In really special cases, you might need to customize this properties names. 

So, you can do it by passing some special parameters to the API via query string.

| Param | Type | Default | Remark |
|-------|------|---------|--------|
| _router_status | string | 'status' | A custom name for the http status code property. |
| _router_data | string | 'data' | A custom name for the returned data property. |

E.g:

```
https://lyrebird.abtz.co/users?_router_status=statusCode&_router_data=results&...
```

This will result in the following structure:

```
{
  statusCode: 200,
  results: []
}
```

### Support

Have any question or suggestion?

Drop me an email and I'll do my best to answer as soon as possible.

rogerio.taques [at] gmail.com
