# Bustle WordPress Exporter

WordPress model exporter for Bustle

## Usage

### HTTP Endpoints

The JSON representations of WordPress converted models can be accessed via HTTP requests to either the native WP JSON API like `https://domain.tld/wp-json/bustle/{model}/{id}` e.g. `/wp-json/bustle/post/1774807` or a custom permalink endpoint like `https://domain.tld/slug/bustle/`

### WP CLI

There is also a WP CLI command to output the models.

`wp bustle-exporter export posts --ids 1234,1235`

### Invoking Directly

If you want to convert models on your own, simply instantiate the model, and call `get_model()`;

```
$bustle_post = new \Bustle\Post( $post );
return $bustle_post->get_model();
```
### Mobiledoc only

If you want to just convert your post content to the Mobiledoc format for export to another Mobiledoc-capable system, you can call: `new \Bustle\Mobiledoc\Wrapper( $post )`

## Filters

The plugin provides numerous filters throughout the request cycle. Many of these are dynamic.

* To change the value of a model's property specifically, use
`bustle_exporter_{$model_name}_attribute_{$key}` -> `bustle_exporter_post_attribute_title`

* These values run through a sanitization function that defaults to `esc_attr`, but you can register your own callback with:
`bustle_exporter_{$model_name}_attribute_{$key}_sanitizer` with an example of: 
`add_filter( 'bustle_exporter_post_attribute_primaryMediaURL_sanitizer', 'esc_url' );`

* There is also a catch-all filter before the model is returned that overrides the rest:
`bustle_exporter_{$model_name}_model`

* The wp-json API is on by default, but you can deactivate the endpoint with" 
`add_filter('bustle_exporter_api_active', '__return_false');`

* The custom rewrite endpoint is also able to be deactivated with:" 
`add_filter('bustle_exporter_endpoint_active', '__return_false');`
