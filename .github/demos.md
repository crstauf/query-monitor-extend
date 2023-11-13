# Demos

Demos of QMX are available via the [WordPress Playground](https://developer.wordpress.org/playground/):

- [Install as plugin](https://playground.wordpress.net/#%7B%22landingPage%22:%22/wp-admin/plugins.php%22,%22steps%22:%5B%7B%22step%22:%22login%22,%22username%22:%22admin%22,%22password%22:%22password%22%7D,%7B%22step%22:%22installPlugin%22,%22pluginZipFile%22:%7B%22resource%22:%22wordpress.org/plugins%22,%22slug%22:%22query-monitor%22%7D%7D,%7B%22step%22:%22installPlugin%22,%22pluginZipFile%22:%7B%22resource%22:%22url%22,%22url%22:%22https://calebstauffer.wpengine.com/plugin-proxy.php?repo=crstauf/query-monitor-extend&name=plugin.zip%22,%22caption%22:%22Installing%20Query%20Monitor%20Extend%22%7D%7D%5D%7D)
- [Install as mu-plugin](https://playground.wordpress.net/#%7B%22landingPage%22:%22/wp-admin/plugins.php?plugin_status=mustuse%22,%22steps%22:%5B%7B%22step%22:%22login%22,%22username%22:%22admin%22,%22password%22:%22password%22%7D,%7B%22step%22:%22installPlugin%22,%22pluginZipFile%22:%7B%22resource%22:%22wordpress.org/plugins%22,%22slug%22:%22query-monitor%22%7D%7D,%7B%22step%22:%22mkdir%22,%22path%22:%22/wordpress/qmx%22%7D,%7B%22step%22:%22writeFile%22,%22path%22:%22/wordpress/qmx/mu-plugin.zip%22,%22data%22:%7B%22resource%22:%22url%22,%22url%22:%22https://calebstauffer.wpengine.com/plugin-proxy.php?repo=crstauf/query-monitor-extend&name=mu-plugin.zip%22,%22caption%22:%22Downloading%20Query%20Monitor%20Extend%22%7D,%22progress%22:%7B%22weight%22:2,%22caption%22:%22Installing%20Query%20Monitor%20Extend%22%7D%7D,%7B%22step%22:%22unzip%22,%22zipPath%22:%22/wordpress/qmx/mu-plugin.zip%22,%22extractToPath%22:%22/wordpress/qmx%22%7D,%7B%22step%22:%22mv%22,%22fromPath%22:%22/wordpress/qmx/mu-plugins/query-monitor-extend%22,%22toPath%22:%22/wordpress/wp-content/mu-plugins/query-monitor-extend%22%7D,%7B%22step%22:%22mv%22,%22fromPath%22:%22/wordpress/qmx/mu-plugins/load-qmx.php%22,%22toPath%22:%22/wordpress/wp-content/mu-plugins/load-qmx.php%22%7D%5D%7D)

## URL Generation

WordPress Playground URLs are generated using the below JavaScript, available at https://jsfiddle.net/crstauf/m54cdobk/.

```js
const muplugin = false;

let zipArtifactUrl = 'https://calebstauffer.wpengine.com/plugin-proxy.php?repo=crstauf/query-monitor-extend&name=plugin.zip';

// Install as plugin.
let blueprint = {
  landingPage: '/wp-admin/plugins.php',
  steps: [
    {
      step: 'login',
      username: 'admin',
      password: 'password',
    },
    {
      step: 'installPlugin',
      pluginZipFile: {
        resource: 'wordpress.org/plugins',
        slug: 'query-monitor',
      },
    },
    {
      step: 'installPlugin',
      pluginZipFile: {
        resource: 'url',
        url: zipArtifactUrl,
        caption: "Installing Query Monitor Extend",
      },
    },
  ],
};

// Install as mu-plugin.
if ( muplugin ) {
  zipArtifactUrl = 'https://calebstauffer.wpengine.com/plugin-proxy.php?repo=crstauf/query-monitor-extend&name=mu-plugin.zip';

  blueprint.landingPage = '/wp-admin/plugins.php?plugin_status=mustuse';

  blueprint.steps = [
    {
      step: 'login',
      username: 'admin',
      password: 'password',
    },
    {
      step: 'installPlugin',
      pluginZipFile: {
        resource: 'wordpress.org/plugins',
        slug: 'query-monitor',
      },
    },
    {
      step: 'mkdir',
      path: '/wordpress/qmx',
    },
    {
      step: 'writeFile',
      path: '/wordpress/qmx/mu-plugin.zip',
      data: {
        resource: 'url',
        url: zipArtifactUrl,
        caption: 'Downloading Query Monitor Extend',
      },
      progress: {
        weight: 2,
        caption: 'Installing Query Monitor Extend',
      },
    },
    {
      step: 'unzip',
      zipPath: '/wordpress/qmx/mu-plugin.zip',
      extractToPath: '/wordpress/qmx',
    },
    {
      step: 'mv',
      fromPath: '/wordpress/qmx/mu-plugins/query-monitor-extend',
      toPath: '/wordpress/wp-content/mu-plugins/query-monitor-extend',
    },
    {
      step: 'mv',
      fromPath: '/wordpress/qmx/mu-plugins/load-qmx.php',
      toPath: '/wordpress/wp-content/mu-plugins/load-qmx.php',
    }
  ];
}

const encoded = JSON.stringify( blueprint );
document.write( 'https://playground.wordpress.net/#' + encodeURI( encoded ) );
```