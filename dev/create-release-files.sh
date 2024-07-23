#!/bin/bash

# Creates release zips with the following panels:
# - ACF
# - Constants
# - Files
# - Heartbeat
# - Image Sizes
# - Paths
# - Time

rm -rf releases;

echo "Making directories...";

mkdir releases;
mkdir releases/plugins;
mkdir releases/mu-plugins;
mkdir releases/plugins/query-monitor-extend;
mkdir releases/mu-plugins/query-monitor-extend;

echo "Copying directories...";

# ACF
cp -r acf/ releases/plugins/query-monitor-extend/acf;
cp -r acf/ releases/mu-plugins/query-monitor-extend/acf;
echo "- acf";

# Constants
cp -r constants/ releases/plugins/query-monitor-extend/constants;
cp -r constants/ releases/mu-plugins/query-monitor-extend/constants;
echo "- constants";

# Files
cp -r files/ releases/plugins/query-monitor-extend/files;
cp -r files/ releases/mu-plugins/query-monitor-extend/files;
echo "- files";

# Globals
cp -r globals/ releases/plugins/query-monitor-extend/globals;
cp -r globals/ releases/mu-plugins/query-monitor-extend/globals;
echo "- globals";

# Heartbeat
cp -r heartbeat/ releases/plugins/query-monitor-extend/heartbeat;
cp -r heartbeat/ releases/mu-plugins/query-monitor-extend/heartbeat;
echo "- heartbeat";

# Image Sizes
cp -r image-sizes/ releases/plugins/query-monitor-extend/image-sizes;
cp -r image-sizes/ releases/mu-plugins/query-monitor-extend/image-sizes;
echo "- image-sizes";

# Paths
cp -r paths/ releases/plugins/query-monitor-extend/paths;
cp -r paths/ releases/mu-plugins/query-monitor-extend/paths;
echo "- paths";

# Time
cp -r time/ releases/plugins/query-monitor-extend/time;
cp -r time/ releases/mu-plugins/query-monitor-extend/time;
echo "- time";

echo "Copying files...";

# Conditionals
cp qmx-conditionals.php releases/plugins/query-monitor-extend/qmx-conditionals.php;
cp qmx-conditionals.php releases/mu-plugins/query-monitor-extend/qmx-conditionals.php;
echo "- qmx-conditionals.php";

# Plugin file
cp query-monitor-extend.php releases/plugins/query-monitor-extend/query-monitor-extend.php;
cp query-monitor-extend.php releases/mu-plugins/query-monitor-extend/query-monitor-extend.php;
echo "- query-monitor-extend.php";

echo "Creating mu-plugin loader...";

# Create file to load mu-plugin
head -13 query-monitor-extend.php > releases/mu-plugins/load-qmx.php;
echo "add_action( 'plugin_loaded', static function ( \$plugin ) : void {" >> releases/mu-plugins/load-qmx.php;
echo "\tif ( trailingslashit( constant( 'WP_PLUGIN_DIR' ) ) . 'query-monitor/query-monitor.php' !== \$plugin ) {\n\t\treturn;\n\t}\n" >> releases/mu-plugins/load-qmx.php;
echo "\trequire_once 'query-monitor-extend/query-monitor-extend.php';" >> releases/mu-plugins/load-qmx.php;
echo "} );" >> releases/mu-plugins/load-qmx.php;

echo "";
echo "Done copying files.";

echo "";
echo "==========================================="
echo "";

ls -lR releases;

echo "";
echo "==========================================="
echo "";

echo "Creating zips..."
echo ""

cd releases/plugins/;
zip --test --display-bytes --display-counts -r "../plugin.zip" query-monitor-extend/;
cd ../../;

echo ""

cd releases/;
zip --test --display-bytes --display-counts -r "mu-plugin.zip" mu-plugins/;
cd ../../;

echo ""
echo "Done creating zip files."
echo ""

echo "Complete"
