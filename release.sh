#!/bin/bash

echo "Retrieving Mollie API client v2..."
git submodule update --init
rmdir ./catalog/controller/extension/payment/mollie-api-client/examples
rmdir ./catalog/controller/extension/payment/mollie-api-client/tests

echo "1/8 Create temporary zip..."
zip -9 -rq temp.zip admin catalog
echo "2/8 Create temporary upload dir..."
rm -rf ./upload
mkdir ./upload
echo "3/8 Unzip temporary zip to upload dir..."
unzip -q temp.zip -d upload
echo "4/8 Create opencart installable zip..."
rm mollie-opencart-2.3-and-up.ocmod.zip
zip -9 -rq mollie-opencart-2.3-and-up.ocmod.zip upload LICENSE readme.md -x *.git* *.DS_Store

echo "5/8 Move files for legacy support (2.2 and lower)..."
mv ./upload/admin/controller/extension/* ./upload/admin/controller
mv ./upload/admin/language/dutch/extension/* ./upload/admin/language/dutch
mv ./upload/admin/language/nl-nl/extension/* ./upload/admin/language/nl-nl
mv ./upload/admin/language/english/extension/* ./upload/admin/language/english
mv ./upload/admin/language/en-gb/extension/* ./upload/admin/language/en-gb
mv ./upload/admin/language/french/extension/* ./upload/admin/language/french
mv ./upload/admin/language/fr-fr/extension/* ./upload/admin/language/fr-fr
mv ./upload/admin/view/template/extension/* ./upload/admin/view/template
rmdir ./upload/admin/controller/extension
rmdir ./upload/admin/language/dutch/extension
rmdir ./upload/admin/language/nl-nl/extension
rmdir ./upload/admin/language/english/extension
rmdir ./upload/admin/language/en-gb/extension
rmdir ./upload/admin/language/french/extension
rmdir ./upload/admin/language/fr-fr/extension
rmdir ./upload/admin/view/template/extension

mv ./upload/catalog/controller/extension/* ./upload/catalog/controller
mv ./upload/catalog/model/extension/* ./upload/catalog/model
mv ./upload/catalog/language/dutch/extension/* ./upload/catalog/language/dutch
mv ./upload/catalog/language/nl-nl/extension/* ./upload/catalog/language/nl-nl
mv ./upload/catalog/language/english/extension/* ./upload/catalog/language/english
mv ./upload/catalog/language/en-gb/extension/* ./upload/catalog/language/en-gb
mv ./upload/catalog/language/french/extension/* ./upload/catalog/language/french
mv ./upload/catalog/language/fr-fr/extension/* ./upload/catalog/language/fr-fr
mv ./upload/catalog/view/theme/default/template/extension/* ./upload/catalog/view/theme/default/template
rmdir ./upload/catalog/controller/extension
rmdir ./upload/catalog/model/extension
rmdir ./upload/catalog/language/dutch/extension
rmdir ./upload/catalog/language/nl-nl/extension
rmdir ./upload/catalog/language/english/extension
rmdir ./upload/catalog/language/en-gb/extension
rmdir ./upload/catalog/language/french/extension
rmdir ./upload/catalog/language/fr-fr/extension
rmdir ./upload/catalog/view/theme/default/template/extension

echo "6/8 Adjust files for legacy support (2.2 and lower)..."
echo "--------------------------------------------------------------------";
sh ./editFiles.sh
echo "--------------------------------------------------------------------";

echo "7/8 Create zip for legacy support (2.2 and lower)..."
rm mollie-opencart-2.2-and-lower.ocmod.zip
zip -9 -rq mollie-opencart-2.2-and-lower.ocmod.zip upload LICENSE readme.md -x *.git* *.DS_Store

echo "8/8 Cleanup..."
rm -rf ./upload
rm -rf ./temp.zip

echo "Done!"