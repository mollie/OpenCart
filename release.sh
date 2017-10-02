#!/bin/bash

echo "1/7 Create temporary zip..."
zip -9 -rq temp.zip admin catalog
echo "2/7 Create temporary upload dir..."
rm -rf ./upload
mkdir ./upload
echo "3/7 Unzip temporary zip to upload dir..."
unzip -q temp.zip -d upload

echo "4/7 Copying files for legacy version support 2.2 and lower..."
cp -r ./upload/admin/controller/extension/* ./upload/admin/controller
cp -r ./upload/admin/language/dutch/extension/* ./upload/admin/language/dutch
cp -r ./upload/admin/language/nl-nl/extension/* ./upload/admin/language/nl-nl
cp -r ./upload/admin/language/english/extension/* ./upload/admin/language/english
cp -r ./upload/admin/language/en-gb/extension/* ./upload/admin/language/en-gb
cp -r ./upload/admin/language/french/extension/* ./upload/admin/language/french
cp -r ./upload/admin/language/fr-fr/extension/* ./upload/admin/language/fr-fr
cp -r ./upload/admin/view/template/extension/* ./upload/admin/view/template

cp -r ./upload/catalog/controller/extension/* ./upload/catalog/controller
cp -r ./upload/catalog/language/dutch/extension/* ./upload/catalog/language/dutch
cp -r ./upload/catalog/language/nl-nl/extension/* ./upload/catalog/language/nl-nl
cp -r ./upload/catalog/language/english/extension/* ./upload/catalog/language/english
cp -r ./upload/catalog/language/en-gb/extension/* ./upload/catalog/language/en-gb
cp -r ./upload/catalog/language/french/extension/* ./upload/catalog/language/french
cp -r ./upload/catalog/language/fr-fr/extension/* ./upload/catalog/language/fr-fr
cp -r ./upload/catalog/view/theme/default/template/extension/* ./upload/catalog/view/theme/default/template

echo "5/7 Adjust file contents for legacy version support 2.2 and lower (this may take a while)..."
sh ./editFiles.sh

echo "6/7 Create opencart installable zip..."
rm mollie-opencart-x.x.x.ocmod.zip
zip -9 -rq mollie-opencart-x.x.x.ocmod.zip upload LICENSE readme.mdown -x *.git* *.DS_Store
echo "7/7 Cleanup..."
rm -rf ./upload
rm -rf ./temp.zip
echo "Done!"