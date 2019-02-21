echo "Retrieving Mollie API client v2..."
echo "1/5 Create temporary zip..."
zip -9 -rq temp.zip admin catalog system vqmod
echo "2/5 Create temporary upload dir..."
rm -rf ./upload
mkdir ./upload
echo "3/5 Unzip temporary zip to upload dir..."
unzip -q temp.zip -d upload
echo "4/5 Create opencart installable zip..."
rm mollie-opencart-2.3-and-up.ocmod.zip
rm mollie-opencart-1.5-and-up.vqmod.zip
zip -9 -rq mollie-opencart-1.5-and-up.vqmod.zip upload LICENSE readme.md -x *.git* *.DS_Store

echo "5/5 Cleanup..."
rm -rf ./upload
rm -rf ./temp.zip

echo "Done!"
