SCORM Cloud Plugin for Wordpress

SCORM Cloud For WordPress enables you to manage and deliver training from within WordPress.  Harnessing the SCORM Engine powered SCORM Cloud training delivery service, this plugin provides all version SCORM compliance to any WordPress blog or WordPress Multi-Site installation, including BuddyPress support.


*   Upload SCORM or AICC courses to the SCORM Cloud from within WordPress.
*   Embed training into posts and pages of your blog. Select whether all users or only logged in users can launch courseware.
*   Assign training to other WordPress users directly.
*   Include widgets on your blog for displaying a logged in user's training or displaying a catalog of available courses to launch.
*   View learner progress as well as overall reports of learner training, including aggregated reports based on course, blog post, and all training.
*   Activate in network mode for WordPress Multi-Site to enable all blog sites to use a single SCORM Cloud account, but to manage their training individually.  

In addition to the functionality found in the SCORM Cloud For WordPress plugin, you will have the full power and functionality provided by the <a href='https://cloud.scorm.com/sc/guest/SignInForm'>SCORM Cloud site</a> which provides access to even more history information, account management, and extensive SCORM testing tools. A SCORM Cloud account is required to use the SCORM Cloud service features of the plugin; accounts are free for limited training usage but unlimited testing within the SCORM Cloud site.

Visit the <a href='http://www.scorm.com/scorm-solved/scorm-cloud/'>SCORM Cloud website</a> to learn more about SCORM Cloud.

## Repos

This github repository holds the development trunk, but it is also should be considered stable and ready for use.  Versions are maintained at the wordpress site <http://wordpress.org/extend/plugins/scormcloud/>, but this repo should represent the latest in both places.

Note that when pulling the source from the github repository, the plugin is dependent on the <a href='https://github.com/RusticiSoftware/SCORMCloud_PHPLibrary'>SCORMCloud_PHPLibrary</a> repository, which has been added to this plugin as a submodule.

## Installation

You can download and install SCORM Cloud For WordPress using the built in WordPress plugin installer. If you download SCORM Cloud For WordPress manually, make sure the 'scormcloud' folder is uploaded to "/wp-content/plugins/".

Activate SCORM Cloud For WordPress in the "Plugins" admin panel using the "Activate" or "Network Activate"link. 

On the left-hand Admin menu panel, open the SCORM Cloud menu and click on the Settings link.  On the SCORM Cloud Settings page, enter your AppID and Secret Key which can be found on the <a href='http://cloud.scorm.com/sc/user/Apps'>SCORM Cloud Apps</a> page by logging into your SCORM Cloud account.  If you are a super-admin and setting up a network-activated plugin, your credentials will be used for all sites and you need to choose whether to allow courses to be shared across all sites.  Click "Update Settings".