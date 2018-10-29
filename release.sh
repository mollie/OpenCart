echo "Retrieving Mollie API client v2..."
echo "1/6 Create temporary zip..."
zip -9 -rq temp.zip admin catalog system vqmod
echo "2/6 Create temporary upload dir..."
rm -rf ./upload
mkdir ./upload
echo "3/6 Unzip temporary zip to upload dir..."
unzip -q temp.zip -d upload
echo "4/6 Create opencart installable zip..."
rm mollie-opencart-2.3-and-up.ocmod.zip
zip -9 -rq mollie-opencart-2.3-and-up.ocmod.zip upload LICENSE readme.md -x *.git* *.DS_Store

echo "5/6 Move files for legacy support (2.2 and lower)..."
echo "6/6 Cleanup..."
rm -rf ./upload
rm -rf ./temp.zip

echo "Done!"