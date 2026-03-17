# Sledgehammer MVC

MVC aka Model View Controller

## Model

These are the classes you'll write yourself.
If your looking for database persistance for your models check out the Sledgehammer ORM module.

## View

The "View" is a composition of components.

Every component has a "render" function which sends the output directly to the browser (echo) and doesn't have a return value.

### Component->getHeaders()

A Component has an optional getHeaders() function, which is called before render() and passes info to the components higher in the component hierarchy.
This can add "HTTP headers", append stylesheets and other configuration that must be injected into the `<head>`

'http' This is an array that will be sent with the header() function.
'meta' This is an array that is added as `<meta>` tag(s) in the `<head>`
'css' This is an array with urls that are added as `<link type="text/css">` in the `<head>`
'link' This is an array that is added as `<link>` tag(s) in the `<head>`
'title' This is the `<title>` that is placed in the `<head>`

## Controller

Sledgehammer doesn't have a Routing class, all requests are handled by a subclass of [Website](src/Website.php) but modularity is achieved by using [Folder](src/Folder.php) classes.

### Mapping examples:

The "/about.html" url is mapped to MyWebsite->about().
If no public _about_ method is found the file() method is called.
By default the file() returns an component that renders a 404 error.

The "/blog/author.html" is mapped to `MyWebsite->blog_folder()`.
if no public _blog_ method is found the `folder()` method is called.

The `blog_folder()` could directly return a compontent, but it could also create a Folder object which would handle all request inside the "blog/" folder.
The "author.html" part of url is mapped to `MyBlogFolder->author()`.
If no public _author_ method is found the file() method is called on the MyBlogFolder.

### Website scope

Complete handling of requests.

Sending to browser
Saving to disk

### HtmlDocument scope

Processing the values of getHeaders() in the doctype template.

## Installation

Place the mvc folder in the same folder as Sledgehammer's core folder.

To generate a scaffolding for an MVC project, run

```sh
php sledgehammer/utils/empty_project.php
```

## Twitter Bootstrap

Contrains all the css & javascript from: http://twitter.github.com/bootstrap/ and adds Sledgehammer\View classes.

```php
$pagination = new Pagination(5, 1);
```

Becomes:

```html
<div class="pagination">
  <ul>
    <li class="disabled"><a href="#">«</a></li>
    <li class="active"><a href="?page=1">1</a></li>
    <li><a href="?page=2">2</a></li>
    <li><a href="?page=3">3</a></li>
    <li><a href="?page=4">4</a></li>
    <li><a href="?page=5">»</a></li>
  </ul>
</div>
```
