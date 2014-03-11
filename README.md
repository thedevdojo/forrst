Forrst API
======

A Forrst API Wrapper Package for Laravel 4

## Installation

Begin by installing this package through Composer. Edit your project's `composer.json` file to require `devdojo/forrst`.

	"require": {
		"devdojo/forrst": "dev-master"
	}

Next, update Composer from the Terminal:

    composer update

Once this operation completes, the final step is to add the service provider. Open `app/config/app.php`, and add a new item to the providers array.

    'Devdojo\Forrst\ForrstServiceProvider',

That's it! You're all set to go.

## Usage

Here's an example of how to get the most recent posts.

```php
<?php $forrst = Forrst::listPosts('snap', 'recent', 30); ?>

@foreach($forrst->resp->posts as $post)
  
  print_r($post);
  
@endforeach
```

For further info on using the Forrst API, be sure to checkout: http://forrst.com/api

Hope you enjoy :)
