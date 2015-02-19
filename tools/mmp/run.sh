#!/bin/bash -
dir=$(cd $(dirname "$0"); pwd)
$dir/migration.php --config=$dir/config.ini --savedir=$dir/db $@ # schema
