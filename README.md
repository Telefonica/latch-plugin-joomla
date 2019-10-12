# LATCH INSTALLATION GUIDE FOR JOOMLA


## PREREQUISITES 
 * Joomla version 3.x.

 * Git version 2.12.2 

 * Composer version 1.4.1

 * Curl extensions active in PHP (uncomment **"extension=php_curl.dll"** or"** extension=curl.so"** in Windows or Linux php.ini respectively). 

 * To get the **"Application ID"** and **"Secret"**, (fundamental values for integrating Latch in any application), it’s necessary to register a developer account in [Latch's website](https://latch.elevenpaths.com"https://latch.elevenpaths.com"). On the upper right side, click on **"Developer area"**.

 
## DOWNLOADING THE JOOMLA PLUGIN
 * When the account is activated, the user will be able to create applications with Latch and access to developer documentation, including existing SDKs and plugins. The user has to access again to [Developer area](https://latch.elevenpaths.com/www/developerArea"https://latch.elevenpaths.com/www/developerArea"), and browse his applications from **"My applications"** section in the side menu.

* When creating an application, two fundamental fields are shown: **"Application ID"** and **"Secret"**, keep these for later use. There are some additional parameters to be chosen, as the application icon (that will be shown in Latch) and whether the application will support OTP  (One Time Password) or not.

* From the side menu in developers area, the user can access the **"Documentation & SDKs"** section. Inside it, there is a **"SDKs and Plugins"** menu. Links to different SDKs in different programming languages and plugins developed so far, are shown.


## INSTALLING THE MODULE IN JOOMLA
* Download the plugin, unzip it, open a terminal window in the "/extensions/libraries/latch/" subpath and run the command **"composer install"**. Rezip it after it's finished.

* Add the plugin as a module in its administration panel in Joomla. Click on **"Manage"** inside **"Extensions"**. It will show a form where you can browse and select previously downloaded ZIP files. Press **"Upload & Install"** to install it.

* Select **"Plugins"** in the top menu under **"Extensions"**. Search for the Latch plugin ("Latch user plugin for Joomla!"), click on it and type your Application ID and Application secret. The **"Host"** field should be autocompleted with the value **"https://latch.elevenpaths.com"**. Save the configuration. If everything is ok, a confirmation message will be received. Click on close.

* In the current window, select the Latch plugin and click on **"Enable"**.

* Select **"Manage"** under **"Extensions">"Manage"** in the top menu. Search for "Latch", select all results and click on **"Enable"**.

* Click on **"Module Manager: Modules"**, on the left side of the administrator's panel. To change the position it is necessary to edit the module, clicking on it. A new window will be opened and the location may be set. It will depend on the theme used in Joomla.


## UNINSTALLING THE MODULE IN JOOMLA
Go to **"Extensions Manager"**, inside **"Extensions"**. Press **"Manage"** on the left side menu. Between installed modules, search for **"Latch Package"**. Click on the checkbox and press **"Uninstall"** on the upper side. A confirmation message will appear.


## USE OF LATCH MODULE FOR THE USERS
**Latch does not affect in any case or in any way the usual operations with an account. It just allows or denies actions over it, acting as an independent extra layer of security that, once removed or without effect, will have no effect over the accounts, that will remain with its original state.**

### Pairing a user in Joomla
The user needs the Latch application installed on the phone, and follow these steps:

* **Step 1:** Log into your own account and you will find the Latch module on the top right corner. Click on the button **"Pair Account"** and you will be prompted to introduce your pairing token.

* **Step 2:** From the Latch app on the phone, the user has to generate the token, pressing on **“Add a new service"** at the bottom of the application. Pressing **"Generate new code"** will take the user to a new screen where the pairing code will be displayed.

* **Step 3:** The user has to type the characters generated on the phone into the text box displayed on the web page. Click on **"Submit"** button.

* **Step 4:** Now the user may lock and unlock the account, preventing any unauthorized access.


### Unpairing a user in Joomla
* From your Joomla account tap the **“Unpair account”** button. You will receive a notification indicating that the service has been unpaired.



## RESOURCES
- You can access Latch´s use and installation manuals, together with a list of all available plugins here: [https://latch.elevenpaths.com/www/developers/resources](https://latch.elevenpaths.com/www/developers/resources)

- Further information on de Latch´s API can be found here: [https://latch.elevenpaths.com/www/developers/doc_api](https://latch.elevenpaths.com/www/developers/doc_api)

- For more information about how to use Latch and testing more free features, please refer to the user guide in Spanish and English:
	1. [English version](https://latch.elevenpaths.com/www/public/documents/howToUseLatchNevele_EN.pdf)
	1. [Spanish version](https://latch.elevenpaths.com/www/public/documents/howToUseLatchNevele_ES.pdf)
