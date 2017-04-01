# TADL CMS
A json headless CMS built on the f3 framework


alpha build.

v.0.0.0.3-alpha

# Installation steps:

1. Download files
2. Install Composer
3. Run `composer install` or `composer update` to install required files.
4. Point your http server to the /public folder.
5. Add your DB config to the user.cfg file.
6. Visit tadl.site/json/setup/tables_install
7. Visit tadl.site/, tadl.site/json, or tadl.site/cp

# So far you can...
- You can install tables
-- visiting /json/setup/tables_install

- You can build ajax based themes
-- Put your theme in app\ui\themes\something and assign the theme to your site 0 data in the sites table.
-- Your theme should use hte TADL json endpoint system to register json functions to use
--- Default themes for frontend: Alice in /ui/themes/Alice
--- Default themes for admin: RedQueen in /ui/themes/RedQueen

- You can add JSON endpoints programmaticly via TADL class
-- Register 'exposed' functions that should be accessible via json.
-- Look at the setup class for an example of how to do this.

- You can use Event hooks in your classes to allow other developers to extend them without modifying your code.

- You can load custom configs from within your plugin (plugin system not up yet).


