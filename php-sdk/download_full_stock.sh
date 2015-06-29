#!/bin/bash
_now=$(date +"%Y%m%d")
_file="/home/erp/software/eve-demo/data/inventory.$_now.hk.xml"
scp erp@59.188.196.20:"$_file" ./morning.inventory.hk.xml
