#!/bin/bash

# Creates release zips with the following panels:
# - ACF
# - Constants
# - Files
# - Heartbeat
# - Image Sizes
# - Paths
# - Time

rm -r releases;

mkdir releases;
mkdir releases/plugins;
mkdir releases/mu-plugins;
mkdir releases/plugins/query-monitor-extend;
mkdir releases/mu-plugins/query-monitor-extend;

# ACF
cp -r acf/ releases/plugins/query-monitor-extend/acf;
cp -r acf/ releases/mu-plugins/query-monitor-extend/acf;

# Constants
cp -r constants/ releases/plugins/query-monitor-extend/constants;
cp -r constants/ releases/mu-plugins/query-monitor-extend/constants;

# Files
cp -r files/ releases/plugins/query-monitor-extend/files;
cp -r files/ releases/mu-plugins/query-monitor-extend/files;

# Heartbeat
cp -r heartbeat/ releases/plugins/query-monitor-extend/heartbeat;
cp -r heartbeat/ releases/mu-plugins/query-monitor-extend/heartbeat;

# Image Sizes
cp -r image-sizes/ releases/plugins/query-monitor-extend/image-sizes;
cp -r image-sizes/ releases/mu-plugins/query-monitor-extend/image-sizes;

# Paths
cp -r paths/ releases/plugins/query-monitor-extend/paths;
cp -r paths/ releases/mu-plugins/query-monitor-extend/paths;

# Time
cp -r time/ releases/plugins/query-monitor-extend/time;
cp -r time/ releases/mu-plugins/query-monitor-extend/time;

# Conditionals
cp qmx-conditionals.php releases/plugins/query-monitor-extend/qmx-conditionals.php;
cp qmx-conditionals.php releases/mu-plugins/query-monitor-extend/qmx-conditionals.php;

# Plugin file
cp query-monitor-extend.php releases/plugins/query-monitor-extend/query-monitor-extend.php;
cp query-monitor-extend.php releases/mu-plugins/query-monitor-extend/query-monitor-extend.php;

# Load mu-plugin
head -15 query-monitor-extend.php > releases/mu-plugins/load-qmx.php
echo 'require_once "query-monitor-extend/query-monitor-extend.php";' >> releases/mu-plugins/load-qmx.php
