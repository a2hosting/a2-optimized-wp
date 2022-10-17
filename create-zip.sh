#!/bin/bash

# clean up older files if they exist
rm -f a2-optimized-wp.zip
rm -f ../a2-optimized-wp.zip

# move one folder up
cd ..

# zip the plugin folder and exclude the items we do not need for deployment
zip -r a2-optimized-wp.zip ./a2-optimized-wp -x */\.* *.git* \.* ./a2-optimized-wp/*.git* a2-optimized-wp/.wordpress-org/ a2-optimized-wp/.wordpress-org/* a2-optimized-wp/assets/scss/ a2-optimized-wp/.gitattributes ./a2-optimized-wp/assets/scss/* a2-optimized-wp/assets/bootstrap/config.json a2-optimized-wp/docs/ a2-optimized-wp/docs/* a2-optimized-wp/.distignore a2-optimized-wp/.editorconfig a2-optimized-wp/.gitatrtributes a2-optimized-wp/.gitignore a2-optimized-wp/.gitatrtributes a2-optimized-wp/.php_cs.cache a2-optimized-wp/.phpcs.xml.dist a2-optimized-wp/create-zip.sh a2-optimized-wp/deploy.sh a2-optimized-wp/README.md

# move the new zip file into our working path
mv a2-optimized-wp.zip a2-optimized-wp/
