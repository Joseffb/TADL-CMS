# f3-Tiger-CMS
a light headless CMS built on the f3 framework


alpha build.

v.0.0.0.2-alpha

So far:

- You can install the tables by visiting /json/setup/tables_install
- You can build and load a (ajax) theme in app\ui\themes\something... (it should be getting its data from teh /json endpoints, not php , but you can...)
- Default themes are rendering from /ui/themes/Alice for frontend /, and /ui/themes/RedQueen for admin
- You can see all available json methods via the /json url. This is using the tadl and json classes. Tadl registers them and json processes them from tadl registry.
- You can see how to load a json method in the setup class.
