# Utrust for Magento 2.3

**Demo Store:** https://magento2.store.utrust.com/

Accept Bitcoin, Ethereum, Utrust Token and other cryptocurrencies directly on your store with the Utrust payment gateway for Magento.
Utrust is cryptocurrency agnostic and provides fiat settlements.
The Utrust plugin extends Magento allowing you to take cryptocurrency payments directly on your store via Utrust’s API.
Find out more about Utrust in [utrust.com](https://utrust.com).

# Requirements

- Utrust Merchant account
- Online store in Magento 2.3.x

## Install and Update

### Installing

You will need FTP and SSH access to install this module:

1. Download our latest release zip file on the [releases page](https://github.com/utrustdev/utrust-for-magento2/releases).
2. Unzip the zip file in `app/code/Utrust/Payment`.
3. Enable the module by running `bin/magento module:enable Utrust_Payment`
4. Apply database updates by running `bin/magento setup:upgrade`
5. Flush the cache by running `bin/magento cache:flush`
6. Go to your Magento admin dashboard (it should be something like https://<your-store.com>/admin).
7. If you go to `Stores -> Sales -> Payment Methods` and `Utrust` is there it's successfully installed!

** Note: In production please use the `--keep-generated` option **

### Update

You can always check our [releases page](https://github.com/utrustdev/utrust-for-magento2/releases) for a new version. You can update by following the same instructions as installing.

### Uninstall (without Composer)

If [removing with Composer](https://devdocs.magento.com/guides/v2.3/install-gde/install/cli/install-cli-uninstall-mods.html) doesn't work (which is recommended), you can try to remove it manually:

```
bin/magento module:disable Utrust_Payment
bin/magento setup:upgrade
rm -rf app/code/Utrust
bin/magento cache:clean
bin/magento setup:static-content:deploy -f
```

# Setup

### On Utrust side

1. Go to [Utrust merchant dashboard](https://merchants.utrust.com).
2. Log in or sign up if you didn't yet.
3. On the left sidebar choose "Organization".
4. Click the button "Generate Credentials".
5. You will see now your `Client Id` and `Client Secret`, copy them – you will only be able to see the `Client Secret` once, after refreshing or changing page it will be no longer available to copy; if needed, you can always generate new credentials.

Note: It's important that you don't send your credentials to anyone otherwise they can use it to place orders _on your behalf_.

## On Magento side

1. Go to your Magento admin dashboard.
2. Navitage to `Stores -> Sales -> Payment Methods -> Utrust`.
3. Add your `Client Id` and `Client Secret` and click "Save Config" button on top.
4. Done!

## Features

- Creates Order and redirects to Utrust payment widget
- Receives and handles webhook payment received
- Receives and handles webhook payment cancelled

## Support

You can create [issues](https://github.com/utrustdev/utrust-for-magento2/issues) on our repository. In case of specific problems with your account, please contact support@utrust.com.

# Contributing

We commit all our new features directly into our GitHub repository. But you can also request or suggest new features or code changes yourself!

### Adding code to the repo

If you have a fix or feature, submit a pull request through GitHub to the `dev` branch. The master branch is only for stable releases. Please make sure the new code follows the same style and conventions as already written code.

### Developing

If you want to change the code on our plugin, it's recommended to install it in a local Magento store (using a virtual host) so you can make changes in a controlled environment. Alternatively, you can also do it in a Magento online store that you have for testing/staging.
You can access the module code via FTP in `app/code/Utrust/Payment/`. All the changes there should be reflected live in the store. You should test things before opening a pull request on this repo.
If something goes wrong with the module, logs can be found in `var/log/utrust.log`.

### Publishing

If you are member of UTRUST devteam and want to publish a new version of the plugin follow these [instructions](https://github.com/utrustdev/utrust-for-magento2/wiki/Publishing).

# License

???????????, check LICENSE file for more info.
