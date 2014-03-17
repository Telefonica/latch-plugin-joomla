#LATCH INSTALLATION GUIDE FOR JOOMLA


##PREREQUISITES
 * Joomla version 1.5 or later.

 * Curl extensions active in PHP (uncomment **"extension=php_curl.dll"** or"** extension=curl.so"** in Windows or Linux php.ini respectively).

 * To get the **"Application ID"** and **"Secret"**, (fundamental values for integrating Latch in any application), it’s necessary to register a developer account in [Latch's website](https://latch.elevenpaths.com). On the upper right side, click on **"Developer area"**.


##DOWNLOADING THE JOOMLA PLUGIN
 * When the account is activated, the user will be able to create applications with Latch and access to developer documentation, including existing SDKs and plugins. The user has to access again to [Developer area](https://latch.elevenpaths.com/www/developerArea), and browse his applications from **"My applications"** section in the side menu.

* When creating an application, two fundamental fields are shown: **"Application ID"** and **"Secret"**, keep these for later use. There are some additional parameters to be chosen, as the application icon (that will be shown in Latch) and whether the application will support OTP  (One Time Password) or not.

* From the side menu in developers area, the user can access the **"Documentation & SDKs"** section. Inside it, there is a **"SDKs and Plugins"** menu. Links to different SDKs in different programming languages and plugins developed so far, are shown.


##INSTALLING THE MODULE IN JOOMLA
* Add the plugin as a module in its administration panel in Joomla. Click on **"Extensions Manager"**, inside **"Extensions"**. It will show a form where you can browse and select previously downloaded ZIP file. Press **"Upload & Install"** to install it.

* Introduce **"Application ID"** and **"Secret"** previously generated. The administrator can now save the changes clicking on **"Save"** or **"Save & Close"**. If everything is ok, a confirmation message will be received.

* Click on **"Module Manager: Modules"**, on the left side of the administrator's panel. To change the position it is necessary to edit the module, clicking on it. A new window will be opened and the location may be set. It will depend on the theme used in Joomla.


##UNINSTALLING THE MODULE IN JOOMLA
Go to **"Extensions Manager"**, inside **"Extensions"**. Press **"Manage"** on the left side menu. Between installed modules, search for **"Latch Package"**. Click on the checkbox and press **"Uninstall"** on the upper side. A confirmation message will appear.
