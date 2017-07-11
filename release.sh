#!/bin/bash

echo "1/5 Create temporary zip..."
zip -9 -rq temp.zip admin catalog
echo "2/5 Create temporary upload dir..."
rm -rf ./upload
mkdir ./upload
echo "3/5 Unzip temporary zip to upload dir..."
unzip -q temp.zip -d upload
echo "4/5 Create opencart installable zip..."
rm mollie-opencart-x.x.x.ocmod.zip
zip -9 -rq mollie-opencart-x.x.x.ocmod.zip upload LICENSE readme.mdown -x *.git* *.DS_Store
echo "5/5 Cleanup..."
rm -rf ./upload
rm -rf ./temp.zip
echo "Done!"