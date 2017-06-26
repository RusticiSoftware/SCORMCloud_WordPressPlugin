=== Plugin Name ===
Contributors: troyef, stuartchilds, timedwards
Tags: elearning, learning, scorm, aicc, education, training, cloud
Requires at least: 4.3
Tested up to: 4.7
Stable tag: 1.2.3

Tap the power of SCORM to deliver and track training right from your WordPress-powered site.

== Description ==

SCORM Cloud For WordPress enables you to manage and deliver training from within WordPress.  Harnessing the SCORM Engine powered SCORM Cloud training delivery service, this plugin provides all version SCORM compliance to any WordPress blog or WordPress Multi-Site installation, including BuddyPress support.


*   Upload SCORM or AICC courses to the SCORM Cloud from within WordPress.
*   Embed training into posts and pages of your blog. Select whether all users or only logged in users can launch courseware.
*   Assign training to other WordPress users directly.
*   Include widgets on your blog for displaying a logged in user's training or displaying a catalog of available courses to launch.
*   View learner progress as well as overall reports of learner training, including aggregated reports based on course, blog post, and all training.
*   Activate in network mode for WordPress Multi-Site to enable all blog sites to use a single SCORM Cloud account, but to manage their training individually.  

In addition to the functionality found in the SCORM Cloud For WordPress plugin, you will have the full power and functionality provided by the <a href='https://cloud.scorm.com/sc/guest/SignInForm'>SCORM Cloud site</a> which provides access to even more history information, account management, and extensive SCORM testing tools. A SCORM Cloud account is required to use the SCORM Cloud service features of the plugin; accounts are free for limited training usage but unlimited testing within the SCORM Cloud site.

Visit the <a href='http://www.scorm.com/scorm-solved/scorm-cloud/'>SCORM Cloud website</a> to learn more about SCORM Cloud.

== Installation ==

You can download and install SCORM Cloud For WordPress using the built in WordPress plugin installer. If you download SCORM Cloud For WordPress manually, make sure it is uploaded to "/wp-content/plugins/scormcloud/".

Activate SCORM Cloud For WordPress in the "Plugins" admin panel using the "Activate" or "Network Activate"link. 

On the left-hand Admin menu panel, open the SCORM Cloud menu and click on the Settings link.  On the SCORM Cloud Settings page, enter your AppID and Secret Key which can be found on the <a href='http://cloud.scorm.com/sc/user/Apps'>SCORM Cloud Apps</a> page by logging into your SCORM Cloud account.  If you are a super-admin and setting up a network-activated plugin, your credentials will be used for all sites and you need to choose whether to allow courses to be shared across all sites.  Click "Update Settings".



== Frequently Asked Questions ==

= Does a SCORM Cloud account cost money? =

Yes, although there is a free trial level account that allows for limited monthly training usage and unlimited SCORM testing.  If you decide that you need more than the 10 monthly free training registrations, there are several different tiers of paid accounts available, based on your usage needs. More <a href='http://www.scorm.com/scorm-solved/scorm-cloud/scorm-cloud-pricing/'>info on pricing</a>.

= What BuddyPress support do you provide? =

The SCORM Cloud For WordPress basic functionality works with BuddyPress without issue, including the SCORM Cloud widgets.  Additionally, SCORM Cloud for WordPress updates the BuddyPress activity stream with information about SCORM Cloud training that users take.

== Screenshots ==



== Changelog ==
= 1.2.3 =
* Fixed bug in course catalog widget

= 1.2.2 =
* Fixed bug in anonymous registrations

= 1.2.1 =
* Removed more deprecated methods
* Honor http/https schema being used for css/javascript includes

= 1.2.0 =
* Removed deprecated WP methods

= 1.1.9 =
* Updates Training View
* Updates to synching learner information
* Updates to shortcode management

= 1.1.8 =
* Adding proxy support

= 1.1.7 =
* Adding fixes for the non-user catalog launches and to prevent widget launch double-clicking (which could produce duplicate SCORM Cloud registrations).

= 1.1.6 =
* Fixes a bug database structure bug related to adding training to posts/pages.

= 1.1.5 =
* Fixes a bug that prevents a Multi-site enabled WordPress installation from sharing courses among sites.

= 1.1.4 =
* Fixes a bug that shows up in WordPress version 3.3.1 having to do with jQuery loading properly.

= 1.1.3 =
* Updates the course properties editor to latest editor in the SCORM Cloud.
* Fixes some minor UI styling inconsistencies.
* Fixes bugs related to missing registration data.

= 1.1.2 =
* Fixes a bug occasionally preventing widgets from appearing.

= 1.1.1 =
* Fixes a bug in database upgrade process.

= 1.1.0 =
* Adds option to send notification email to training invitees.
* Adds network settings page to control SCORM Cloud account usage/course library sharing.
* Fixes a bug when changing AppIDs.

