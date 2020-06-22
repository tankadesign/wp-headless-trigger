# Added features
I've added a CRON setting so the deploy webhook will fire during a CRON interval instead of every time you save a post. Setting the CRON interval to 0 will make the webhook fire immediately. If using the CRON interval, be sure to set `define('DISABLE_WP_CRON', 'true');` in *wp-config.php* and hit wp-cron.php every minute via crontab. You can use either Crontab, or if using something like Plesk setup a scheduled task. The task should run every minute (`* * * * *`) and hit the URL to the cron script over cURL. In Crontab it will look like this:
`* * * * * curl https://example.com/wp-cron.php?doing_wp_cron > /dev/null 2>&1`

----

# WP Headless Trigger

This plugin was built to make it easier for developers to use WordPress as a headless CMS.
It makes it a breeze to trigger builds in hostings like [ZEIT Now](https://zeit.co/home) or [Netlify](https://www.netlify.com/) whenever content is created or updated.

This plugin uses the `save_post` WordPress hook and makes a POST request to the webhook that you specified each time a post, page or custom post type is publish or updated.

## Installation

You can download the .zip file from [the github repo](https://github.com/nicoandrade/wp-headless-trigger.git) or clone the repository into the plugins folder using the following code.

```
git clone https://github.com/nicoandrade/wp-headless-trigger.git
```

Next you have to install and activate the plugin within the wordpress admin. Once activated, grab the webhook url from your hosting and enter it into the plugin settings page under Tools > Headless Trigger.

## Screenshots

<p align="left">
  <img src="https://cl.ly/e13c24a47adb/screenshot.png" alt="Settings page in WordPress admin">
</p>

