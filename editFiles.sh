#!/bin/bash

echo Edit copied files for old style folders.
echo Changing classnames.
find ./upload/admin/controller/payment -name '*.php' -exec sed -i 's/ControllerExtensionPayment/ControllerPayment/g' '{}' \;
find ./upload/catalog/controller/payment -name '*.php' -exec sed -i 's/ControllerExtensionPayment/ControllerPayment/g' '{}' \;
find ./upload/catalog/model/payment -name '*.php' -exec sed -i 's/ModelExtensionPayment/ModelPayment/g' '{}' \;

echo Edit Helper

sed "s/extension\/payment/payment/g" ./upload/catalog/controller/payment/mollie/helper.php  -i

echo Edit base Controllers.

sed "s/extension\/payment/payment/g" ./upload/admin/controller/payment/mollie/base.php  -i
sed "s/return 'payment'/return 'extension\/payment'/g" ./upload/admin/controller/payment/mollie/base.php  -i
sed "s/\"controller\/payment\/\" \:/\"controller\/extension\/payment\/\" \:/g" ./upload/admin/controller/payment/mollie/base.php  -i

sed "s/extension\/payment/payment/g" ./upload/catalog/controller/payment/mollie/base.php  -i
sed "s/return 'payment'/return 'extension\/payment'/g" ./upload/catalog/controller/payment/mollie/base.php  -i
sed "s/extension\_payment/payment/g" ./upload/catalog/controller/payment/mollie/base.php  -i

echo Edit base Model.

sed "s/extension\/payment/payment/g" ./upload/catalog/model/payment/mollie/base.php  -i

echo Old style created.