= 1.0.7.3 =
* Fixes a bug preventing invalid configuration from being corrected.

= 1.0.7.2 =
* Fixes a bug in certain database updates.

= 1.0.7.1 =
* Fixes a bug preventing new users from configuring the plugin.

= 1.0.7 =
* Fixes some security vulnerabilities.

= 1.0.6.6 =
* Small update to fix a potentially troublesome php tag in embedTrainig.php.

= 1.0.6.5 =
* Adds the ability to limit training widget listings to only the latest per course.

= 1.0.6.4 =
* Disables the launch button after being clicked to prevent multiple clicks.

= 1.0.6.3 =
* Adds improved support for WordPress 3.0.1.

= 1.0.6.2 =
* Added a missing closing div tag to the catalog widget.

= 1.0.6.1 =
* Updates to cleanup contextual help funcitonality.

= 1.0.6 =
* Modified the length of the course_id value in the database to handle long packageId values created on SCORM Cloud.

= 1.0.5 =
* Primarily fixes that were hidden except in debug mode.
* Removed use of user_level since it has been deprecated. 

= 1.0.4 =
* Now sending a learner's email to the SCORM Cloud so that they will be associated with any existing learner or added to SCORM Cloud, aiding in user managament/reporting on the SCORM Cloud web app.
* Fixed a bug that sent across the name of the current user for direct invitations (instead of the invitee's name).
* Added functionality to update a user's name on the SCORM Cloud when their name is changed in wordpress.

= 1.0.3 =
* Modifications to better handle translation.  Also added a lang folder and a default .po file.
* Added Dutch language support.

= 1.0.2 =
* Modified the launch functionality so that non wordpress account holders can relaunch a course by entering the same email as their original launch into the launch form.

= 1.0.1 =
* Added a course Package Properties Editor to the admin courses page.

= 1.0 =
* Original Release.

== Upgrade Notice ==
= 1.1.9 =
* Updates Training View
* Updates to synching learner information
* Updates to shortcode management

= 1.1.8 =
* Adding proxy support

= 1.1.7 =
* Adding fixes for the non-user catalog launches and to prevent widget launch double-clicking (which could produce duplicate SCORM Cloud registrations).

= 1.1.6 =
* Fixes a bug database structure bug related to adding training to posts/pages.

= 1.1.5 =
* Fixes a bug that prevents a Multi-site enabled WordPress installation from sharing courses among sites. Does not effect non-multi-site functionality.

= 1.1.4 =
* Fixes a bug that shows up in WordPress version 3.3.1.

= 1.1.3 =
* Updates the course properties editor to latest editor in the SCORM Cloud.
* Fixes some minor UI styling inconsistencies.
* Fixes bugs related to missing registration data.

= 1.1.2 =
* Fixes a bug occasionally preventing widgets from appearing.

= 1.1.1 =
* Fixes a bug in the database upgrade process.

= 1.1.0 =
* Feature improvements in administration menus and training management.
* Fixes a bug when changing AppIDs.

= 1.0.7.3 =
* Fixes a bug preventing invalid configuration from being corrected.

= 1.0.7.2 =
* Fixes a bug in certain database updates. Upgrade strongly recommended.

= 1.0.7.1 =
* Fixes a bug that prevents new installations from being configured.

= 1.0.7 =
* Critical security vulnerability fixes. Upgrade immediately.

= 1.0.6.6 =
* Small update to fix a potentially troublesome php tag. Not needed if you are embedding trainings in posts without issue.

= 1.0.6.5 =
* Adds the ability to limit training widget listings to only the latest per course.

= 1.0.6.4 =
Adds improved functionality on the post/page embedded launch dialogs. Disables the button after being clicked to prevent multiple clicks.

= 1.0.6.3 =
Adds improved support for WordPress 3.0.1.

= 1.0.6.2 =
Added a missing closing div tag to the catalog widget.

= 1.0.6.1 =
Changes have been applied to better handle the contextual help for admins.  It should no longer throw warnings on non-SCORM Cloud plugin admin pages.

= 1.0.6 =
DB Change to handle longer courseId values from SCORM Cloud.

= 1.0.5 =
Mainly changes to remove warnings that display while in debug mode. Also changes to use the built in roles instead of the old user levels.

= 1.0.4 = 
Upgrades to better handle user management between wordpress and the SCORM Cloud web app.

= 1.0.3 =
Plugin modified to better handle translation using language files.
Added support for Dutch.

= 1.0.2 =
With this upgrade, if a learner who does not have an account on your WordPress system enters the same learner information and clicks launch a on the same course a second time, the course will re-launch where the learner left off instead of starting a new training and using a new SCORM Cloud registration.

= 1.0.1 =
The added course Package Properties Editor allows administrators to set course properties settings from within WordPress.  The course properties help determine how the course is delivered to the user.
