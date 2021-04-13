# moceanapi-sms

This CiviCRM extension provides SMS integration with MoceanAPI. 
Users are able to send individual, bulk and scheduled SMS message through its MoceanAPI Gateway to selected mobile phone users.

Try for FREE now. 20 trial SMS credits will be given upon [registration](https://dashboard.moceanapi.com/register?fr=civicrm). Additional SMS credits can be requested and is subject to approval by MoceanSMS.

## Requirements
* PHP v7.2+
* CiviCRM v5.35+
* Valid MoceanAPI account

## Installation
To install the extension, you must download the file in ZIP format which can be found here "https://github.com/".

Once the extension has been downloaded, you have to unzip the file and place it in "//civicrm/ext".

## Configuration
1.	Navigate to Administer -> System Settings -> SMS Providers
2.	Add new SMS Providers
3.	Select the "MoceanAPISMS" from select field.(required)
4.	In the Title field give any title for this MoceanAPISMS provider.
5.	Username field = Mocean account API key. (required)
6.	Password field = Mocean account API secret. (required)
7.	Leave the API Type as "http".
8.	Leave the API Url as "https://rest.moceanapi.com/rest/2/sms". 
9.	API PARAMETERS = mocean-from="your_name". (required)

## Notes
* Phone number format must having country code. E.g Malaysia phone number: 60123456789
* Phone type must be MOBILE
