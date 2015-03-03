# Silk plugin for WordPress
Not ready for production

## WordPress Installation
* Clone repo
* Run `composer install`
* Activate plugin "OWC Silk"
* You might need to run `chmod 777 wp-content/vendor/owc/silk-api/silk.log` if you get an error activating the plugin
* Make sure you setup everything in Settings > Silk

## Import products
* Open `wp-content/plugins/wp-silk/src/Debug.php` and uncomment line 43
* Reload!
* Comment it again

### TODO
* Push service
* Attribute > Taxonomy mapping and sync
* Cron for sync
* Manual sync