Smart Template Engine
===

A class that handles templates in plugins.

How it works
---

The class searches for files in several places allowing users of your plugin to create their of templates, and for you to include several themes for your plugin as well as add themes later.

The search order:
1. First it searches for the requested template file in the child theme folder
2. If nothing was found, it tries the theme folder.
3. If that fails it looks for the files in a public theme folder (your-plugin-slug-themes/active-theme-slug), which is located in 'wp-content'
4. If that fails it looks in the plugin theme folder (your-plugin-path/themes/active-theme-slug).
5. If that fails aswell, it tries to find it in a folder called "templates" in the plugin folder. If nothing is located still, the file probably does not exist.

Example
---

```
require_once 'template-engine/template.php';

$myPluginTemplates = new Smart_Template_Engine(
    'my-plugin-slug',
    '/path/to/wp-content/plugins/myplugin/',
    'http://example.com/wp-content/plugins/myplugin/',
    array(
        'my-post-type' => array( 'my-single.php', 'or-my-single.php' ),
        'my-other-post-type' => array( 'i-like-dem-apples.php' )
    ),
    array(
        'my-post-type' => array( 'my-archive.php' ),
        'my-other-post-type' => array( 'all-my-apples.php' )
    ),
    array(
        'my-taxonomy' => array( 'my-taxonomy.php' )
    ),
    array(
        'custom-style.css', 'replace-this.css'
    )
);

// Find a file
$template_file = smart_get_template_part($myPluginTemplate, 'fish', 'chips');

```

The arguments, broken down:
---
```
'my-plugin-slug'
```

The slug you want for your plugin.


```
'/path/to/wp-content/plugins/myplugin/'
```

The path to your plugin folder. For example you could just insert the function `plugin_dir_path( __FILE__ )` if the code is located in your main plugin file.

```
'http://example.com/wp-content/plugins/myplugin/',
```

The path to your plugin folder. For example you could just insert the function `plugin_dir_url( __FILE__ )` if the code is located in your main plugin file.

```
array(
    'my-post-type' => array( 'my-single.php', 'or-my-single.php' ),
    'my-other-post-type' => array( 'i-like-dem-apples.php' )
),
```

These are the template files for your post type singles. The first element in the array is always the first to be searched for. When it is not found it tries to find the second one. Falls back to the default WordPress template system if nothing is found.

```
array(
    'my-post-type' => array( 'my-archive.php' ),
    'my-other-post-type' => array( 'all-my-apples.php' )
),
```

These are the template files for your post type archives. The first element in the array is always the first to be searched for. When it is not found it tries to find the second one. Falls back to the default WordPress template system if nothing is found.

```
array(
    'my-taxonomy' => array( 'my-taxonomy.php' )
)
```

These are the template files for your taxonomies. The first element in the array is always the first to be searched for. When it is not found it tries to find the second one. Falls back to the default WordPress template system if nothing is found.

```
array(
    'custom-style.css', 'replace-this.css'
)
```

These are the stylesheets you want to enqueue. Each entry in the array represents an indidual stylesheet.
